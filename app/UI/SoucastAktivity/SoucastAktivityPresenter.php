<?php

declare(strict_types=1);

namespace App\UI\SoucastAktivity;

use App\Forms\ActivityPartFormFactory;
use Nette\Application\UI\Form;
use Nette;

final class SoucastAktivityPresenter extends Nette\Application\UI\Presenter
{
	public function __construct(Nette\Database\Explorer $database, ActivityPartFormFactory $activityPartFormFactory)
	{
		parent::__construct();
		$this->explorer = $database;
		$this->activityPartFormFactory = $activityPartFormFactory;
	}

	protected $explorer;
	private ActivityPartFormFactory $activityPartFormFactory;

	public function renderDefault(int $soucastID, int $aktivitaID, int $svpID): void
	{
		//informace o součásti
		$soucast = $this->explorer->table('soucastAktivity')->get($soucastID);
		$this->template->jmenoSoucasti = $soucast->jmenoSoucasti;
		
		//informace o aktivitě
		$aktivita = $this->explorer->table('vzdelavaciAktivita')->get($aktivitaID);
		$this->template->jmenoAktivity = $aktivita->jmenoAktivity;
		$this->template->aktivitaID = $aktivitaID;

		$this->template->svpID = $svpID;

		//informace o oboru
		$this->template->vzdelavaciObor = $this->explorer->table('vzdelavaciObor')->get($soucast->vzdelavaciObor_vzdelavaciOborID);

		//informace o obsahu
		$this->template->vzdelavaciObsah = $this->explorer->table('vzdelavaciObsah')->get($soucast->vzdelavaciObsah_vzdelavaciObsahID);
	}

	protected function createComponentActivityPartForm(): Form
	{
		$soucastID = (int)$this->getParameter('soucastID');
		$svpID = (int)$this->getParameter('svpID');
		$soucast = $this->explorer->table('soucastAktivity')->get($soucastID);

		$defaultValues = [
			'jmenoSoucasti' => $soucast->jmenoSoucasti,
			'popisSoucasti' => $soucast->popisSoucasti,
			'vzdelavaciObor' => $soucast->vzdelavaciObor_vzdelavaciOborID,
			'vzdelavaciObsah' => $soucast->vzdelavaciObsah_vzdelavaciObsahID,
			'rocnik' => $soucast->rocnik_rocnikID,
			'pomucka' => $soucast->pomucka_pomuckaID,
			'tema' => $soucast->tema_temaID
		];

		$form = $this->activityPartFormFactory->create($svpID, $defaultValues);
		$form->onSuccess[] = function (\stdClass $data) use ($soucastID) {
			$this->activityPartFormFactory->process($data, $this->explorer, $soucastID);
			$this->flashMessage('Součást vzdělávací aktivity úspěšně upravena', 'success');
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