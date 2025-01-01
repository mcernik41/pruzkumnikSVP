<?php

declare(strict_types=1);

namespace App\UI\Skola;

use App\Forms\SchoolFormFactory;
use App\Forms\PlanFormFactory;
use Nette\Application\UI\Form;
use Nette;

final class SkolaPresenter extends Nette\Application\UI\Presenter
{
	public function __construct(Nette\Database\Explorer $database, PlanFormFactory $planFormFactory, SchoolFormFactory $schoolFormFactory)
	{
		parent::__construct();
		$this->explorer = $database;
		$this->planFormFactory = $planFormFactory;
		$this->schoolFormFactory = $schoolFormFactory;
	}

	protected $explorer;
	private SchoolFormFactory $schoolFormFactory;
	private PlanFormFactory $planFormFactory;

	public function renderDefault(int $skolaID): void
	{
		$skola = $this->explorer->table('skola')->get($skolaID);
		$this->template->jmenoSkoly = $skola->jmenoSkoly;

		$this->template->vzdelavaciPlany = $skola->related('svp');
	}

	protected function createComponentPlanForm(): Form
	{
		$form = $this->planFormFactory->create();
		$form->onSuccess[] = function (\stdClass $data) {
			$skolaID = (int)$this->getParameter('skolaID');
			$this->planFormFactory->process($data, $this->explorer, null, $skolaID);
			$this->flashMessage('Vzdělávací plán úspěšně přidán', 'success');
			$this->redirect('this');
		};

		return $form;
	}

	protected function createComponentSchoolForm(): Form
	{
		$skolaID = (int)$this->getParameter('skolaID');
		$skola = $this->explorer->table('skola')->get($skolaID);

		$defaultValues = [
			'name' => $skola->jmenoSkoly
		];

		$form = $this->schoolFormFactory->create($defaultValues);
		$form->onSuccess[] = function (\stdClass $data) use ($skolaID) {
			$this->schoolFormFactory->process($data, $this->explorer, $skolaID);
			$this->flashMessage('Škola úspěšně upravena', 'success');
			$this->redirect('this');
		};

		return $form;
	}
}
