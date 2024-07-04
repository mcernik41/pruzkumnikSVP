<?php

declare(strict_types=1);

namespace App\UI\Cil;
use Nette\Application\UI\Form;

use Nette;


final class CilPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Nette\Database\Explorer $database) 
	{
		$this->explorer = $database;
	}

	protected $explorer;

	public function renderDefault(int $cilID, int $svpID): void
	{
		$cil = $this->explorer->table('cil')->get($cilID);
		$this->template->jmenoCile = $cil->jmenoCile;
		$this->template->cilID = $cilID;

		$this->template->svpID = $svpID;

		$this->template->plneniCile = $cil->related('plneniCile');
		$this->template->vzdelavaciObsah = $this->explorer->table('vzdelavaciObsah')->fetchAll();
	}

	protected function createComponentGoalFulfillingForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addSelect('vzdelavaciObsah', 'Obsah vedoucí k plnění cíle:', $this->explorer->table('vzdelavaciObsah')->fetchPairs('vzdelavaciObsahID', 'jmenoObsahu'))
			->setPrompt('Vyberte vzdělávací obsah')
			->setRequired();

		$form->addTextarea('popisPlneni', 'Popis součásti vzdělávací aktivity:');

		$form->addSubmit('send', 'Přidat plnění cíle');

		$form->onSuccess[] = $this->goalFulfillingFormSucceeded(...);

		return $form;
	}

	private function goalFulfillingFormSucceeded(\stdClass $data): void
	{
		$cilID = $this->getParameter('cilID');

		$this->database->table('plneniCile')->insert([
			'cil_cilID' => $cilID,
			'popisPlneniCile' => $data->popisPlneni,
			'vzdelavaciObsah_vzdelavaciObsahID' => $data->vzdelavaciObsah
		]);

		$this->flashMessage('Vzdělávací plán úspěšně přidán', 'success');
		$this->redirect('this');
	}

	protected function createComponentGoalForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form
		
		$cilID = $this->getParameter('cilID');
		$cil = $this->explorer->table('cil')->get($cilID);

		$form->addText('jmenoCile', 'Jméno cíle:')
			->setDefaultValue($cil->jmenoCile)
			->setRequired();

		$form->addTextarea('popisCile', 'Popis cíle:')
			->setDefaultValue($cil->popisCile);

		$form->addSubmit('send', 'Upravit cíl');

		$form->onSuccess[] = $this->goalFormSucceeded(...);

		return $form;
	}

	private function goalFormSucceeded(\stdClass $data): void
	{
		$cilID = $this->getParameter('cilID');

		$this->database->table('cil')
			->where('cilID', $cilID)
			->update([
				'jmenoCile' => $data->jmenoCile,
				'popisCile' => $data->popisCile,
		]);

		$this->flashMessage('Cíl úspěšně upraven', 'success');
		$this->redirect('this');
	}
}
