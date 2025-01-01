<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Nette\Database\Explorer;

class GradeFormFactory
{
	private Explorer $explorer;

	public function __construct(Explorer $explorer)
	{
		$this->explorer = $explorer;
	}

	public function create(?array $defaultValues = null): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoRocniku', 'Jméno ročníku:')
			->setDefaultValue($defaultValues['jmenoRocniku'] ?? '')
			->setRequired();

		$form->addTextarea('popisRocniku', 'Popis ročníku:')
			->setDefaultValue($defaultValues['popisRocniku'] ?? '');

		$form->addSubmit('send', $defaultValues ? 'Upravit ročník' : 'Přidat ročník');

		return $form;
	}

	public function process(\stdClass $data, Explorer $database, int $rocnikID = null): void
	{
		$values = [
			'jmenoRocniku' => $data->jmenoRocniku,
			'popisRocniku' => $data->popisRocniku
		];

		if ($rocnikID) {
			$database->table('rocnik')
				->where('rocnikID', $rocnikID)
				->update($values);
		} else {
			$database->table('rocnik')->insert($values);
		}
	}

	public function delete(Explorer $database, int $rocnikID): void
	{
		$database->table('rocnik')->where('rocnikID', $rocnikID)->delete();
	}
}