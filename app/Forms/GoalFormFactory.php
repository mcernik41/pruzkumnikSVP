<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Nette\Database\Explorer;

class GoalFormFactory
{
	private Explorer $explorer;

	public function __construct(Explorer $explorer)
	{
		$this->explorer = $explorer;
	}

	public function create(?array $defaultValues = null): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoCile', 'Jméno cíle:')
			->setDefaultValue($defaultValues['jmenoCile'] ?? '')
			->setRequired();

		$form->addTextarea('popisCile', 'Popis cíle:')
			->setDefaultValue($defaultValues['popisCile'] ?? '');

		$form->addSubmit('send', $defaultValues ? 'Upravit cíl' : 'Přidat cíl');

		return $form;
	}

	public function process(\stdClass $data, Explorer $database, int $cilID = null, int $svpID): void
	{
		$values = [
			'jmenoCile' => $data->jmenoCile,
			'popisCile' => $data->popisCile,
			'svp_svpID' => $svpID
		];

		if ($cilID) {
			$database->table('cil')
				->where('cilID', $cilID)
				->update($values);
		} else {
			$database->table('cil')->insert($values);
		}
	}

    public function delete(Explorer $database, int $cilID): void
    {
        $database->table('cil')->where('cilID', $cilID)->delete();
    }
}