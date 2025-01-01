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

	public function create(int $svpID, ?array $defaultValues = null): Form
	{
		$form = new Form; // means Nette\Application\UI\Form
		
		$recursiveGetters = new \App\Services\RecursiveGetters($this->explorer);
		$vzdelavaciObsahy = $recursiveGetters->getRecursiveObsahy($svpID, null);
		$obsahy_mezery = $recursiveGetters->createRecArray_content_breaks($vzdelavaciObsahy);

		$form->addTextarea('popisPlneni', 'Popis plnění:')
			->setDefaultValue($defaultValues['popisPlneni'] ?? '');

		$form->addSelect('vzdelavaciObsah', 'Vzdělávací obsah:', $obsahy_mezery)
			->setDefaultValue($defaultValues['vzdelavaciObsah'] ?? null)
			->setPrompt('Vyberte vzdělávací obsah')
			->setRequired();

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

    public function delete(Explorer $database, int $plneniCileID): void
    {
        $database->table('plneniCile')->where('plneniCileID', $plneniCileID)->delete();
    }
}