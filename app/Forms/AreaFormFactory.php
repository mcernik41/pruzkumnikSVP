<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Nette\Database\Explorer;

class AreaFormFactory
{
	private Explorer $explorer;

	public function __construct(Explorer $explorer)
	{
		$this->explorer = $explorer;
	}

	public function create(?array $defaultValues = null): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoOboru', 'Jméno vzdělávacího oboru:')
			->setDefaultValue($defaultValues['jmenoOboru'] ?? '')
			->setRequired();

		$form->addTextarea('popisOboru', 'Popis vzdělávacího oboru:')
			->setDefaultValue($defaultValues['popisOboru'] ?? '');

		$form->addSubmit('send', $defaultValues ? 'Upravit vzdělávací obor' : 'Přidat vzdělávací obor');

		return $form;
	}

	public function process(\stdClass $data, Explorer $database, int $vzdelavaciOborID = null, int $svpID = null, int $rodicovskyVzdelavaciObor = null): void
	{
		$values = [
			'jmenoOboru' => $data->jmenoOboru,
			'popisOboru' => $data->popisOboru,
			'rodicovskyVzdelavaciOborID' => $rodicovskyVzdelavaciObor
		];

		if ($vzdelavaciOborID) {
			$database->table('vzdelavaciObor')
				->where('vzdelavaciOborID', $vzdelavaciOborID)
				->update($values);
		} else {
			$values['svp_svpID'] = $svpID;
			$database->table('vzdelavaciObor')->insert($values);
		}
	}

    public function delete(Explorer $database, int $vzdelavaciOborID): void
    {
        $database->table('vzdelavaciObor')->where('vzdelavaciOborID', $vzdelavaciOborID)->delete();
    }
}