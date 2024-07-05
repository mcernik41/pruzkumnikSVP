<?php

declare(strict_types=1);

namespace App\UI\SeznamAktivit;
use Nette\Application\UI\Form;

use Nette;


final class SeznamAktivitPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Nette\Database\Explorer $database) 
	{
		$this->explorer = $database;
	}

	protected $explorer;

	public function renderDefault(int $svpID): void
	{
		$plan = $this->explorer->table('svp')->get($svpID);
		$this->template->jmenoSVP = $plan->jmenoSVP;

		$this->template->svpID = $svpID;

		//načtení vzdělávacích aktivit
		$aktivity = $this->explorer->table('vzdelavaciAktivita')->where('svp_svpID = ?', $svpID)->fetchAll();
		$this->template->aktivity = $aktivity;
	}

	protected function createComponentActivityForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoAktivity', 'Jméno vzdělvávací aktivity:')
			->setRequired();

		$form->addTextarea('popisAktivity', 'Popis vzdělvávací aktivity:');

		$form->addSelect('typAktivity', 'Typ aktivity:', $this->explorer->table('typAktivity')->fetchPairs('typAktivityID', 'jmenoTypu'))
			->setPrompt('Vyberte typ aktivity')
			->setRequired();

		$form->addSubmit('send', 'Přidat vzdělávací aktivitu');

		$form->onSuccess[] = $this->activityFormSucceeded(...);

		return $form;
	}

	private function activityFormSucceeded(\stdClass $data): void
	{
		$svpID = $this->getParameter('svpID');

		$this->database->table('vzdelavaciAktivita')->insert([
			'svp_svpID' => $svpID,
			'jmenoAktivity' => $data->jmenoAktivity,
			'popisAktivity' => $data->popisAktivity,
			'typAktivity_typAktivityID' => $data->typAktivity
		]);

		$this->flashMessage('Vzdělávací aktivita úspěšně přidána', 'success');
		$this->redirect('this');
	}
}
