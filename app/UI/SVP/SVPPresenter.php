<?php

declare(strict_types=1);

namespace App\UI\SVP;

use App\Forms\PlanFormFactory;
use Nette\Application\UI\Form;
use Nette;

final class SVPPresenter extends Nette\Application\UI\Presenter
{
	public function __construct(Nette\Database\Explorer $database, PlanFormFactory $planFormFactory) 
	{
		parent::__construct();
		$this->explorer = $database;
		$this->planFormFactory = $planFormFactory;
	}

	protected $explorer;
	private PlanFormFactory $planFormFactory;

	public function renderDefault(int $svpID): void
	{
		$plan = $this->explorer->table('svp')->get($svpID);
		$this->template->jmenoSVP = $plan->jmenoSVP;
		$this->template->svpID = $plan->svpID;
	}

	protected function createComponentPlanForm(): Form
	{
		$planID = (int)$this->getParameter('svpID');
		$plan = $this->explorer->table('svp')->get($planID);

		$defaultValues = [
			'jmenoPlanu' => $plan->jmenoSVP,
			'popisSVP' => $plan->popisSVP
		];

		$form = $this->planFormFactory->create($defaultValues);
		$form->onSuccess[] = function (\stdClass $data) use ($planID) {
			$this->planFormFactory->process($data, $this->explorer, $planID);
			$this->flashMessage('Vzdělávací plán úspěšně upraven', 'success');
			$this->redirect('this');
		};

		return $form;
	}
}