<?php

declare(strict_types=1);

namespace App\UI\PlneniCile;
use Nette\Application\UI\Form;

use Nette;


final class PlneniCilePresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Nette\Database\Explorer $database) 
	{
		$this->explorer = $database;
	}

	protected $explorer;

	public function renderDefault(int $plneniCileID, int $cilID, int $svpID): void
	{
		//informace o součásti
		$plneni = $this->explorer->table('plneniCile')->get($plneniCileID);
		$this->template->popisPlneni = $plneni->popisPlneniCile;
		
		//informace o aktivitě
		$aktivita = $this->explorer->table('cil')->get($cilID);
		$this->template->jmenoCile = $aktivita->jmenoCile;
		$this->template->cilID = $cilID;

		$this->template->svpID = $svpID;

		//informace o obsahu
		$this->template->vzdelavaciObsah = $this->explorer->table('vzdelavaciObsah')->get($plneni->vzdelavaciObsah_vzdelavaciObsahID);
	}

	protected function createComponentGoalFulfillingForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form
		
		$plneniCileID = $this->getParameter('plneniCileID');
		$plneni = $this->explorer->table('plneniCile')->get($plneniCileID);
		$popisPlneni = $plneni->popisPlneniCile;
		$vzdelavaciObsah = $this->explorer->table('vzdelavaciObsah')->get($plneni->vzdelavaciObsah_vzdelavaciObsahID);

		$form->addSelect('vzdelavaciObsah', 'Obsah vedoucí k plnění cíle:', $this->explorer->table('vzdelavaciObsah')->fetchPairs('vzdelavaciObsahID', 'jmenoObsahu'))
			->setDefaultValue($vzdelavaciObsah->vzdelavaciObsahID)
			->setPrompt('Vyberte vzdělávací obsah')
			->setRequired();

		$form->addTextarea('popisPlneni', 'Popis plnění cíle:')
			->setDefaultValue($popisPlneni);

		$form->addSubmit('send', 'Upravit plnění cíle');

		$form->onSuccess[] = $this->goalFulfillingFormSucceeded(...);

		return $form;
	}

	private function goalFulfillingFormSucceeded(\stdClass $data): void
	{
		$plneniCileID = $this->getParameter('plneniCileID');

		$this->database->table('plneniCile')
			->where('plneniCileID', $plneniCileID)	
			->update([
				'popisPlneniCile' => $data->popisPlneni,
				'vzdelavaciObsah_vzdelavaciObsahID' => $data->vzdelavaciObsah
		]);

		$this->flashMessage('Plnění vzdělávacího cíle úspěšně upraveno', 'success');
		$this->redirect('this');
	}
}
