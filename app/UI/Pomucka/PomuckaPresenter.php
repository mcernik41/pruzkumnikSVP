<?php

declare(strict_types=1);

namespace App\UI\Pomucka;

use App\Forms\ToolFormFactory;
use Nette\Application\UI\Form;
use Nette;

final class PomuckaPresenter extends Nette\Application\UI\Presenter
{
	public function __construct(Nette\Database\Explorer $database, ToolFormFactory $toolFormFactory)
	{
		parent::__construct();
		$this->explorer = $database;
		$this->toolFormFactory = $toolFormFactory;
	}

	protected $explorer;
	private ToolFormFactory $toolFormFactory;

	public function renderDefault(int $pomuckaID): void
	{
		$pomucka = $this->explorer->table('pomucka')->get($pomuckaID);
		$this->template->jmenoPomucky = $pomucka->jmenoPomucky;
	}

	protected function createComponentToolForm(): Form
	{
		$pomuckaID = (int)$this->getParameter('pomuckaID');
		$pomucka = $this->explorer->table('pomucka')->get($pomuckaID);

		$defaultValues = [
			'jmenoPomucky' => $pomucka->jmenoPomucky,
			'popisPomucky' => $pomucka->popisPomucky
		];

		$form = $this->toolFormFactory->create($defaultValues);
		$form->onSuccess[] = function (\stdClass $data) use ($pomuckaID) {
			$this->toolFormFactory->process($data, $this->explorer, $pomuckaID);
			$this->flashMessage('Pomůcka úspěšně upravena', 'success');
			$this->redirect('this');
		};

		return $form;
	}

	public function handleDeleteTool(int $id): void
	{
		$this->toolFormFactory->delete($this->explorer, $id);
		$this->flashMessage('Pomůcka úspěšně odebrána', 'success');
		$this->redirect('this');
	}
}
