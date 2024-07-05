<?php

declare(strict_types=1);

namespace App\UI\VzdelavaciAktivita;
use Nette\Application\UI\Form;

use Nette;


final class VzdelavaciAktivitaPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Nette\Database\Explorer $database) 
	{
		$this->explorer = $database;
	}

	protected $explorer;

	public function renderDefault(int $aktivitaID, int $svpID): void
	{
		$aktivita = $this->explorer->table('vzdelavaciAktivita')->get($aktivitaID);
		$this->template->jmenoAktivity = $aktivita->jmenoAktivity;
		$this->template->aktivitaID = $aktivitaID;

		$this->template->svpID = $svpID;

		$this->template->soucastiAktivity = $aktivita->related('soucastAktivity');
	}

	protected function createComponentActivityPartForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoSoucasti', 'Jméno součásti vzdělávací aktivity:')
			->setRequired();

		$form->addTextarea('popisSoucasti', 'Popis součásti vzdělávací aktivity:');

		$form->addSelect('vzdelavaciObor', 'Vzdělávací obor:', $this->explorer->table('vzdelavaciObor')->fetchPairs('vzdelavaciOborID', 'jmenoOboru'))
			->setPrompt('Vyberte vzdělávací obor')
			->setRequired();

		$form->addSelect('vzdelavaciObsah', 'Vzdělávací obsah:', $this->explorer->table('vzdelavaciObsah')->fetchPairs('vzdelavaciObsahID', 'jmenoObsahu'))
			->setPrompt('Vyberte vzdělávací obsah')
			->setRequired();

		$form->addSubmit('send', 'Přidat součást vzdělávací aktivity');

		$form->onSuccess[] = $this->activityPartFormSucceeded(...);

		return $form;
	}

	private function activityPartFormSucceeded(\stdClass $data): void
	{
		$aktivitaID = $this->getParameter('aktivitaID');

		$this->database->table('soucastAktivity')->insert([
			'vzdelavaciAktivita_vzdelavaciAktivitaID' => $aktivitaID,
			'jmenoSoucasti' => $data->jmenoSoucasti,
			'popisSoucasti' => $data->popisSoucasti,
			'vzdelavaciObor_vzdelavaciOborID' => $data->vzdelavaciObor,
			'vzdelavaciObsah_vzdelavaciObsahID' => $data->vzdelavaciObsah
		]);

		$this->flashMessage('Vzdělávací plán úspěšně přidán', 'success');
		$this->redirect('this');
	}

	protected function createComponentActivityForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form
		
		$aktivitaID = $this->getParameter('aktivitaID');
		$aktivita = $this->explorer->table('vzdelavaciAktivita')->get($aktivitaID);
		$jmenoAktivity = $aktivita->jmenoAktivity;
		$popisAktivity = $aktivita->popisAktivity;
		$typAktivity = $aktivita->typAktivity;

		$form->addText('jmenoAktivity', 'Jméno vzdělvávací aktivity:')
			->setDefaultValue($jmenoAktivity)
			->setRequired();

		$form->addTextarea('popisAktivity', 'Popis vzdělvávací aktivity:')
			->setDefaultValue($popisAktivity);

		$form->addSelect('typAktivity', 'Typ aktivity:', $this->explorer->table('typAktivity')->fetchPairs('typAktivityID', 'jmenoTypu'))
			->setDefaultValue($typAktivity)
			->setPrompt('Vyberte typ aktivity')
			->setRequired();

		$form->addSubmit('send', 'Upravit vzdělávací aktivitu');

		$form->onSuccess[] = $this->activityFormSucceeded(...);

		return $form;
	}

	private function activityFormSucceeded(\stdClass $data): void
	{
		$aktivitaID = $this->getParameter('aktivitaID');

		$this->database->table('vzdelavaciAktivita')
			->where('vzdelavaciAktivitaID = ?', $aktivitaID)
			->update([
				'jmenoAktivity' => $data->jmenoAktivity,
				'popisAktivity' => $data->popisAktivity,
				'typAktivity_typAktivityID' => $data->typAktivity
		]);

		$this->flashMessage('Vzdělávací aktivita úspěšně upravena', 'success');
		$this->redirect('this');
	}
}
