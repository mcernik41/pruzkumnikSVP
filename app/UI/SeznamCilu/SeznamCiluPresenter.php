<?php

declare(strict_types=1);

namespace App\UI\SeznamCilu;

use App\Forms\GoalFormFactory;
use Nette\Application\UI\Form;
use Nette;

final class SeznamCiluPresenter extends Nette\Application\UI\Presenter
{

	public function __construct(Nette\Database\Explorer $database, GoalFormFactory $goalFormFactory)
	{
		parent::__construct();
		$this->explorer = $database;
		$this->goalFormFactory = $goalFormFactory;
	}

	protected $explorer;
	protected $svpID;
	private GoalFormFactory $goalFormFactory;

	public function renderDefault(int $svpID): void
	{
		$plan = $this->explorer->table('svp')->get($svpID);
		$this->template->jmenoSVP = $plan->jmenoSVP;
		$this->template->svpID = $plan->svpID;

		$this->svpID = $svpID;

		//načtení cílů
		$cile = $this->explorer->table('cil')->where('svp_svpID = ?', $svpID)->fetchAll();
		$this->template->cile = $cile;
	}

	protected function createComponentGoalForm(): Form
	{
		$form = $this->goalFormFactory->create();
		$form->onSuccess[] = function (\stdClass $data) {
			$this->goalFormFactory->process($data, $this->explorer, null, (int)$this->getParameter('svpID'));
			$this->flashMessage('Cíl úspěšně přidán', 'success');
			$this->redirect('this');
		};

		return $form;
	}

	public function handleDeleteGoal(int $id): void
	{
		$this->goalFormFactory->delete($this->explorer, $id);
		$this->flashMessage('Cíl úspěšně odebrán', 'success');
		$this->redirect('this');
	}
}
