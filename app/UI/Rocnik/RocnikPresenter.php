<?php

declare(strict_types=1);

namespace App\UI\Rocnik;

use App\Forms\GradeFormFactory;
use Nette\Application\UI\Form;
use Nette;

final class RocnikPresenter extends Nette\Application\UI\Presenter
{
	private GradeFormFactory $gradeFormFactory;

	public function __construct(Nette\Database\Explorer $database, GradeFormFactory $gradeFormFactory)
	{
		parent::__construct();
		$this->explorer = $database;
		$this->gradeFormFactory = $gradeFormFactory;
	}

	protected $explorer;

	public function renderDefault(int $rocnikID): void
	{
		$rocnik = $this->explorer->table('rocnik')->get($rocnikID);
		$this->template->jmenoRocniku = $rocnik->jmenoRocniku;
	}

	protected function createComponentGradeForm(): Form
	{
		$rocnikID = (int)$this->getParameter('rocnikID');
		$rocnik = $this->explorer->table('rocnik')->get($rocnikID);

		$defaultValues = [
			'jmenoRocniku' => $rocnik->jmenoRocniku,
			'popisRocniku' => $rocnik->popisRocniku
		];

		$form = $this->gradeFormFactory->create($defaultValues);
		$form->onSuccess[] = function (\stdClass $data) use ($rocnikID) {
			$this->gradeFormFactory->process($data, $this->explorer, $rocnikID);
			$this->flashMessage('Ročník úspěšně upraven', 'success');
			$this->redirect('this');
		};

		return $form;
	}
}
