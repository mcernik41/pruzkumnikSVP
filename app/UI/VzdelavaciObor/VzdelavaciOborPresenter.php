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
					$soucastAktivity_akt = new SoucastAktivity($soucastAktivity->soucastAktivityID, $soucastAktivity->jmenoSoucasti, $soucastAktivity->popisSoucasti);
					$aktivitaKObsahu->soucastiAktivity[] = $soucastAktivity_akt;
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

	public function handleNahratOborySVP_NV(int $svpID)
	{
		$this->nahratVzdelavaciObory($svpID, $this->vzdelavaciStruktura);

		$this->redirect('this');
	}

	public function nahratVzdelavaciObory(int $svpID, array $vzdelavaciStruktura)
    {
        foreach ($vzdelavaciStruktura as $oblast => $obory) {
            $oblastID = $this->explorer->table('vzdelavaciObor')->insert([
                'jmenoOboru' => $oblast,
                'svp_svpID' => $svpID
            ])->getPrimary();

            foreach ($obory as $obor => $tematickeOkruhy) {
                $oborID = $this->explorer->table('vzdelavaciObor')->insert([
                    'jmenoOboru' => $obor,
                    'rodicovskyVzdelavaciOborID' => $oblastID,
                    'svp_svpID' => $svpID
                ])->getPrimary();

                foreach ($tematickeOkruhy as $okruh) {
                    $this->explorer->table('vzdelavaciObor')->insert([
                        'jmenoOboru' => $okruh,
                        'rodicovskyVzdelavaciOborID' => $oborID,
                        'svp_svpID' => $svpID
                    ]);
                }
            }
        }
    }

	private array $vzdelavaciStruktura = [
		'Jazyk a jazyková komunikace' => [
			'Český jazyk a literatura' => [''],
			'Cizí jazyk' => [
				'Receptivní řečové dovednosti (poslech s porozuměním a čtení s porozuměním)',
				'Produktivní řečové dovednosti (mluvení a psaní)',
				'Interaktivní řečové dovednosti (ústní a písemná komunikace)',
				'Mediace (pouze pro 2. stupeň)'
			]
		],
		'Matematika a její aplikace' => [
			'Matematika' => ['Algebra']
		],
		'Informatika' => [
			'Informatika' => [
				'Data, informace a modelování',
				'Algoritmizace a programování',
				'Informační systémy',
				'Digitální technologie'
			]
		],
		'Člověk a jeho svět' => [
			'Člověk a jeho svět' => [
				'Místo, kde žijeme',
				'Lidé kolem nás',
				'Lidé a čas',
				'Rozmanitost přírody',
				'Člověk, jeho zdraví a bezpečí',
				'Lidé a svět financí'
			]
		],
		'Člověk a společnost' => [
			'Dějepis' => ['Tvoříme dějiny'],
			'Výchova k občanství' => ['Já ve společnosti', 'Odpovědný občan']
		],
		'Umění a kultura' => [
			'Hudební, taneční a dramatická výchova' => [
				'Recepce a reflexe uměleckého díla',
				'Kulturní povědomí a jednání',
				'Interpretace, vlastní tvorba a její sdílení'
			]
		],
		'Člověk, zdraví a bezpečí' => [
			'Výchova ke zdraví a bezpečí' => ['Činnosti ovlivňující pohybové učení a zdraví']
		],
		'Člověk, jeho osobnost a svět práce' => [
			'Polytechnická výchova a praktické činnosti' => [
				'Konstrukční činnosti a automatizace',
				'Práce s technickým materiálem a technická tvořivost',
				'Péče o domácnost a zahradu'
			]
		]
	];
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

class SoucastAktivity
{
	public int $soucastID;
	public string $jmenoSoucasti;
	public string $popisSoucasti;

	public function __construct(int $soucastID, string $jmenoSoucasti, string $popisSoucasti)
	{
		$this->soucastID = $soucastID;
		$this->jmenoSoucasti = $jmenoSoucasti;
		$this->popisSoucasti = $popisSoucasti;
	}
}