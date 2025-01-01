<?php

declare(strict_types=1);

namespace App\UI\VzdelavaciObsah;

use App\Forms\ContentFormFactory;
use Nette\Application\UI\Form;
use Nette\Database\Explorer;
use Nette;

final class VzdelavaciObsahPresenter extends Nette\Application\UI\Presenter
{
	private ContentFormFactory $contentFormFactory;

	public function __construct(Explorer $database, ContentFormFactory $contentFormFactory)
	{
		parent::__construct();
		$this->explorer = $database;
		$this->contentFormFactory = $contentFormFactory;
	}

	protected $explorer;

	public function renderDefault(int $svpID, int $vzdelavaciObsahID): void
	{
		$plan = $this->explorer->table('svp')->get($svpID);
		$this->template->jmenoSVP = $plan->jmenoSVP;

		$this->template->svpID = $svpID;
		$this->template->vzdelavaciObsahID = $vzdelavaciObsahID;

		//načtení vzdělávacích obsahů
		if ($vzdelavaciObsahID === -1) 
		{
			$obsahy = $this->explorer->table('vzdelavaciObsah')
				->where('rodicovskyVzdelavaciObsahID IS NULL AND svp_svpID = ?', $svpID)
				->fetchAll();
		} 
		else 
		{
			$obsahy = $this->explorer->table('vzdelavaciObsah')
				->where('rodicovskyVzdelavaciObsahID = ?', $vzdelavaciObsahID)
				->fetchAll();

			$obsah = $this->explorer->table('vzdelavaciObsah')->get($vzdelavaciObsahID);
			$this->template->jmenoObsahu = $obsah->jmenoObsahu;
		}

		$this->template->vzdelavaciObsahy = $obsahy;

		//nalezení součástí vzdělávacích aktivit - podle oboru a aktivity
		$this->template->obory = \App\Services\ActivityLoader::nacistAktivity($this->explorer, $vzdelavaciObsahID, 'vzdelavaciObsah');
	}

	protected function createComponentContentForm(): Form
	{
		$vzdelavaciObsahID = ((int)$this->getParameter('vzdelavaciObsahID') == -1) ? null : (int)$this->getParameter('vzdelavaciObsahID');

		$form = $this->contentFormFactory->create();
		$form->onSuccess[] = function (\stdClass $data) use ($vzdelavaciObsahID) {
			$this->contentFormFactory->process($data, $this->explorer, null, (int)$this->getParameter('svpID'), $vzdelavaciObsahID);
			$this->flashMessage('Vzdělávací obsah úspěšně přidán', 'success');
			$this->redirect('this');
		};

		return $form;
	}

	protected function createComponentContentModifyForm(): Form
	{
		$vzdelavaciObsahID = (int)$this->getParameter('vzdelavaciObsahID');
		$obsah = $this->explorer->table('vzdelavaciObsah')->get($vzdelavaciObsahID);

		$defaultValues = [
			'jmenoObsahu' => $obsah->jmenoObsahu,
			'popisObsahu' => $obsah->popisObsahu
		];

		$form = $this->contentFormFactory->create($defaultValues);
		$form->onSuccess[] = function (\stdClass $data) use ($vzdelavaciObsahID, $obsah) {
			$this->contentFormFactory->process($data, $this->explorer, $vzdelavaciObsahID, (int)$this->getParameter('svpID'), $obsah->rodicovskyVzdelavaciObsahID);
			$this->flashMessage('Vzdělávací obsah úspěšně upraven', 'success');
			$this->redirect('this');
		};

		return $form;
	}

	public function handleNahratObsahSVP_NV(int $svpID)
	{
		$dataInsetrer = new \App\Services\DataInserter($this->explorer);
		$dataInsetrer->insertContents($svpID);

		$this->redirect('this');
	}
}