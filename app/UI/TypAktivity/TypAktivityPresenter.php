<?php

declare(strict_types=1);

namespace App\UI\TypAktivity;

use App\Forms\ActivityTypeFormFactory;
use Nette\Application\UI\Form;
use Nette;

final class TypAktivityPresenter extends Nette\Application\UI\Presenter
{
	private ActivityTypeFormFactory $activityTypeFormFactory;

	public function __construct(Nette\Database\Explorer $database, ActivityTypeFormFactory $activityTypeFormFactory)
	{
		parent::__construct();
		$this->explorer = $database;
		$this->activityTypeFormFactory = $activityTypeFormFactory;
	}

	protected $explorer;

	public function renderDefault(int $typAktivityID): void
	{
		$typAktivity = $this->explorer->table('typAktivity')->get($typAktivityID);
		$this->template->jmenoTypu = $typAktivity->jmenoTypu;
	}

	protected function createComponentActivityTypeForm(): Form
	{
		$typAktivityID = (int)$this->getParameter('typAktivityID');
		$typAktivity = $this->explorer->table('typAktivity')->get($typAktivityID);

		$defaultValues = [
			'jmenoTypu' => $typAktivity->jmenoTypu,
			'popisTypu' => $typAktivity->popisTypu
		];

		$form = $this->activityTypeFormFactory->create($defaultValues);
		$form->onSuccess[] = function (\stdClass $data) use ($typAktivityID) {
			$this->activityTypeFormFactory->process($data, $this->explorer, $typAktivityID);
			$this->flashMessage('Typ aktivity úspěšně upraven', 'success');
			$this->redirect('this');
		};

		return $form;
	}

	public function handleDeleteActivityType(int $id): void
	{
		$this->activityTypeFormFactory->delete($this->explorer, $id);
		$this->flashMessage('Typ aktivity úspěšně odebrán', 'success');
		$this->redirect('this');
	}
}
