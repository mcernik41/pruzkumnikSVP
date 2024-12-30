<?php

declare(strict_types=1);

namespace App\UI\Rocnik;
use Nette\Application\UI\Form;

use Nette;


final class RocnikPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Nette\Database\Explorer $database) 
	{
		$this->explorer = $database;
	}

	protected $explorer;

	public function renderDefault(int $rocnikID): void
	{
		$rocnik = $this->explorer->table('rocnik')->get($rocnikID);
		$this->template->nazevRocniku = $rocnik->nazevRocniku;
	}

	protected function createComponentGradeForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form
		
		$rocnikID = $this->getParameter('rocnikID');
		$rocnik = $this->explorer->table('rocnik')->get($rocnikID);

		$form->addText('nazevRocniku', 'Jméno ročníku:')
			->setDefaultValue($rocnik->nazevRocniku)
			->setRequired();

		$form->addTextarea('popisRocniku', 'Popis ročníku:')
			->setDefaultValue($rocnik->popisRocniku);

		$form->addSubmit('send', 'Upravit ročník');

		$form->onSuccess[] = $this->gradeFormSucceeded(...);

		return $form;
	}

	private function gradeFormSucceeded(\stdClass $data): void
	{
		$rocnikID = $this->getParameter('rocnikID');

		$this->database->table('rocnik')
			->where('rocnikID', $rocnikID)
			->update([
				'nazevRocniku' => $data->nazevRocniku,
				'popisRocniku' => $data->popisRocniku,
		]);

		$this->flashMessage('Ročník úspěšně upraven', 'success');
		$this->redirect('this');
	}
}
