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
		$aktivita = $this->explorer->table('cil')->get($cilID);
		$this->template->jmenoCile = $aktivita->jmenoCile;
		$this->template->cilID = $cilID;

		$this->template->svpID = $svpID;

		$this->template->plneniCile = $aktivita->related('plneniCile');
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
}
