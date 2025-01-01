<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Nette\Database\Explorer;

class ContentFormFactory
{
	private Explorer $explorer;

	public function __construct(Explorer $explorer)
	{
		$this->explorer = $explorer;
	}

	public function create(?array $defaultValues = null): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoObsahu', 'Jméno vzdělávacího obsahu:')
			->setDefaultValue($defaultValues['jmenoObsahu'] ?? '')
			->setRequired();

		$form->addTextarea('popisObsahu', 'Popis vzdělávacího obsahu:')
			->setDefaultValue($defaultValues['popisObsahu'] ?? '');

		$form->addSubmit('send', $defaultValues ? 'Upravit vzdělávací obsah' : 'Přidat vzdělávací obsah');

		return $form;
	}

	public function process(\stdClass $data, Explorer $database, int $vzdelavaciObsahID = null, int $svpID = null, int $rodicovskyVzdelavaciObsah = null): void
	{
		$values = [
			'jmenoObsahu' => $data->jmenoObsahu,
			'popisObsahu' => $data->popisObsahu,
			'rodicovskyVzdelavaciObsahID' => $rodicovskyVzdelavaciObsah
		];

		if ($vzdelavaciObsahID) {
			$database->table('vzdelavaciObsah')
				->where('vzdelavaciObsahID', $vzdelavaciObsahID)
				->update($values);
		} else {
			$values['svp_svpID'] = $svpID;
			$database->table('vzdelavaciObsah')->insert($values);
		}
	}
}