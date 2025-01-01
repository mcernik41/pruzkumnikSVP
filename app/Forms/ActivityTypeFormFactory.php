<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Nette\Database\Explorer;

class ActivityTypeFormFactory
{
	private Explorer $explorer;

	public function __construct(Explorer $explorer)
	{
		$this->explorer = $explorer;
	}

	public function create(?array $defaultValues = null): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoTypu', 'Jméno typu aktivity:')
			->setDefaultValue($defaultValues['jmenoTypu'] ?? '')
			->setRequired();

		$form->addTextarea('popisTypu', 'Popis typu aktivity:')
			->setDefaultValue($defaultValues['popisTypu'] ?? '');

		$form->addSubmit('send', $defaultValues ? 'Upravit typ aktivity' : 'Přidat typ aktivity');

		return $form;
	}

	public function process(\stdClass $data, Explorer $database, int $typAktivityID = null): void
	{
		$values = [
			'jmenoTypu' => $data->jmenoTypu,
			'popisTypu' => $data->popisTypu
		];

		if ($typAktivityID) {
			$database->table('typAktivity')
				->where('typAktivityID', $typAktivityID)
				->update($values);
		} else {
			$database->table('typAktivity')->insert($values);
		}
	}

	public function delete(Explorer $database, int $typAktivityID): void
	{
		$database->table('typAktivity')->where('typAktivityID', $typAktivityID)->delete();
	}
}