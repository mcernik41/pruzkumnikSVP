<?php

declare(strict_types=1);

namespace App\UI\Home;

use App\Forms\SchoolFormFactory;
use App\Forms\ToolFormFactory;
use App\Forms\GradeFormFactory;
use App\Forms\ActivityTypeFormFactory;
use Nette\Application\UI\Form;
use Nette;

final class HomePresenter extends Nette\Application\UI\Presenter
{
	public function __construct(Nette\Database\Explorer $database, SchoolFormFactory $schoolFormFactory, ToolFormFactory $toolFormFactory, GradeFormFactory $gradeFormFactory, ActivityTypeFormFactory $activityTypeFormFactory)
	{
		parent::__construct();
		$this->explorer = $database;
		$this->schoolFormFactory = $schoolFormFactory;
		$this->toolFormFactory = $toolFormFactory;
		$this->gradeFormFactory = $gradeFormFactory;
		$this->activityTypeFormFactory = $activityTypeFormFactory;
	}

	protected $explorer;
	private SchoolFormFactory $schoolFormFactory;
	private ToolFormFactory $toolFormFactory;
	private GradeFormFactory $gradeFormFactory;
	private ActivityTypeFormFactory $activityTypeFormFactory;

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
		$form = $this->activityTypeFormFactory->create();
		$form->onSuccess[] = function (\stdClass $data) {
			$this->activityTypeFormFactory->process($data, $this->explorer);
			$this->flashMessage('Typ aktivity úspěšně přidán', 'success');
			$this->redirect('this');
		};

		return $form;
	}

	public function handleDeleteActivityType(int $id): void
	{
		$this->activityTypeFormFactory->delete($this->explorer, $id);
		$this->flashMessage('Typ aktivity úspěšně odebrán', 'success');
		$this->redirect('this');
	}

	/* ROČNÍKY */
	public function handleCreateGrades(): void
	{
		$dataInsetrer = new \App\Services\DataInserter($this->explorer);
		$dataInsetrer->insertGrades();
	
		$this->flashMessage('Ročníky úspěšně vytvořeny', 'success');
		$this->redirect('this');
	}

	protected function createComponentGradeForm(): Form
	{
		$form = $this->gradeFormFactory->create();
		$form->onSuccess[] = function (\stdClass $data) {
			$this->gradeFormFactory->process($data, $this->explorer);
			$this->flashMessage('Ročník úspěšně přidán', 'success');
			$this->redirect('this');
		};

		return $form;
	}

	public function handleDeleteGrade(int $id): void
	{
		$this->gradeFormFactory->delete($this->explorer, $id);
		$this->flashMessage('Ročník úspěšně odebránn', 'success');
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
