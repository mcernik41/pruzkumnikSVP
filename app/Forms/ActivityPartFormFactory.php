<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Nette\Database\Explorer;

class ActivityPartFormFactory
{
	private Explorer $explorer;

	public function __construct(Explorer $explorer)
	{
		$this->explorer = $explorer;
	}

	public function create(int $svpID, ?array $defaultValues = null): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoSoucasti', 'Jméno součásti vzdělávací aktivity:')
			->setDefaultValue($defaultValues['jmenoSoucasti'] ?? '')
			->setRequired();

		$form->addTextarea('popisSoucasti', 'Popis součásti vzdělávací aktivity:')
			->setDefaultValue($defaultValues['popisSoucasti'] ?? '');

		$recursiveGetters = new \App\Services\RecursiveGetters($this->explorer);
		$vzdelavaciObsahy = $recursiveGetters->getRecursiveObsahy($svpID, null);
		$vzdelavaciObory = $recursiveGetters->getRecursiveObory($svpID, null);

		$obsahy_mezery = $recursiveGetters->createRecArray_content_breaks($vzdelavaciObsahy);
		$obory_mezery = $recursiveGetters->createRecArray_field_breaks($vzdelavaciObory);

		$form->addSelect('vzdelavaciObor', 'Vzdělávací obor:', $obory_mezery)
			->setDefaultValue($defaultValues['vzdelavaciObor'] ?? null)
			->setPrompt('Vyberte vzdělávací obor')
			->setRequired();

		$form->addSelect('vzdelavaciObsah', 'Vzdělávací obsah:', $obsahy_mezery)
			->setDefaultValue($defaultValues['vzdelavaciObsah'] ?? null)
			->setPrompt('Vyberte vzdělávací obsah')
			->setRequired();

		$form->addSelect('rocnik', 'Ročník:', $this->explorer->table('rocnik')->fetchPairs('rocnikID', 'jmenoRocniku'))
			->setDefaultValue($defaultValues['rocnik'] ?? null)
			->setPrompt('Vyberte ročník');

		$form->addSelect('pomucka', 'Pomůcka:', $this->explorer->table('pomucka')->order('jmenoPomucky ASC')->fetchPairs('pomuckaID', 'jmenoPomucky'))
			->setDefaultValue($defaultValues['pomucka'] ?? null)
			->setPrompt('Vyberte pomůcku');

		$form->addSelect('tema', 'Téma:', $this->explorer->table('tema')->order('jmenoTematu ASC')->fetchPairs('temaID', 'jmenoTematu'))
			->setDefaultValue($defaultValues['tema'] ?? null)
			->setPrompt('Vyberte téma');

		$form->addSubmit('send', $defaultValues ? 'Upravit součást vzdělávací aktivity' : 'Přidat součást vzdělávací aktivity');

		return $form;
	}

	public function process(\stdClass $data, Explorer $database, int $soucastID = null, int $aktivitaID = null): void
	{
		$values = [
			'jmenoSoucasti' => $data->jmenoSoucasti,
			'popisSoucasti' => $data->popisSoucasti,
			'vzdelavaciObor_vzdelavaciOborID' => $data->vzdelavaciObor,
			'vzdelavaciObsah_vzdelavaciObsahID' => $data->vzdelavaciObsah,
			'rocnik_rocnikID' => $data->rocnik,
			'pomucka_pomuckaID' => $data->pomucka,
			'tema_temaID' => $data->tema
		];

		if ($soucastID) {
			$database->table('soucastAktivity')
				->where('soucastAktivityID', $soucastID)
				->update($values);
		} else {
			$values['vzdelavaciAktivita_vzdelavaciAktivitaID'] = $aktivitaID;
			$database->table('soucastAktivity')->insert($values);
		}
	}

	public function delete(Explorer $database, int $soucastID): void
	{
		$database->table('soucastAktivity')->where('soucastAktivityID', $soucastID)->delete();
	}
}