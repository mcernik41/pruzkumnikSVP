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
		$this->nahratVzdelavaciObsah($svpID, $this->vzdelavaciStruktura);

		$this->redirect('this');
	}	

	public function nahratVzdelavaciObsah(int $svpID, array $vzdelavaciStruktura)
    {
        foreach ($vzdelavaciStruktura as $oblast => $obory) {
            $oblastID = $this->explorer->table('vzdelavaciObsah')->insert([
                'jmenoObsahu' => $oblast,
                'svp_svpID' => $svpID
            ])->getPrimary();

            foreach ($obory as $obor => $tematickeOkruhy) {
                $oborID = $this->explorer->table('vzdelavaciObsah')->insert([
                    'jmenoObsahu' => $obor,
                    'rodicovskyVzdelavaciObsahID' => $oblastID,
                    'svp_svpID' => $svpID
                ])->getPrimary();

                foreach ($tematickeOkruhy as $okruh) {
                    $this->explorer->table('vzdelavaciObsah')->insert([
                        'jmenoObsahu' => $okruh,
                        'rodicovskyVzdelavaciObsahID' => $oborID,
                        'svp_svpID' => $svpID
                    ]);
                }
            }
        }
    }

	private array $vzdelavaciStruktura = [
		'Klíčové kompetence' => [
			'Klíčová kompetence k učení' => [
				'Umění se učit',
				'Smysl a cíl učení',
				'Celoživotní učení'
			],
			'Klíčová kompetence komunikační' => [
				'Porozumění a vyjádření',
				'Komunikace v různých kontextech'
			],
			'Klíčová kompetence osobnostní a sociální' => [
				'Vlastní wellbeing',
				'Resilience',
				'Identita',
				'Empatie a porozumění druhým',
				'Budování a udržování zdravých vztahů'
			],
			'Klíčová kompetence k občanství a udržitelnosti' => [
				'Aktivní občanství a participace',
				'Udržitelný rozvoj'
			],
			'Klíčová kompetence k podnikavosti a pracovní' => [
				'Nápady, příležitosti a výzvy',
				'Mobilizování zdrojů',
				'Realizace akcí, aktivit, projektů',
				'Spolupráce a týmová práce'
			],
			'Klíčová kompetence k řešení problémů' => [
				'Řešení běžných problematických situací',
				'Kritické hodnocení a využití vědeckého poznání',
				'Badatelství'
			],
			'Klíčová kompetence kulturní' => [
				'Kulturní povědomí a vyjadřování',
				'Interpretace a hodnocení kulturních a uměleckých projevů'
			],
			'Klíčová kompetence digitální' => [
				'Digitální gramotnost a bezpečnost',
				'Kritické myšlení a práce s informacemi'
			]
		],
		'Základní gramotnosti' => [
			'Čtenářská a pisatelská gramotnost' => [
				'Čtenářská nezávislost',
				'Vztah ke čtení a čtenářství',
				'Psaní a pisatelství',
				'Syntéza a tvorba'
			],
			'Logicko-matematická gramotnost' => [
				'Matematická reflexe',
				'Řešení matematických situací',
				'Aplikace matematiky v různých kontextech'
			]
		],
		'Průřezová témata' => [
			'Péče o wellbeing' => [
				'Fyzická pohoda',
				'Duševní pohoda',
				'Sociální zdraví',
				'Emocionální zdraví'
			],
			'Společnost pro všechny' => [
				'Inkluze',
				'Rovné příležitosti',
				'Aktivní občanství',
				'Demokracie'
			],
			'Udržitelné prostředí' => [
				'Ekologická výchova',
				'Ochrana životního prostředí',
				'Udržitelné technologie'
			]
		]
	];
}
