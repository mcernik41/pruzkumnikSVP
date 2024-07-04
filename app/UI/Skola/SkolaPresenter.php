<?php

declare(strict_types=1);

namespace App\UI\Skola;
use Nette\Application\UI\Form;

use Nette;


final class SkolaPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Nette\Database\Explorer $database) 
	{
		$this->explorer = $database;
	}

	protected $explorer;

	public function renderDefault(int $skolaID): void
	{
		$skola = $this->explorer->table('skola')->get($skolaID);
		$this->template->jmenoSkoly = $skola->jmenoSkoly;

		$this->template->vzdelavaciPlany = $skola->related('svp');
	}

	protected function createComponentPlanForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoPlanu', 'Jméno vzdělvávacího plánu:')
			->setRequired();

		$form->addTextarea('popisSVP', 'Popis vzdělvávacího plánu:');

		$form->addSubmit('send', 'Přidat vzdělávací plán');

		$form->onSuccess[] = $this->planFormSucceeded(...);

		return $form;
	}

	private function planFormSucceeded(\stdClass $data): void
	{
		$skolaID = $this->getParameter('skolaID');

		$this->database->table('svp')->insert([
			'skola_skolaID' => $skolaID,
			'jmenoSVP' => $data->jmenoPlanu,
			'popisSVP' => $data->popisSVP,
		]);

		$this->flashMessage('Vzdělávací plán úspěšně přidán', 'success');
		$this->redirect('this');
	}

	protected function createComponentSchoolForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$skolaID = $this->getParameter('skolaID');
		$skola = $this->explorer->table('skola')->get($skolaID);
		$this->template->jmenoSkoly = $skola->jmenoSkoly;

		$form->addText('name', 'Jméno školy:')
			->setRequired()
			->setDefaultValue($skola->jmenoSkoly);

		$form->addSubmit('send', 'Upravit školu');

		$form->onSuccess[] = $this->schoolFormSucceeded(...);

		return $form;
	}

	private function schoolFormSucceeded(\stdClass $data): void
	{
		$skolaID = $this->getParameter('skolaID');

		$this->database->table('skola')
			->where('skolaID', $skolaID)
			->update([
				'jmenoSkoly' => $data->name
		]);

		$this->flashMessage('Škola úspěšně upravena', 'success');
		$this->redirect('this');
	}
}
