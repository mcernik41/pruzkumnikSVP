<?php

declare(strict_types=1);

namespace App\UI\TypAktivity;
use Nette\Application\UI\Form;

use Nette;


final class TypAktivityPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Nette\Database\Explorer $database) 
	{
		$this->explorer = $database;
	}

	protected $explorer;

	public function renderDefault(int $typAktivityID): void
	{
		$typAktivity = $this->explorer->table('typAktivity')->get($typAktivityID);
		$this->template->jmenoTypu = $typAktivity->jmenoTypu;
	}

	protected function createComponentTypeForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form
		
		$typAktivityID = $this->getParameter('typAktivityID');
		$typAktivity = $this->explorer->table('typAktivity')->get($typAktivityID);

		$form->addText('jmenoTypu', 'Jméno typu aktivity:')
			->setDefaultValue($typAktivity->jmenoTypu)
			->setRequired();

		$form->addTextarea('popisTypu', 'Popis typu aktivity:')
			->setDefaultValue($typAktivity->popisTypu);

		$form->addSubmit('send', 'Upravit typ aktivity');

		$form->onSuccess[] = $this->typeFormSucceeded(...);

		return $form;
	}

	private function typeFormSucceeded(\stdClass $data): void
	{
		$typAktivityID = $this->getParameter('typAktivityID');

		$this->database->table('typAktivity')
			->where('typAktivityID', $typAktivityID)
			->update([
				'jmenoTypu' => $data->jmenoTypu,
				'popisTypu' => $data->popisTypu,
		]);

		$this->flashMessage('Typ aktivity úspěšně upraven', 'success');
		$this->redirect('this');
	}
}
