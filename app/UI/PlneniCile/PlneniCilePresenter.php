<?php

declare(strict_types=1);

namespace App\UI\PlneniCile;

use App\Forms\GoalFulfillingFormFactory;
use Nette\Application\UI\Form;
use Nette;

final class PlneniCilePresenter extends Nette\Application\UI\Presenter
{
	public function __construct(Nette\Database\Explorer $database, GoalFulfillingFormFactory $goalFulfillingFormFactory)
	{
		parent::__construct();
		$this->explorer = $database;
		$this->goalFulfillingFormFactory = $goalFulfillingFormFactory;
	}

	protected $explorer;
	private GoalFulfillingFormFactory $goalFulfillingFormFactory;

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
		$plneniCileID = (int)$this->getParameter('plneniCileID');
		$plneniCile = $this->explorer->table('plneniCile')->get($plneniCileID);

		$defaultValues = [
			'popisPlneni' => $plneniCile->popisPlneniCile,
			'vzdelavaciObsah' => $plneniCile->vzdelavaciObsah_vzdelavaciObsahID
		];

		$form = $this->goalFulfillingFormFactory->create((int)$this->getParameter('svpID'), $defaultValues);
		$form->onSuccess[] = function (\stdClass $data) use ($plneniCileID) {
			$this->goalFulfillingFormFactory->process($data, $this->explorer, $plneniCileID);
			$this->flashMessage('Plnění vzdělávacího cíle úspěšně upraveno', 'success');
			$this->redirect('this');
		};

		return $form;
	}
}
