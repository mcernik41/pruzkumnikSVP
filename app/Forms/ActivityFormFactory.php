<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Nette\Database\Explorer;

class ActivityFormFactory
{
	private Explorer $explorer;

	public function __construct(Explorer $explorer)
	{
		$this->explorer = $explorer;
	}

	public function create(?array $defaultValues = null): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoAktivity', 'Jméno vzdělávací aktivity:')
			->setDefaultValue($defaultValues['jmenoAktivity'] ?? '')
			->setRequired();

		$form->addTextarea('popisAktivity', 'Popis vzdělávací aktivity:')
			->setDefaultValue($defaultValues['popisAktivity'] ?? '');

		$form->addSelect('typAktivity', 'Typ aktivity:', $this->explorer->table('typAktivity')->fetchPairs('typAktivityID', 'jmenoTypu'))
			->setDefaultValue($defaultValues['typAktivity'] ?? null)
			->setPrompt('Vyberte typ aktivity')
			->setRequired();

		$form->addSubmit('send', $defaultValues ? 'Upravit vzdělávací aktivitu' : 'Přidat vzdělávací aktivitu');

		return $form;
	}

	public function process(\stdClass $data, Explorer $database, int $aktivitaID = null, int $svpID = null): void
	{
		$values = [
			'jmenoAktivity' => $data->jmenoAktivity,
			'popisAktivity' => $data->popisAktivity,
			'typAktivity_typAktivityID' => $data->typAktivity
		];

		if ($aktivitaID) {
			$database->table('vzdelavaciAktivita')
				->where('vzdelavaciAktivitaID', $aktivitaID)
				->update($values);
		} else {
			$values['svp_svpID'] = $svpID;
			$database->table('vzdelavaciAktivita')->insert($values);
		}
	}

	public function delete(Explorer $database, int $aktivitaID): void
	{
		$database->table('vzdelavaciAktivita')->where('vzdelavaciAktivitaID', $aktivitaID)->delete();
	}
}