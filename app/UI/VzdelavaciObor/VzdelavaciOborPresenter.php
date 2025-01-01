<?php

declare(strict_types=1);

namespace App\UI\VzdelavaciObor;

use App\Forms\TopicFormFactory;
use Nette\Application\UI\Form;
use Nette;


final class VzdelavaciOborPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Nette\Database\Explorer $database, TopicFormFactory $topicFormFactory) 
	{
        parent::__construct();
		$this->explorer = $database;
        $this->topicFormFactory = $topicFormFactory;
	}

	protected $explorer;
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
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoOboru', 'Jméno vzdělvávacího oboru:')
			->setRequired();

		$form->addTextarea('popisOboru', 'Popis vzdělvávacího oboru:');

		$form->addSubmit('send', 'Přidat vzdělávací obor');

		$form->onSuccess[] = $this->areaFormSucceeded(...);

		return $form;
	}

	private function areaFormSucceeded(\stdClass $data): void
	{
		$svpID = $this->getParameter('svpID');
		$vzdelavaciOborID = $this->getParameter('vzdelavaciOborID');

		$this->database->table('vzdelavaciObor')->insert([
			'svp_svpID' => $svpID,
			'rodicovskyVzdelavaciOborID' => ($vzdelavaciOborID == -1) ? null : $vzdelavaciOborID,
			'jmenoOboru' => $data->jmenoOboru,
			'popisOboru' => $data->popisOboru,
		]);

		$this->flashMessage('Vzdělávací obor úspěšně přidán', 'success');
		$this->redirect('this');
	}

	protected function createComponentAreaModifyForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$vzdelavaciOborID = $this->getParameter('vzdelavaciOborID');		
		$obor = $this->explorer->table('vzdelavaciObor')->get($vzdelavaciOborID);

		$form->addText('jmenoOboru', 'Jméno vzdělvávacího oboru:')
			->setDefaultValue($obor->jmenoOboru)
			->setRequired();

		$form->addTextarea('popisOboru', 'Popis vzdělvávacího oboru:')
			->setDefaultValue($obor->popisOboru);

		$form->addSubmit('send', 'Upravit vzdělávací obor');

		$form->onSuccess[] = $this->areaModifyFormSucceeded(...);

		return $form;
	}

	private function areaModifyFormSucceeded(\stdClass $data): void
	{
		$vzdelavaciOborID = $this->getParameter('vzdelavaciOborID');

		$this->database->table('vzdelavaciObor')
			->where('vzdelavaciOborID', $vzdelavaciOborID)
			->update([
				'jmenoOboru' => $data->jmenoOboru,
				'popisOboru' => $data->popisOboru,
		]);

		$this->flashMessage('Vzdělávací obor úspěšně upraven', 'success');
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

	public function handleNahratOborySVP_NV(int $svpID)
	{
		$dataInsetrer = new \App\Services\DataInserter($this->explorer);
		$dataInsetrer->insertFields($svpID);

		$this->redirect('this');
	}
}