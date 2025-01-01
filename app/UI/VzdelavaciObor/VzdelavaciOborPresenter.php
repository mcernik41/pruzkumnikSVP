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

		//nalezení ročníků		
		$this->template->rocniky = $this->explorer->table('rocnik')->fetchAll();
	}

	private function nacistTemata($vzdelavaciOborID)
	{
		//najdu všechny témata, které se váží k tomuto oboru
		$temata = [];
		$rows = $this->explorer->table('tema')
			->where('vzdelavaciObor_vzdelavaciOborID = ?', $vzdelavaciOborID)
			->order('mesicID_zacatek ASC')
			->fetchAll();

		for($i = 1; $i <= 10; $i++)
		{
			$temata[$i] = [];
		}

		foreach ($rows as $row) 
		{
			$temata[$row->rocnik_rocnikID][] = $row;
		}

		$this->template->temata = $temata;
	}

	protected function createComponentAreaForm(): Form
	{
		$vzdelavaciOborID = ((int)$this->getParameter('vzdelavaciOborID') == -1) ? null : (int)$this->getParameter('vzdelavaciOborID');

		$form = $this->areaFormFactory->create();
		$form->onSuccess[] = function (\stdClass $data) use ($vzdelavaciOborID) {
			$this->areaFormFactory->process($data, $this->explorer, null, (int)$this->getParameter('svpID'), $vzdelavaciOborID);
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
		$form->onSuccess[] = function (\stdClass $data) use ($vzdelavaciOborID, $obor) {
			$this->areaFormFactory->process($data, $this->explorer, $vzdelavaciOborID, (int)$this->getParameter('svpID'), $obor->rodicovskyVzdelavaciOborID);
			$this->flashMessage('Vzdělávací obor úspěšně upraven', 'success');
			$this->redirect('this');
		};

		return $form;
	}

	public function handleDeleteArea(int $id): void
	{
		$this->areaFormFactory->delete($this->explorer, $id);
		$this->flashMessage('Vzdělávací obor úspěšně odebrán', 'success');
		$this->redirect('this');
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

	public function handleDeleteTopic(int $id): void
	{
		$this->topicFormFactory->delete($this->explorer, $id);
		$this->flashMessage('Téma úspěšně odebráno', 'success');
		$this->redirect('this');
	}

	public function handleNahratOborySVP_NV(int $svpID)
	{
		$dataInsetrer = new \App\Services\DataInserter($this->explorer);
		$dataInsetrer->insertFields($svpID);

		$this->redirect('this');
	}
}