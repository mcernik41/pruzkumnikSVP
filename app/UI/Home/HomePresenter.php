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
}
