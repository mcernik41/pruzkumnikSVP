<?php

declare(strict_types=1);

namespace App\UI\Skola;

use App\Forms\SchoolFormFactory;
use Nette\Application\UI\Form;
use Nette;

final class SkolaPresenter extends Nette\Application\UI\Presenter
{
	public function __construct(Nette\Database\Explorer $database, SchoolFormFactory $schoolFormFactory)
	{
		parent::__construct();
		$this->explorer = $database;
		$this->schoolFormFactory = $schoolFormFactory;
	}

	protected $explorer;
	private SchoolFormFactory $schoolFormFactory;

	public function renderDefault(int $skolaID): void
	{
		$skola = $this->explorer->table('skola')->get($skolaID);
		$this->template->jmenoSkoly = $skola->jmenoSkoly;

		$this->template->vzdelavaciPlany = $skola->related('svp');
	}

	protected function createComponentPlanForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoPlanu', 'Jméno vzdělvávacího plánu:')
			->setRequired();

		$form->addTextarea('popisSVP', 'Popis vzdělvávacího plánu:');

		$form->addSubmit('send', 'Přidat vzdělávací plán');

		$form->onSuccess[] = $this->planFormSucceeded(...);

		return $form;
	}

	private function planFormSucceeded(\stdClass $data): void
	{
		$skolaID = $this->getParameter('skolaID');

		$this->database->table('svp')->insert([
			'skola_skolaID' => $skolaID,
			'jmenoSVP' => $data->jmenoPlanu,
			'popisSVP' => $data->popisSVP,
		]);

		$this->flashMessage('Vzdělávací plán úspěšně přidán', 'success');
		$this->redirect('this');
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
