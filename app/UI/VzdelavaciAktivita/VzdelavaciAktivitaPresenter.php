<?php

declare(strict_types=1);

namespace App\UI\VzdelavaciAktivita;

use App\Forms\ActivityFormFactory;
use Nette\Application\UI\Form;
use Nette;

final class VzdelavaciAktivitaPresenter extends Nette\Application\UI\Presenter
{
	public function __construct(Nette\Database\Explorer $database, ActivityFormFactory $activityFormFactory)
	{
		parent::__construct();
		$this->explorer = $database;
		$this->activityFormFactory = $activityFormFactory;
	}

	protected $explorer;
	private ActivityFormFactory $activityFormFactory;

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
		
		$svpID = (int)$this->getParameter('svpID');

		$form->addText('jmenoSoucasti', 'Jméno součásti vzdělávací aktivity:')
			->setRequired();

		$form->addTextarea('popisSoucasti', 'Popis součásti vzdělávací aktivity:');

		$recursiveGetters = new \App\Services\RecursiveGetters($this->explorer);
		$vzdelavaciObsahy = $recursiveGetters->getRecursiveObsahy($svpID, null);
		$vzdelavaciObory = $recursiveGetters->getRecursiveObory($svpID, null);

		$obsahy_mezery = $recursiveGetters->createRecArray_content_breaks($vzdelavaciObsahy);
		$obory_mezery = $recursiveGetters->createRecArray_field_breaks($vzdelavaciObory);

		$form->addSelect('vzdelavaciObor', 'Vzdělávací obor:', $obory_mezery)
			->setPrompt('Vyberte vzdělávací obor')
			->setRequired();

		$form->addSelect('vzdelavaciObsah', 'Vzdělávací obsah:', $obsahy_mezery)
			->setPrompt('Vyberte vzdělávací obsah')
			->setRequired();

		$form->addSelect('rocnik', 'Ročník:', $this->explorer->table('rocnik')->fetchPairs('rocnikID', 'jmenoRocniku'))
			->setPrompt('Vyberte ročník');
	
		$form->addSelect('pomucka', 'Pomůcka:', $this->explorer->table('pomucka')->fetchPairs('pomuckaID', 'jmenoPomucky'))
			->setPrompt('Vyberte pomůcku');

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
			'vzdelavaciObsah_vzdelavaciObsahID' => $data->vzdelavaciObsah,
			'rocnik_rocnikID' => $data->rocnik,
			'pomucka_pomuckaID' => $data->pomucka
		]);

		$this->flashMessage('Součást vzdělávací aktivity úspěšně přidána', 'success');
		$this->redirect('this');
	}

	protected function createComponentActivityForm(): Form
	{
		$aktivitaID = (int)$this->getParameter('aktivitaID');
		$aktivita = $this->explorer->table('vzdelavaciAktivita')->get($aktivitaID);

		$defaultValues = [
			'jmenoAktivity' => $aktivita->jmenoAktivity,
			'popisAktivity' => $aktivita->popisAktivity,
			'typAktivity' => $aktivita->typAktivity_typAktivityID
		];

		$form = $this->activityFormFactory->create($defaultValues);
		$form->onSuccess[] = function (\stdClass $data) use ($aktivitaID) {
			$this->activityFormFactory->process($data, $this->explorer, $aktivitaID);
			$this->flashMessage('Vzdělávací aktivita úspěšně upravena', 'success');
			$this->redirect('this');
		};

		return $form;
	}
}
