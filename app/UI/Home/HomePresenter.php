<?php

declare(strict_types=1);

namespace App\UI\Home;

use App\Forms\SchoolFormFactory;
use App\Forms\ToolFormFactory;
use Nette\Application\UI\Form;
use Nette;

final class HomePresenter extends Nette\Application\UI\Presenter
{
	public function __construct(Nette\Database\Explorer $database, SchoolFormFactory $schoolFormFactory, ToolFormFactory $toolFormFactory)
	{
		parent::__construct();
		$this->explorer = $database;
		$this->schoolFormFactory = $schoolFormFactory;
		$this->toolFormFactory = $toolFormFactory;
	}

	protected $explorer;
	private SchoolFormFactory $schoolFormFactory;
	private ToolFormFactory $toolFormFactory;

	public function handleCreateTestData(): void
	{
		$dataInsetrer = new \App\Services\DataInserter($this->explorer);
		$dataInsetrer->insertTestData();
	
		$this->flashMessage('Testovací data úspěšně vložena', 'success');
		$this->redirect('this');
	}

	public function renderDefault(): void
	{
		$skoly = $this->explorer->table('skola');
		$this->template->skoly = $skoly;
		
		$typyAktivit = $this->explorer->table('typAktivity');
		$this->template->typyAktivit = $typyAktivit;

		$rocniky = $this->explorer->table('rocnik');
		$this->template->rocniky = $rocniky;
	
		$pomucky = $this->explorer->table('pomucka');
		$this->template->pomucky = $pomucky;
	}

	protected function createComponentSchoolForm(): Form
	{
		$form = $this->schoolFormFactory->create();
		$form->onSuccess[] = function (\stdClass $data) {
			$this->schoolFormFactory->process($data, $this->explorer);
			$this->flashMessage('Škola úspěšně přidána', 'success');
			$this->redirect('this');
		};

		return $form;
	}

	/* TYPY AKTIVIT */
	protected function createComponentActivityTypeForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoTypu', 'Jméno typu aktivity:')
			->setRequired();

		$form->addTextarea('popisTypu', 'Popis typu aktivity:');

		$form->addSubmit('send', 'Přidat typ aktivity');

		$form->onSuccess[] = $this->activityTypeFormSucceeded(...);

		return $form;
	}

	private function activityTypeFormSucceeded(\stdClass $data): void
	{
		$this->database->table('typAktivity')->insert([
			'jmenoTypu' => $data->jmenoTypu,
			'popisTypu' => $data->popisTypu
		]);

		$this->flashMessage('Typ aktivity úspěšně přidán', 'success');
		$this->redirect('this');
	}

	public function handleDeleteActivityType(int $id): void
	{
		$this->database->table('typAktivity')->where('typAktivityID', $id)->delete();

		$this->flashMessage('Typ aktivity úspěšně odebrán', 'success');
		$this->redirect('this');
	}

	/* ROČNÍKY */
	protected function createComponentGradeForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoRocniku', 'Jméno ročníku:')
			->setRequired();

		$form->addTextarea('popisRocniku', 'Popis ročníku:');

		$form->addSubmit('send', 'Přidat ročník');

		$form->onSuccess[] = $this->gradeFormSucceeded(...);

		return $form;
	}

	private function gradeFormSucceeded(\stdClass $data): void
	{
		$this->database->table('rocnik')->insert([
			'jmenoRocniku' => $data->jmenoRocniku,
			'popisRocniku' => $data->popisRocniku
		]);

		$this->flashMessage('Ročník úspěšně přidán', 'success');
		$this->redirect('this');
	}

	public function handleCreateGrades(): void
	{
		$dataInsetrer = new \App\Services\DataInserter($this->explorer);
		$dataInsetrer->insertGrades();
	
		$this->flashMessage('Ročníky úspěšně vytvořeny', 'success');
		$this->redirect('this');
	}

	public function handleDeleteGrade(int $id): void
	{
		$this->database->table('rocnik')->where('rocnikID', $id)->delete();

		$this->flashMessage('Ročník úspěšně odebrán', 'success');
		$this->redirect('this');
	}

	/* POMŮCKY */
	protected function createComponentToolForm(): Form
	{
		$form = $this->toolFormFactory->create();
		$form->onSuccess[] = function (\stdClass $data) {
			$this->toolFormFactory->process($data, $this->explorer);
			$this->flashMessage('Pomůcka úspěšně přidána', 'success');
			$this->redirect('this');
		};

		return $form;
	}

	public function handleDeleteTool(int $id): void
	{
		$this->toolFormFactory->delete($this->explorer, $id);
		$this->flashMessage('Pomůcka úspěšně odebrána', 'success');
		$this->redirect('this');
	}
}
