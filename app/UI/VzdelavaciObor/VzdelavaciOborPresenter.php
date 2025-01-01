<?php

declare(strict_types=1);

namespace App\UI\VzdelavaciObor;

use App\Forms\AreaFormFactory;
use App\Forms\TopicFormFactory;
use Nette\Application\UI\Form;
use Nette;

final class VzdelavaciOborPresenter extends Nette\Application\UI\Presenter
{

	public function __construct(Nette\Database\Explorer $database, AreaFormFactory $areaFormFactory, TopicFormFactory $topicFormFactory)
	{
		parent::__construct();
		$this->explorer = $database;
		$this->areaFormFactory = $areaFormFactory;
		$this->topicFormFactory = $topicFormFactory;
	}

	protected $explorer;
	private AreaFormFactory $areaFormFactory;
	private TopicFormFactory $topicFormFactory;

	public function renderDefault(int $svpID, int $vzdelavaciOborID): void
	{
		$plan = $this->explorer->table('svp')->get($svpID);
		$this->template->jmenoSVP = $plan->jmenoSVP;

		$this->template->svpID = $svpID;
		$this->template->vzdelavaciOborID = $vzdelavaciOborID;

		//načtení vzdělávacích oborů
		if ($vzdelavaciOborID === -1) 
		{
			$obory = $this->explorer->table('vzdelavaciObor')
				->where('rodicovskyVzdelavaciOborID IS NULL AND svp_svpID = ?', $svpID)
				->fetchAll();
		} 
		else 
		{
			$obory = $this->explorer->table('vzdelavaciObor')
				->where('rodicovskyVzdelavaciOborID = ?', $vzdelavaciOborID)
				->fetchAll();

			$obor = $this->explorer->table('vzdelavaciObor')->get($vzdelavaciOborID);
			$this->template->jmenoOboru = $obor->jmenoOboru;
		}

		$this->template->vzdelavaciObory = $obory;

		//nalezení součástí vzdělávacích aktivit - podle obsahu a aktivity
		$this->template->obsahy = \App\Services\ActivityLoader::nacistAktivity($this->explorer, $vzdelavaciOborID, 'vzdelavaciObor');

		//nalezení témat
		$this->nacistTemata($vzdelavaciOborID);
	}

	private function nacistTemata($vzdelavaciOborID)
	{
		//najdu všechny témata, které se váží k tomuto oboru
		$temata = $this->explorer->table('tema')
			->where('vzdelavaciObor_vzdelavaciOborID = ?', $vzdelavaciOborID)
			->fetchAll();

		$this->template->temata = $temata;
	}

	protected function createComponentAreaForm(): Form
	{
		$form = $this->areaFormFactory->create();
		$form->onSuccess[] = function (\stdClass $data) {
			$this->areaFormFactory->process($data, $this->explorer, null, (int)$this->getParameter('svpID'), ((int)$this->getParameter('vzdelavaciOborID') == -1) ? null : (int)$this->getParameter('vzdelavaciOborID'));
			$this->flashMessage('Vzdělávací obor úspěšně přidán', 'success');
			$this->redirect('this');
		};

		return $form;
	}

	protected function createComponentAreaModifyForm(): Form
	{
		$vzdelavaciOborID = (int)$this->getParameter('vzdelavaciOborID');
		$obor = $this->explorer->table('vzdelavaciObor')->get($vzdelavaciOborID);

		$defaultValues = [
			'jmenoOboru' => $obor->jmenoOboru,
			'popisOboru' => $obor->popisOboru
		];

		$form = $this->areaFormFactory->create($defaultValues);
		$form->onSuccess[] = function (\stdClass $data) use ($vzdelavaciOborID) {
			$this->areaFormFactory->process($data, $this->explorer, $vzdelavaciOborID, (int)$this->getParameter('svpID'), ((int)$this->getParameter('vzdelavaciOborID') == -1) ? null : (int)$this->getParameter('vzdelavaciOborID'));
			$this->flashMessage('Vzdělávací obor úspěšně upraven', 'success');
			$this->redirect('this');
		};

		return $form;
	}

	protected function createComponentTopicForm(): Form
	{
		$form = $this->topicFormFactory->create();
		$form->onSuccess[] = function (\stdClass $data) {
			$this->topicFormFactory->process($data, $this->explorer, null, (int)$this->getParameter('vzdelavaciOborID'));
			$this->flashMessage('Téma úspěšně přidáno', 'success');
			$this->redirect('this');
		};

		return $form;
	}

	public function handleNahratOborySVP_NV(int $svpID)
	{
		$dataInsetrer = new \App\Services\DataInserter($this->explorer);
		$dataInsetrer->insertFields($svpID);

		$this->redirect('this');
	}
}