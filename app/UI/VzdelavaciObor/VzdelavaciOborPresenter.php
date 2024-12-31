<?php

declare(strict_types=1);

namespace App\UI\VzdelavaciObor;
use Nette\Application\UI\Form;

use Nette;


final class VzdelavaciOborPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Nette\Database\Explorer $database) 
	{
		$this->explorer = $database;
	}

	protected $explorer;

	public function renderDefault(int $svpID, int $vzdelavaciOborID): void
	{
		$plan = $this->explorer->table('svp')->get($svpID);
		$this->template->jmenoSVP = $plan->jmenoSVP;

		$this->template->svpID = $svpID;
		$this->template->vzdelavaciOborID = $vzdelavaciOborID;

		//načtení vzdělávacích oborů
		if ($vzdelavaciOborID === -1) 
		{
			$obory = $this->explorer->table('vzdelavaciObor')
				->where('rodicovskyVzdelavaciOborID IS NULL AND svp_svpID = ?', $svpID)
				->fetchAll();
		} 
		else 
		{
			$obory = $this->explorer->table('vzdelavaciObor')
				->where('rodicovskyVzdelavaciOborID = ?', $vzdelavaciOborID)
				->fetchAll();

			$obor = $this->explorer->table('vzdelavaciObor')->get($vzdelavaciOborID);
			$this->template->jmenoOboru = $obor->jmenoOboru;
		}

		$this->template->vzdelavaciObory = $obory;

		//nalezení součástí vzdělávacích aktivit - podle obsahu a aktivity
		$this->nacistAktivity($vzdelavaciOborID);
	}

	private function nacistAktivity($vzdelavaciOborID)
	{
		//najdu všechny aktivity, které se váží k tomuto oboru
		$soucastiAktivit = $this->explorer->table('soucastAktivity')
			->where('vzdelavaciObor_vzdelavaciOborID = ?', $vzdelavaciOborID)
			->fetchAll();

		//najdu ID obsahů, do kterých patří nalezené aktivity
		$vzdelavaciObsahyID = [];
		foreach ($soucastiAktivit as $soucastAktivity) { $vzdelavaciObsahyID[] = $soucastAktivity->vzdelavaciObsah_vzdelavaciObsahID; }
		$vzdelavaciObsahyID = array_unique($vzdelavaciObsahyID);

		//najdu příslušné obsahy
		$obsahy = $this->explorer->table('vzdelavaciObsah')
			->where('vzdelavaciObsahID', $vzdelavaciObsahyID)
			->fetchAll();

		$obsahyKOboru = [];

		foreach($obsahy as $obsah)
		{
			$soucastiAktivit = $this->explorer->table('soucastAktivity')
				->where('vzdelavaciObor_vzdelavaciOborID = ?', $vzdelavaciOborID)
				->where('vzdelavaciObsah_vzdelavaciObsahID = ?', $obsah)
				->fetchAll();

			$vzdelavaciAktivityID = [];
			foreach ($soucastiAktivit as $soucastAktivity) { $vzdelavaciAktivityID[] = $soucastAktivity->vzdelavaciAktivita_vzdelavaciAktivitaID; }
			$vzdelavaciAktivityID = array_unique($vzdelavaciAktivityID);

			$aktivity = $this->explorer->table('vzdelavaciAktivita')
				->where('vzdelavaciAktivitaID', $vzdelavaciAktivityID)
				->fetchAll();

			$obsahKOboru = new Obsah($obsah->vzdelavaciObsahID, $obsah->jmenoObsahu);

			foreach($aktivity as $aktivita)
			{
				$aktivitaKObsahu = new Aktivita($aktivita->vzdelavaciAktivitaID, $aktivita->jmenoAktivity);

				$soucastiAktivit = $this->explorer->table('soucastAktivity')
					->where('vzdelavaciObor_vzdelavaciOborID = ?', $vzdelavaciOborID)
					->where('vzdelavaciObsah_vzdelavaciObsahID = ?', $obsah)
					->where('vzdelavaciAktivita_vzdelavaciAktivitaID = ?', $aktivita)
					->fetchAll();

				foreach($soucastiAktivit as $soucastAktivity)
				{
					$aktivitaKObsahu->soucastiAktivity[] = $soucastAktivity;
				}

				$obsahKOboru->aktivity[] = $aktivitaKObsahu;
			}

			$obsahyKOboru[] = $obsahKOboru;
		}

		$this->template->obsahy = $obsahyKOboru;
	}

	protected function createComponentAreaForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoOboru', 'Jméno vzdělvávacího oboru:')
			->setRequired();

		$form->addTextarea('popisOboru', 'Popis vzdělvávacího oboru:');

		$form->addSubmit('send', 'Přidat vzdělávací obor');

		$form->onSuccess[] = $this->areaFormSucceeded(...);

		return $form;
	}

	private function areaFormSucceeded(\stdClass $data): void
	{
		$svpID = $this->getParameter('svpID');
		$vzdelavaciOborID = $this->getParameter('vzdelavaciOborID');

		$this->database->table('vzdelavaciObor')->insert([
			'svp_svpID' => $svpID,
			'rodicovskyVzdelavaciOborID' => ($vzdelavaciOborID == -1) ? null : $vzdelavaciOborID,
			'jmenoOboru' => $data->jmenoOboru,
			'popisOboru' => $data->popisOboru,
		]);

		$this->flashMessage('Vzdělávací obor úspěšně přidán', 'success');
		$this->redirect('this');
	}

	protected function createComponentAreaModifyForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$vzdelavaciOborID = $this->getParameter('vzdelavaciOborID');		
		$obor = $this->explorer->table('vzdelavaciObor')->get($vzdelavaciOborID);

		$form->addText('jmenoOboru', 'Jméno vzdělvávacího oboru:')
			->setDefaultValue($obor->jmenoOboru)
			->setRequired();

		$form->addTextarea('popisOboru', 'Popis vzdělvávacího oboru:')
			->setDefaultValue($obor->popisOboru);

		$form->addSubmit('send', 'Upravit vzdělávací obor');

		$form->onSuccess[] = $this->areaModifyFormSucceeded(...);

		return $form;
	}

	private function areaModifyFormSucceeded(\stdClass $data): void
	{
		$vzdelavaciOborID = $this->getParameter('vzdelavaciOborID');

		$this->database->table('vzdelavaciObor')
			->where('vzdelavaciOborID', $vzdelavaciOborID)
			->update([
				'jmenoOboru' => $data->jmenoOboru,
				'popisOboru' => $data->popisOboru,
		]);

		$this->flashMessage('Vzdělávací obor úspěšně upraven', 'success');
		$this->redirect('this');
	}

	public function handleNahratOborySVP_NV(int $svpID)
	{
		$dataInsetrer = new \App\Services\DataInserter($this->explorer);
		$dataInsetrer->insertFields($svpID);

		$this->redirect('this');
	}
}

class Obsah
{
	public int $obsahID;
	public string $jmenoObsahu;
	public array $aktivity;
	
	public function __construct(int $obsahID, string $jmenoObsahu)
	{
		$this->obsahID = $obsahID;
		$this->jmenoObsahu = $jmenoObsahu;
		$this->aktivity = [];
	}
}

class Aktivita
{
	public int $aktivitaID;
	public string $jmenoAktivity;
	public array $soucastiAktivity;
	
	public function __construct(int $aktivitaID, string $jmenoAktivity)
	{
		$this->aktivitaID = $aktivitaID;
		$this->jmenoAktivity = $jmenoAktivity;
		$this->soucastiAktivity = [];
	}
}