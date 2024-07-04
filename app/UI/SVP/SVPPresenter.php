<?php

declare(strict_types=1);

namespace App\UI\SVP;
use Nette\Application\UI\Form;
use App\Services\sqlRunner;
use Nette\Database\Explorer;

use Nette;

final class SVPPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Explorer $database, sqlRunner $runner) 
	{
		$this->explorer = $database;
		$this->sqlRunner = $runner;
	}

	protected $explorer;
	protected $sqlRunner;

	public function renderDefault(int $svpID): void
	{
		$plan = $this->explorer->table('svp')->get($svpID);
		$this->template->jmenoSVP = $plan->jmenoSVP;
		$this->template->svpID = $plan->svpID;
	}

	protected function createComponentPlanForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form
		
		$svpID = $this->getParameter('svpID');
		$plan = $this->explorer->table('svp')->get($svpID);

		$form->addText('jmenoPlanu', 'Jméno vzdělvávacího plánu:')
			->setDefaultValue($plan->jmenoSVP)
			->setRequired();

		$form->addTextarea('popisSVP', 'Popis vzdělvávacího plánu:')
			->setDefaultValue($plan->popisSVP);

		$form->addSubmit('send', 'Upravit vzdělávací plán');

		$form->onSuccess[] = $this->planFormSucceeded(...);

		return $form;
	}

	private function planFormSucceeded(\stdClass $data): void
	{
		$svpID = $this->getParameter('svpID');

		$this->database->table('svp')
			->where('svpID', $svpID)
			->update([
				'jmenoSVP' => $data->jmenoPlanu,
				'popisSVP' => $data->popisSVP,
		]);

		$this->flashMessage('Vzdělávací plán úspěšně upraven', 'success');
		$this->redirect('this');
	}

}
