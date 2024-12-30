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

		$rocniky = $this->explorer->table('rocnik');
		$this->template->rocniky = $rocniky;
	
		$pomucky = $this->explorer->table('pomucka');
		$this->template->pomucky = $pomucky;
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

		$form->addText('nazevRocniku', 'Název ročníku:')
			->setRequired();

		$form->addTextarea('popisRocniku', 'Popis ročníku:');

		$form->addSubmit('send', 'Přidat ročník');

		$form->onSuccess[] = $this->gradeFormSucceeded(...);

		return $form;
	}

	private function gradeFormSucceeded(\stdClass $data): void
	{
		$this->database->table('rocnik')->insert([
			'nazevRocniku' => $data->nazevRocniku,
			'popisRocniku' => $data->popisRocniku
		]);

		$this->flashMessage('Ročník úspěšně přidán', 'success');
		$this->redirect('this');
	}

	public function handleCreateGrades(): void
	{
		$grades = [
			['nazevRocniku' => 'prima', 'popisRocniku' => '6. ročník osmiletého gymnázia - 6. ročník základní školy'],
			['nazevRocniku' => 'sekunda', 'popisRocniku' => '7. ročník osmiletého gymnázia - 7. ročník základní školy'],
			['nazevRocniku' => 'tercie', 'popisRocniku' => '8. ročník osmiletého gymnázia - 8. ročník základní školy'],
			['nazevRocniku' => 'kvarta', 'popisRocniku' => '9. ročník osmiletého gymnázia - 9. ročník základní školy'],
			['nazevRocniku' => 'kvinta', 'popisRocniku' => '1. ročník osmiletého gymnázia - 1. ročník střední školy'],
			['nazevRocniku' => 'sexta', 'popisRocniku' => '2. ročník osmiletého gymnázia - 2. ročník střední školy'],
			['nazevRocniku' => 'septima', 'popisRocniku' => '3. ročník osmiletého gymnázia - 3. ročník střední školy'],
			['nazevRocniku' => 'oktáva', 'popisRocniku' => '4. ročník osmiletého gymnázia - 4. ročník střední školy']
		];
	
		$this->database->table('rocnik')->insert($grades);
	
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
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('nazevPomucky', 'Název pomůcky:')
			->setRequired();

		$form->addTextarea('popisPomucky', 'Popis pomůcky:');

		$form->addSubmit('send', 'Přidat pomůcku');

		$form->onSuccess[] = $this->toolFormSucceeded(...);

		return $form;
	}

	private function toolFormSucceeded(\stdClass $data): void
	{
		$this->database->table('pomucka')->insert([
			'nazevPomucky' => $data->nazevPomucky,
			'popisPomucky' => $data->popisPomucky
		]);

		$this->flashMessage('Pomůcka úspěšně přidána', 'success');
		$this->redirect('this');
	}

	public function handleDeleteTool(int $id): void
	{
		$this->database->table('pomucka')->where('pomuckaID', $id)->delete();

		$this->flashMessage('Pomůcka úspěšně odebrána', 'success');
		$this->redirect('this');
	}
}
