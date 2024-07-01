<?php

declare(strict_types=1);

namespace App\UI\VzdelavaciObsah;
use Nette\Application\UI\Form;
use Nette\Database\Explorer;

use Nette;


final class VzdelavaciObsahPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Explorer $database) 
	{
		$this->explorer = $database;
	}

	protected $explorer;

	public function renderDefault(int $svpID, int $vzdelavaciObsahID): void
	{
		$plan = $this->explorer->table('svp')->get($svpID);
		$this->template->jmenoSVP = $plan->jmenoSVP;

		$this->template->svpID = $svpID;
		$this->template->vzdelavaciObsahID = $vzdelavaciObsahID;

		//načtení vzdělávacích obsahů
		if ($vzdelavaciObsahID === -1) 
		{
			$obsahy = $this->explorer->table('vzdelavaciObsah')
				->where('rodicovskyVzdelavaciObsahID IS NULL')
				->fetchAll();
		} 
		else 
		{
			$obsahy = $this->explorer->table('vzdelavaciObsah')
				->where('rodicovskyVzdelavaciObsahID = ?', $vzdelavaciObsahID)
				->fetchAll();

			$obsah = $this->explorer->table('vzdelavaciObsah')->get($vzdelavaciObsahID);
			$this->template->jmenoObsahu = $obsah->jmenoObsahu;
		}

		$this->template->vzdelavaciObsahy = $obsahy;
	}

	protected function createComponentContentForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('jmenoObsahu', 'Jméno vzdělvávacího obsahu:')
			->setRequired();

		$form->addTextarea('popisObsahu', 'Popis vzdělvávacího obsahu:');

		$form->addSubmit('send', 'Přidat vzdělávací obsah');

		//$form->addHidden('vzdelavaciObsahID', $this->template->vzdelavaciObsahID);

		$form->onSuccess[] = $this->contentFormSucceeded(...);

		return $form;
	}

	private function contentFormSucceeded(\stdClass $data): void
	{
		$svpID = $this->getParameter('svpID');
		$vzdelavaciObsahID = $this->getParameter('vzdelavaciObsahID');

		$this->database->table('vzdelavaciObsah')->insert([
			'svp_svpID' => $svpID,
			'rodicovskyVzdelavaciObsahID' => ($vzdelavaciObsahID == -1) ? null : $vzdelavaciObsahID,
			'jmenoObsahu' => $data->jmenoObsahu,
			'popisObsahu' => $data->popisObsahu,
		]);

		$this->flashMessage('Vzdělávací obsah úspěšně přidán', 'success');
		$this->redirect('this');
	}

	public function handleNahratObsahSVP_NV(int $svpID)
	{
		$this->insertVzdelavaciObsah($this->explorer, $svpID);

		$this->redirect('this');
	}

	//vhodné: přesunout do samostatného souboru
	private function insertVzdelavaciObsah(Explorer $database, int $svpID): void {
		// Insert hlavních kategorií a získání jejich ID
		$database->query('INSERT INTO `pruzkumnikSVP`.`vzdelavaciObsah`', [
			'jmenoObsahu' => 'Klíčové kompetence',
			'popisObsahu' => 'Klíčové kompetence jsou základní schopnosti, které žáci potřebují pro úspěch v životě.',
			'rodicovskyVzdelavaciObsahID' => null,
			'svp_svpID' => $svpID
		]);
		$klicoveKompetenceID = $database->getInsertId();

		$database->query('INSERT INTO `pruzkumnikSVP`.`vzdelavaciObsah`', [
			'jmenoObsahu' => 'Základní gramotnosti',
			'popisObsahu' => 'Základní gramotnosti jsou klíčové dovednosti v oblasti čtení, psaní a matematiky.',
			'rodicovskyVzdelavaciObsahID' => null,
			'svp_svpID' => $svpID
		]);
		$zakladniGramotnostiID = $database->getInsertId();

		$database->query('INSERT INTO `pruzkumnikSVP`.`vzdelavaciObsah`', [
			'jmenoObsahu' => 'Průřezová témata',
			'popisObsahu' => 'Průřezová témata integrují vzdělávací obsah napříč různými obory a podporují komplexní rozvoj žáků.',
			'rodicovskyVzdelavaciObsahID' => null,
			'svp_svpID' => $svpID
		]);
		$prurezovaTemataID = $database->getInsertId();

		// Insert Klíčové kompetence a jejich složek
		$kompetence = [
			['Klíčová kompetence k učení', 'Umění se učit, smysl a cíl učení, celoživotní učení.'],
			['Klíčová kompetence komunikační', 'Porozumění a vyjádření, komunikace v různých kontextech.'],
			['Klíčová kompetence osobnostní a sociální', 'Vlastní wellbeing, resilience, identita, empatie a porozumění druhým, budování a udržování zdravých vztahů.'],
			['Klíčová kompetence k občanství a udržitelnosti', 'Aktivní občanství a participace, udržitelný rozvoj.'],
			['Klíčová kompetence k podnikavosti a pracovní', 'Nápady, příležitosti a výzvy, mobilizování zdrojů, realizace akcí, aktivit, projektů, spolupráce a týmová práce.'],
			['Klíčová kompetence k řešení problémů', 'Řešení běžných problematických situací, kritické hodnocení a využití vědeckého poznání, badatelství.'],
			['Klíčová kompetence kulturní', 'Kulturní povědomí a vyjadřování, interpretace a hodnocení kulturních a uměleckých projevů.'],
			['Klíčová kompetence digitální', 'Digitální gramotnost a bezpečnost, kritické myšlení a práce s informacemi.']
		];

		$kompetenceIDs = [];
		foreach ($kompetence as $kompetence) {
			$database->query('INSERT INTO `pruzkumnikSVP`.`vzdelavaciObsah`', [
				'jmenoObsahu' => $kompetence[0],
				'popisObsahu' => $kompetence[1],
				'rodicovskyVzdelavaciObsahID' => $klicoveKompetenceID,
				'svp_svpID' => $svpID
			]);
			$kompetenceIDs[] = $database->getInsertId();
		}

		// Insert Základní gramotnosti a jejich složek
		$gramotnosti = [
			['Čtenářská a pisatelská gramotnost', 'Čtenářská nezávislost, vztah ke čtení a čtenářství, psaní a pisatelství, syntéza a tvorba.'],
			['Logicko-matematická gramotnost', 'Matematická reflexe, řešení matematických situací, aplikace matematiky v různých kontextech.']
		];

		$gramotnostiIDs = [];
		foreach ($gramotnosti as $gramotnost) {
			$database->query('INSERT INTO `pruzkumnikSVP`.`vzdelavaciObsah`', [
				'jmenoObsahu' => $gramotnost[0],
				'popisObsahu' => $gramotnost[1],
				'rodicovskyVzdelavaciObsahID' => $zakladniGramotnostiID,
				'svp_svpID' => $svpID
			]);
			$gramotnostiIDs[] = $database->getInsertId();
		}

		// Insert Průřezová témata a jejich složek
		$temata = [
			['Péče o wellbeing', 'Fyzická a duševní pohoda, sociální a emocionální zdraví.'],
			['Společnost pro všechny', 'Inkluze a rovné příležitosti, aktivní občanství a demokracie.'],
			['Udržitelné prostředí', 'Ekologická výchova, ochrana životního prostředí, udržitelné technologie.']
		];

		$temataIDs = [];
		foreach ($temata as $tema) {
			$database->query('INSERT INTO `pruzkumnikSVP`.`vzdelavaciObsah`', [
				'jmenoObsahu' => $tema[0],
				'popisObsahu' => $tema[1],
				'rodicovskyVzdelavaciObsahID' => $prurezovaTemataID,
				'svp_svpID' => $svpID
			]);
			$temataIDs[] = $database->getInsertId();
		}

		// Insert složek pro klíčovou kompetenci osobnostní a sociální
		$osobnostniSlozky = [
			['Vlastní wellbeing', 'Péče o fyzické a duševní zdraví.'],
			['Resilience', 'Schopnost překonávat překážky.'],
			['Identita', 'Sebepoznání a rozvoj osobnosti.'],
			['Empatie a porozumění druhým', 'Schopnost porozumět a vcítit se do druhých.'],
			['Budování a udržování zdravých vztahů', 'Tvorba a udržování pozitivních vztahů s ostatními.']
		];

		foreach ($osobnostniSlozky as $slozka) {
			$database->query('INSERT INTO `pruzkumnikSVP`.`vzdelavaciObsah`', [
				'jmenoObsahu' => $slozka[0],
				'popisObsahu' => $slozka[1],
				'rodicovskyVzdelavaciObsahID' => $kompetenceIDs[2],  // Klíčová kompetence osobnostní a sociální
				'svp_svpID' => $svpID
			]);
		}

		// Insert složek pro klíčovou kompetenci k podnikavosti a pracovní
		$podnikavostSlozky = [
			['Nápady, příležitosti a výzvy', 'Rozvoj kreativity a podnikavosti.'],
			['Mobilizování zdrojů', 'Efektivní využívání dostupných zdrojů.'],
			['Realizace akcí, aktivit, projektů', 'Plánování a realizace různých aktivit.'],
			['Spolupráce a týmová práce', 'Práce v týmu a spolupráce s ostatními.']
		];

		foreach ($podnikavostSlozky as $slozka) {
			$database->query('INSERT INTO `pruzkumnikSVP`.`vzdelavaciObsah`', [
				'jmenoObsahu' => $slozka[0],
				'popisObsahu' => $slozka[1],
				'rodicovskyVzdelavaciObsahID' => $kompetenceIDs[4],  // Klíčová kompetence k podnikavosti a pracovní
				'svp_svpID' => $svpID
			]);
		}

		// Insert složek pro čtenářskou a pisatelskou gramotnost
		$ctenarskaSlozky = [
			['Čtenářská nezávislost', 'Schopnost samostatně číst a rozumět textům.'],
			['Vztah ke čtení a čtenářství', 'Pozitivní vztah k četbě a literatuře.'],
			['Psaní a pisatelství', 'Rozvoj schopnosti psát různé typy textů.'],
			['Syntéza a tvorba', 'Schopnost kombinovat a tvořit nové texty.']
		];

		foreach ($ctenarskaSlozky as $slozka) {
			$database->query('INSERT INTO `pruzkumnikSVP`.`vzdelavaciObsah`', [
				'jmenoObsahu' => $slozka[0],
				'popisObsahu' => $slozka[1],
				'rodicovskyVzdelavaciObsahID' => $gramotnostiIDs[0],  // Čtenářská a pisatelská gramotnost
				'svp_svpID' => $svpID
			]);
		}

		// Insert složek pro logicko-matematickou gramotnost
		$matematickaSlozky = [
			['Matematická reflexe', 'Schopnost kriticky zhodnotit matematické postupy.'],
			['Řešení matematických situací', 'Praktické využití matematiky v různých situacích.'],
			['Aplikace matematiky v různých kontextech', 'Použití matematických znalostí v reálném životě.']
		];

		foreach ($matematickaSlozky as $slozka) {
			$database->query('INSERT INTO `pruzkumnikSVP`.`vzdelavaciObsah`', [
				'jmenoObsahu' => $slozka[0],
				'popisObsahu' => $slozka[1],
				'rodicovskyVzdelavaciObsahID' => $gramotnostiIDs[1],  // Logicko-matematická gramotnost
				'svp_svpID' => $svpID
			]);
		}

		// Insert složek pro průřezové téma péče o wellbeing
		$wellbeingSlozky = [
			['Fyzická pohoda', 'Péče o fyzické zdraví.'],
			['Duševní pohoda', 'Péče o duševní zdraví.'],
			['Sociální zdraví', 'Budování zdravých sociálních vztahů.'],
			['Emocionální zdraví', 'Rozvoj emocionálního zdraví a stability.']
		];

		foreach ($wellbeingSlozky as $slozka) {
			$database->query('INSERT INTO `pruzkumnikSVP`.`vzdelavaciObsah`', [
				'jmenoObsahu' => $slozka[0],
				'popisObsahu' => $slozka[1],
				'rodicovskyVzdelavaciObsahID' => $temataIDs[0],  // Péče o wellbeing
				'svp_svpID' => $svpID
			]);
		}

		// Insert složek pro průřezové téma společnost pro všechny
		$spolecnostSlozky = [
			['Inkluze', 'Začlenění všech žáků bez ohledu na rozdíly.'],
			['Rovné příležitosti', 'Zajištění rovnosti ve vzdělávání.'],
			['Aktivní občanství', 'Podpora aktivního zapojení do společnosti.'],
			['Demokracie', 'Výchova k demokratickým hodnotám.']
		];

		foreach ($spolecnostSlozky as $slozka) {
			$database->query('INSERT INTO `pruzkumnikSVP`.`vzdelavaciObsah`', [
				'jmenoObsahu' => $slozka[0],
				'popisObsahu' => $slozka[1],
				'rodicovskyVzdelavaciObsahID' => $temataIDs[1],  // Společnost pro všechny
				'svp_svpID' => $svpID
			]);
		}

		// Insert složek pro průřezové téma udržitelné prostředí
		$udrzitelneSlozky = [
			['Ekologická výchova', 'Vzdělávání zaměřené na ochranu životního prostředí.'],
			['Ochrana životního prostředí', 'Praktické aktivity na ochranu přírody.'],
			['Udržitelné technologie', 'Podpora využívání technologií šetrných k životnímu prostředí.']
		];

		foreach ($udrzitelneSlozky as $slozka) {
			$database->query('INSERT INTO `pruzkumnikSVP`.`vzdelavaciObsah`', [
				'jmenoObsahu' => $slozka[0],
				'popisObsahu' => $slozka[1],
				'rodicovskyVzdelavaciObsahID' => $temataIDs[2],  // Udržitelné prostředí
				'svp_svpID' => $svpID
			]);
		}
	}
}
