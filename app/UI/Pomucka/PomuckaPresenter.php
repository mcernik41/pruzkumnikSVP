<?php

declare(strict_types=1);

namespace App\UI\Pomucka;
use Nette\Application\UI\Form;

use Nette;


final class PomuckaPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Nette\Database\Explorer $database) 
	{
		$this->explorer = $database;
	}

	protected $explorer;

	public function renderDefault(int $pomuckaID): void
	{
		$pomucka = $this->explorer->table('pomucka')->get($pomuckaID);
		$this->template->nazevPomucky = $pomucka->nazevPomucky;
	}

	protected function createComponentToolForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form
		
		$pomuckaID = $this->getParameter('pomuckaID');
		$pomucka = $this->explorer->table('pomucka')->get($pomuckaID);

		$form->addText('nazevPomucky', 'Jméno pomůcky:')
			->setDefaultValue($pomucka->nazevPomucky)
			->setRequired();

		$form->addTextarea('popisPomucky', 'Popis pomůcky:')
			->setDefaultValue($pomucka->popisPomucky);

		$form->addSubmit('send', 'Upravit pomůcku');

		$form->onSuccess[] = $this->toolFormSucceeded(...);

		return $form;
	}

	private function toolFormSucceeded(\stdClass $data): void
	{
		$pomuckaID = $this->getParameter('pomuckaID');

		$this->database->table('pomucka')
			->where('pomuckaID', $pomuckaID)
			->update([
				'nazevPomucky' => $data->nazevPomucky,
				'popisPomucky' => $data->popisPomucky,
		]);

		$this->flashMessage('Pomůcka úspěšně upravena', 'success');
		$this->redirect('this');
	}
}
