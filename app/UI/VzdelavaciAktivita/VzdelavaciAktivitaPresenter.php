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

	public function renderDefault(int $aktivitaID): void
	{
		$aktivita = $this->explorer->table('vzdelavaciAktivita')->get($aktivitaID);
		$this->template->jmenoAktivity = $aktivita->jmenoAktivity;

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
}
