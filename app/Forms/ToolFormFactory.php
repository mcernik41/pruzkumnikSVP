<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Nette\Database\Explorer;

class ToolFormFactory
{
	private Explorer $explorer;

	public function __construct(Explorer $explorer)
	{
		$this->explorer = $explorer;
	}

	public function create(?array $defaultValues = null): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoPomucky', 'Jméno pomůcky:')
			->setDefaultValue($defaultValues['jmenoPomucky'] ?? '')
			->setRequired();

		$form->addTextarea('popisPomucky', 'Popis pomůcky:')
			->setDefaultValue($defaultValues['popisPomucky'] ?? '');

		$form->addSubmit('send', $defaultValues ? 'Upravit pomůcku' : 'Přidat pomůcku');

		return $form;
	}

	public function process(\stdClass $data, Explorer $database, int $pomuckaID = null): void
	{
		$values = [
			'jmenoPomucky' => $data->jmenoPomucky,
			'popisPomucky' => $data->popisPomucky
		];

		if ($pomuckaID) {
			$database->table('pomucka')
				->where('pomuckaID', $pomuckaID)
				->update($values);
		} else {
			$database->table('pomucka')->insert($values);
		}
	}

	public function delete(Explorer $database, int $pomuckaID): void
	{
		$database->table('pomucka')->where('pomuckaID', $pomuckaID)->delete();
	}
}