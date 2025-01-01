<?php

declare(strict_types=1);

namespace App\UI\VzdelavaciObsah;
use Nette\Application\UI\Form;
use Nette\Database\Explorer;

use Nette;


final class VzdelavaciObsahPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Explorer $database) 
	{
		$this->explorer = $database;
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
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoObsahu', 'Jméno vzdělvávacího obsahu:')
			->setRequired();

		$form->addTextarea('popisObsahu', 'Popis vzdělvávacího obsahu:');

		$form->addSubmit('send', 'Přidat vzdělávací obsah');

		$form->onSuccess[] = $this->contentFormSucceeded(...);

		return $form;
	}

	private function contentFormSucceeded(\stdClass $data): void
	{
		$svpID = $this->getParameter('svpID');
		$vzdelavaciObsahID = $this->getParameter('vzdelavaciObsahID');

		$this->database->table('vzdelavaciObsah')->insert([
			'svp_svpID' => $svpID,
			'rodicovskyVzdelavaciObsahID' => ($vzdelavaciObsahID == -1) ? null : $vzdelavaciObsahID,
			'jmenoObsahu' => $data->jmenoObsahu,
			'popisObsahu' => $data->popisObsahu,
		]);

		$this->flashMessage('Vzdělávací obsah úspěšně přidán', 'success');
		$this->redirect('this');
	}

	protected function createComponentContentModifyForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$vzdelavaciObsahID = $this->getParameter('vzdelavaciObsahID');		
		$obsah = $this->explorer->table('vzdelavaciObsah')->get($vzdelavaciObsahID);

		$form->addText('jmenoObsahu', 'Jméno vzdělvávacího obsahu:')
			->setDefaultValue($obsah->jmenoObsahu)
			->setRequired();

		$form->addTextarea('popisObsahu', 'Popis vzdělvávacího obsahu:')
			->setDefaultValue($obsah->popisObsahu);

		$form->addSubmit('send', 'Upravit vzdělávací obsah');

		$form->onSuccess[] = $this->contentModifyFormSucceeded(...);

		return $form;
	}

	private function contentModifyFormSucceeded(\stdClass $data): void
	{
		$vzdelavaciObsahID = $this->getParameter('vzdelavaciObsahID');

		$this->database->table('vzdelavaciObsah')
			->where('vzdelavaciObsahID', $vzdelavaciObsahID)
			->update([
				'jmenoObsahu' => $data->jmenoObsahu,
				'popisObsahu' => $data->popisObsahu,
		]);

		$this->flashMessage('Vzdělávací obsah úspěšně upraven', 'success');
		$this->redirect('this');
	}

	public function handleNahratObsahSVP_NV(int $svpID)
	{
		$dataInsetrer = new \App\Services\DataInserter($this->explorer);
		$dataInsetrer->insertContents($svpID);

		$this->redirect('this');
	}
}