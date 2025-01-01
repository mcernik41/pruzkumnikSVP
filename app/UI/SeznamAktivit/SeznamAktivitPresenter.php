<?php

declare(strict_types=1);

namespace App\UI\SeznamAktivit;

use App\Forms\ActivityFormFactory;
use Nette\Application\UI\Form;
use Nette;

final class SeznamAktivitPresenter extends Nette\Application\UI\Presenter
{
	public function __construct(Nette\Database\Explorer $database, ActivityFormFactory $activityFormFactory)
	{
		parent::__construct();
		$this->explorer = $database;
		$this->activityFormFactory = $activityFormFactory;
	}

	protected $explorer;
	private ActivityFormFactory $activityFormFactory;

	public function renderDefault(int $svpID): void
	{
		$plan = $this->explorer->table('svp')->get($svpID);
		$this->template->jmenoSVP = $plan->jmenoSVP;

		$this->template->svpID = $svpID;

		//načtení typů vzdělávacích aktivit
		$this->template->typyAktivit = $this->explorer->table('typAktivity')->order('jmenoTypu')->fetchAll();

		//načtení vzdělávacích aktivit
		$rows = $this->explorer->table('vzdelavaciAktivita')->where('svp_svpID = ?', $svpID)->order('jmenoAktivity ASC')->fetchAll();

		//získání nejvyššího ID vzdělávací aktivity
		$maxID = $this->explorer->table('vzdelavaciAktivita')->max('vzdelavaciAktivitaID');
		
		$aktivity = [];
		for($i = 1; $i <= $maxID; $i++)
		{
			$aktivity[$i] = [];
		}		

		foreach ($rows as $row) 
		{
			$aktivity[$row->typAktivity_typAktivityID][] = $row;
		}
		$this->template->aktivity = $aktivity;
	}

	protected function createComponentActivityForm(): Form
	{
		$form = $this->activityFormFactory->create();
		$form->onSuccess[] = function (\stdClass $data) {
			$this->activityFormFactory->process($data, $this->explorer, null, (int)$this->getParameter('svpID'));
			$this->flashMessage('Vzdělávací aktivita úspěšně přidána', 'success');
			$this->redirect('this');
		};

		return $form;
	}

	public function handleDeleteActivity(int $id): void
	{
		$this->activityFormFactory->delete($this->explorer, $id);
		$this->flashMessage('Vzdělávací aktivita úspěšně odebrána', 'success');
		$this->redirect('this');
	}
}
