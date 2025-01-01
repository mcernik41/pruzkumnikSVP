<?php

declare(strict_types=1);

namespace App\UI\VzdelavaciAktivita;

use App\Forms\ActivityFormFactory;
use App\Forms\ActivityPartFormFactory;
use Nette\Application\UI\Form;
use Nette;

final class VzdelavaciAktivitaPresenter extends Nette\Application\UI\Presenter
{
	private ActivityFormFactory $activityFormFactory;
	private ActivityPartFormFactory $activityPartFormFactory;

	public function __construct(Nette\Database\Explorer $database, ActivityFormFactory $activityFormFactory, ActivityPartFormFactory $activityPartFormFactory)
	{
		parent::__construct();
		$this->explorer = $database;
		$this->activityFormFactory = $activityFormFactory;
		$this->activityPartFormFactory = $activityPartFormFactory;
	}

	protected $explorer;

	public function renderDefault(int $aktivitaID, int $svpID): void
	{
		$aktivita = $this->explorer->table('vzdelavaciAktivita')->get($aktivitaID);
		$this->template->jmenoAktivity = $aktivita->jmenoAktivity;
		$this->template->aktivitaID = $aktivitaID;

		$this->template->svpID = $svpID;

		$this->template->soucastiAktivity = $aktivita->related('soucastAktivity');
	}

	protected function createComponentActivityPartForm(): Form
	{
		$svpID = (int)$this->getParameter('svpID');

		$form = $this->activityPartFormFactory->create($svpID);
		$form->onSuccess[] = function (\stdClass $data) {
			$this->activityPartFormFactory->process($data, $this->explorer, null, (int)$this->getParameter('aktivitaID'));
			$this->flashMessage('Součást vzdělávací aktivity úspěšně přidána', 'success');
			$this->redirect('this');
		};

		return $form;
	}

	protected function createComponentActivityForm(): Form
	{
		$aktivitaID = $this->getParameter('aktivitaID');
		$aktivita = $this->explorer->table('vzdelavaciAktivita')->get($aktivitaID);

		$defaultValues = [
			'jmenoAktivity' => $aktivita->jmenoAktivity,
			'popisAktivity' => $aktivita->popisAktivity,
			'typAktivity' => $aktivita->typAktivity_typAktivityID
		];

		$form = $this->activityFormFactory->create($defaultValues);
		$form->onSuccess[] = function (\stdClass $data) use ($aktivitaID) {
			$this->activityFormFactory->process($data, $this->explorer, $aktivitaID);
			$this->flashMessage('Vzdělávací aktivita úspěšně upravena', 'success');
			$this->redirect('this');
		};

		return $form;
	}

	public function handleDeleteActivityPart(int $id): void
	{
		$this->activityPartFormFactory->delete($this->explorer, $id);
		$this->flashMessage('Součást vzdělávací aktivity úspěšně odebrána', 'success');
		$this->redirect('this');
	}
}
