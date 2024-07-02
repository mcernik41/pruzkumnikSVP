<?php

declare(strict_types=1);

namespace App\UI\Home;
use Nette\Application\UI\Form;

use Nette;


final class HomePresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Nette\Database\Explorer $database) 
	{
		$this->explorer = $database;
	}

	protected $explorer;

	public function renderDefault(): void
	{
		$skoly = $this->explorer->table('skola');
		$this->template->skoly = $skoly;
		
		$typyAktivit = $this->explorer->table('typAktivity');
		$this->template->typyAktivit = $typyAktivit;
	}

	protected function createComponentSchoolForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('name', 'Jméno školy:')
			->setRequired();

		$form->addSubmit('send', 'Přidat školu');

		$form->onSuccess[] = $this->schoolFormSucceeded(...);

		return $form;
	}

	private function schoolFormSucceeded(\stdClass $data): void
	{
		$this->database->table('skola')->insert([
			'jmenoSkoly' => $data->name
		]);

		$this->flashMessage('Škola úspěšně přidána', 'success');
		$this->redirect('this');
	}

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
}
