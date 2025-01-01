<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Nette\Database\Explorer;

class GoalFulfillingFormFactory
{
	private Explorer $explorer;

	public function __construct(Explorer $explorer)
	{
		$this->explorer = $explorer;
	}

	public function create(?array $defaultValues = null): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addTextarea('popisPlneni', 'Popis plnění:')
			->setDefaultValue($defaultValues['popisPlneni'] ?? '')
			->setRequired();

		$form->addSelect('vzdelavaciObsah', 'Vzdělávací obsah:', $this->explorer->table('vzdelavaciObsah')->fetchPairs('vzdelavaciObsahID', 'jmenoObsahu'))
			->setDefaultValue($defaultValues['vzdelavaciObsah'] ?? null)
			->setPrompt('Vyberte vzdělávací obsah');

		$form->addSubmit('send', $defaultValues ? 'Upravit plnění' : 'Přidat plnění');

		return $form;
	}

	public function process(\stdClass $data, Explorer $database, int $plneniCileID = null, int $cilID = null): void
	{
		$values = [
			'popisPlneniCile' => $data->popisPlneni,
			'vzdelavaciObsah_vzdelavaciObsahID' => $data->vzdelavaciObsah
		];

		if ($plneniCileID) {
			$database->table('plneniCile')
				->where('plneniCileID', $plneniCileID)
				->update($values);
		} else {
			$values['cil_cilID'] = $cilID;
			$database->table('plneniCile')->insert($values);
		}
	}
}