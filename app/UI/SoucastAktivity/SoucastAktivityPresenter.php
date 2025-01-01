<?php

declare(strict_types=1);

namespace App\UI\SoucastAktivity;
use Nette\Application\UI\Form;

use Nette;

final class SoucastAktivityPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Nette\Database\Explorer $database) 
	{
		$this->explorer = $database;
	}

	protected $explorer;

	public function renderDefault(int $soucastID, int $aktivitaID, int $svpID): void
	{
		//informace o součásti
		$soucast = $this->explorer->table('soucastAktivity')->get($soucastID);
		$this->template->jmenoSoucasti = $soucast->jmenoSoucasti;
		
		//informace o aktivitě
		$aktivita = $this->explorer->table('vzdelavaciAktivita')->get($aktivitaID);
		$this->template->jmenoAktivity = $aktivita->jmenoAktivity;
		$this->template->aktivitaID = $aktivitaID;

		$this->template->svpID = $svpID;

		//informace o oboru
		$this->template->vzdelavaciObor = $this->explorer->table('vzdelavaciObor')->get($soucast->vzdelavaciObor_vzdelavaciOborID);

		//informace o obsahu
		$this->template->vzdelavaciObsah = $this->explorer->table('vzdelavaciObsah')->get($soucast->vzdelavaciObsah_vzdelavaciObsahID);
	}

	protected function createComponentActivityPartForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form
		
		$soucastID = $this->getParameter('soucastID');
		$svpID = (int)$this->getParameter('svpID');
		$soucast = $this->explorer->table('soucastAktivity')->get($soucastID);
		$jmenoSoucasti = $soucast->jmenoSoucasti;
		$popisSoucasti = $soucast->popisSoucasti;
		$vzdelavaciObor_vzdelavaciOborID = $soucast->vzdelavaciObor_vzdelavaciOborID;
		$vzdelavaciObsah_vzdelavaciObsahID = $soucast->vzdelavaciObsah_vzdelavaciObsahID;
		$rocnikID = $soucast->rocnik_rocnikID;
		$pomuckaID = $soucast->pomucka_pomuckaID;
		$temaID = $soucast->tema_temaID;

		$form->addText('jmenoSoucasti', 'Jméno součásti vzdělávací aktivity:')
			->setDefaultValue($jmenoSoucasti)
			->setRequired();

		$form->addTextarea('popisSoucasti', 'Popis součásti vzdělávací aktivity:')
			->setDefaultValue($popisSoucasti);

		$recursiveGetters = new \App\Services\RecursiveGetters($this->explorer);
		$vzdelavaciObsahy = $recursiveGetters->getRecursiveObsahy($svpID, null);
		$vzdelavaciObory = $recursiveGetters->getRecursiveObory($svpID, null);

		$obsahy_mezery = $recursiveGetters->createRecArray_content_breaks($vzdelavaciObsahy);
		$obory_mezery = $recursiveGetters->createRecArray_field_breaks($vzdelavaciObory);

		$form->addSelect('vzdelavaciObor', 'Vzdělávací obor:', $obory_mezery)
			->setDefaultValue($vzdelavaciObor_vzdelavaciOborID)
			->setPrompt('Vyberte vzdělávací obor')
			->setRequired();

		$form->addSelect('vzdelavaciObsah', 'Vzdělávací obsah:', $obsahy_mezery)
			->setDefaultValue($vzdelavaciObsah_vzdelavaciObsahID)
			->setPrompt('Vyberte vzdělávací obsah')
			->setRequired();

		$form->addSelect('rocnik', 'Ročník:', $this->explorer->table('rocnik')->fetchPairs('rocnikID', 'jmenoRocniku'))
			->setDefaultValue($rocnikID)
			->setPrompt('Vyberte ročník');

		$form->addSelect('pomucka', 'Pomůcka:', $this->explorer->table('pomucka')->fetchPairs('pomuckaID', 'jmenoPomucky'))
			->setDefaultValue($pomuckaID)
			->setPrompt('Vyberte pomůcku');

		$form->addSelect('tema', 'Téma:', $this->explorer->table('tema')
			->where('vzdelavaciObor_vzdelavaciOborID', $vzdelavaciObor_vzdelavaciOborID)
			->where('rocnik_rocnikID', $rocnikID)
			->fetchPairs('temaID', 'jmenoTematu'))
			->setDefaultValue($temaID)
			->setPrompt('Vyberte téma');

		$form->addSubmit('send', 'Upravit součást vzdělávací aktivity');

		$form->onSuccess[] = $this->activityPartFormSucceeded(...);

		return $form;
	}

	private function activityPartFormSucceeded(\stdClass $data): void
	{
		$soucastID = $this->getParameter('soucastID');

		$this->database->table('soucastAktivity')
			->where('soucastAktivityID', $soucastID)
			->update([
				'jmenoSoucasti' => $data->jmenoSoucasti,
				'popisSoucasti' => $data->popisSoucasti,
				'vzdelavaciObor_vzdelavaciOborID' => $data->vzdelavaciObor,
				'vzdelavaciObsah_vzdelavaciObsahID' => $data->vzdelavaciObsah,
				'rocnik_rocnikID' => $data->rocnik,
				'pomucka_pomuckaID' => $data->pomucka,
				'tema_temaID' => $data->tema
		]);

		$this->flashMessage('Součást vzdělávací aktivity úspěšně upravena', 'success');
		$this->redirect('this');
	}
}