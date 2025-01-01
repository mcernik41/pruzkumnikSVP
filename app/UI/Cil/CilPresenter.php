<?php

declare(strict_types=1);

namespace App\UI\Cil;

use App\Forms\GoalFormFactory;
use Nette\Application\UI\Form;
use Nette;

final class CilPresenter extends Nette\Application\UI\Presenter
{
	public function __construct(Nette\Database\Explorer $database, GoalFormFactory $goalFormFactory)
	{
		parent::__construct();
		$this->explorer = $database;
		$this->goalFormFactory = $goalFormFactory;
	}

	protected $explorer;
	private GoalFormFactory $goalFormFactory;

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

		$form->addTextarea('popisPlneni', 'Popis plnění cíle:');

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

		$this->flashMessage('Plnění vzdělávacího cíle úspěšně přidáno', 'success');
		$this->redirect('this');
	}

	protected function createComponentGoalForm(): Form
	{
		$cilID = (int)$this->getParameter('cilID');
		$cil = $this->explorer->table('cil')->get($cilID);

		$defaultValues = [
			'jmenoCile' => $cil->jmenoCile,
			'popisCile' => $cil->popisCile
		];

		$form = $this->goalFormFactory->create($defaultValues);
		$form->onSuccess[] = function (\stdClass $data) use ($cilID) {
			$this->goalFormFactory->process($data, $this->explorer, $cilID, (int)$this->getParameter('svpID'));
			$this->flashMessage('Cíl úspěšně upraven', 'success');
			$this->redirect('this');
		};

		return $form;
	}
}
