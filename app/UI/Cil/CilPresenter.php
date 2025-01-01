<?php

declare(strict_types=1);

namespace App\UI\Cil;

use App\Forms\GoalFormFactory;
use App\Forms\GoalFulfillingFormFactory;
use Nette\Application\UI\Form;
use Nette;

final class CilPresenter extends Nette\Application\UI\Presenter
{
	public function __construct(Nette\Database\Explorer $database, GoalFulfillingFormFactory $goalFulfillingFormFactory, GoalFormFactory $goalFormFactory)
	{
		parent::__construct();
		$this->explorer = $database;
		$this->goalFulfillingFormFactory = $goalFulfillingFormFactory;
		$this->goalFormFactory = $goalFormFactory;
	}

	protected $explorer;
	private GoalFormFactory $goalFormFactory;
	private GoalFulfillingFormFactory $goalFulfillingFormFactory;

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
		$cilID = (int)$this->getParameter('cilID');
		$form = $this->goalFulfillingFormFactory->create((int)$this->getParameter('svpID'));
		$form->onSuccess[] = function (\stdClass $data) use ($cilID) {
			$this->goalFulfillingFormFactory->process($data, $this->explorer, null, $cilID);
			$this->flashMessage('Plnění vzdělávacího cíle úspěšně přidáno', 'success');
			$this->redirect('this');
		};

		return $form;
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
