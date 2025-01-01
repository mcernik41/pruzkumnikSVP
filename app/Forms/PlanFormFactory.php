<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Nette\Database\Explorer;

class PlanFormFactory
{
	private Explorer $explorer;

	public function __construct(Explorer $explorer)
	{
		$this->explorer = $explorer;
	}

	public function create(?array $defaultValues = null): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoPlanu', 'Jméno vzdělávacího plánu:')
			->setDefaultValue($defaultValues['jmenoPlanu'] ?? '')
			->setRequired();

		$form->addTextarea('popisSVP', 'Popis vzdělávacího plánu:')
			->setDefaultValue($defaultValues['popisSVP'] ?? '');

		$form->addSubmit('send', $defaultValues ? 'Upravit plán' : 'Přidat plán');

		return $form;
	}

	public function process(\stdClass $data, Explorer $database, int $planID = null, int $skolaID = null): void
	{
		$values = [
			'jmenoSVP' => $data->jmenoPlanu,
			'popisSVP' => $data->popisSVP
		];

		if ($planID) {
			$database->table('svp')
				->where('svpID', $planID)
				->update($values);
		} else {
			$values['skola_skolaID'] = $skolaID;
			$database->table('svp')->insert($values);
		}
	}
}