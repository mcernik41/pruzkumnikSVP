<?php

declare(strict_types=1);

namespace App\UI\SeznamCilu;
use Nette\Application\UI\Form;

use Nette;


final class SeznamCiluPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Nette\Database\Explorer $database) 
	{
		$this->explorer = $database;
	}

	protected $explorer;
	protected $svpID;

	public function renderDefault(int $svpID): void
	{
		$plan = $this->explorer->table('svp')->get($svpID);
		$this->template->jmenoSVP = $plan->jmenoSVP;
		$this->template->svpID = $plan->svpID;

		$this->svpID = $svpID;

		//načtení cílů
		$cile = $this->explorer->table('cil')->where('svp_svpID = ?', $svpID)->fetchAll();
		$this->template->cile = $cile;
	}

	protected function createComponentGoalForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoCile', 'Jméno cíle:')
			->setRequired();

		$form->addTextarea('popisCile', 'Popis cíle:');

		$form->addSubmit('send', 'Přidat cíl');

		$form->onSuccess[] = $this->goalFormSucceeded(...);

		return $form;
	}

	private function goalFormSucceeded(\stdClass $data): void
	{
		$svpID = $this->getParameter('svpID');

		$this->database->table('cil')->insert([
			'svp_svpID' => $svpID,
			'jmenoCile' => $data->jmenoCile,
			'popisCile' => $data->popisCile,
		]);

		$this->flashMessage('Cíl úspěšně přidán', 'success');
		$this->redirect('this');
	}
}
