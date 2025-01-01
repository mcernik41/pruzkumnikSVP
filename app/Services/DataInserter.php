<?php

namespace App\Services;

class DataInserter
{
    public function __construct(private \Nette\Database\Explorer $database) 
    {
        $this->explorer = $database;
    }

    protected $explorer;

    public function insertGrades()
    {
        $grades = [
			['jmenoRocniku' => 'prima', 'popisRocniku' => '1. ročník osmiletého gymnázia - 6. ročník základní školy'],
			['jmenoRocniku' => 'sekunda', 'popisRocniku' => '2. ročník osmiletého gymnázia - 7. ročník základní školy'],
			['jmenoRocniku' => 'tercie', 'popisRocniku' => '3. ročník osmiletého gymnázia - 8. ročník základní školy'],
			['jmenoRocniku' => 'kvarta', 'popisRocniku' => '4. ročník osmiletého gymnázia - 9. ročník základní školy'],
			['jmenoRocniku' => 'kvinta', 'popisRocniku' => '5. ročník osmiletého gymnázia - 1. ročník střední školy'],
			['jmenoRocniku' => 'sexta', 'popisRocniku' => '6. ročník osmiletého gymnázia - 2. ročník střední školy'],
			['jmenoRocniku' => 'septima', 'popisRocniku' => '7. ročník osmiletého gymnázia - 3. ročník střední školy'],
			['jmenoRocniku' => 'oktáva', 'popisRocniku' => '8. ročník osmiletého gymnázia - 4. ročník střední školy']
		];
	
		$this->explorer->table('rocnik')->insert($grades);
    }

    public function insertMonths()
    {
        $months = [
            ['jmenoMesice' => 'září'],
            ['jmenoMesice' => 'říjen'],
            ['jmenoMesice' => 'listopad'],
            ['jmenoMesice' => 'prosinec'],
            ['jmenoMesice' => 'leden'],
            ['jmenoMesice' => 'únor'],
            ['jmenoMesice' => 'březen'],
            ['jmenoMesice' => 'duben'],
            ['jmenoMesice' => 'květen'],
            ['jmenoMesice' => 'červen']
        ];
    
        $this->explorer->table('mesic')->insert($months);
    }

    public function insertTestData()
    {
        $this->insertGrades();

        $skolaID = $this->explorer->table('skola')->insert(['jmenoSkoly' => 'Gymnázium Nad Kavalírkou'])->getPrimary();

		$svpVG_ID = $this->explorer->table('svp')->insert(['skola_skolaID' => $skolaID, 'jmenoSVP' => "Vzdělávací plán pro vyšší gymnázium"])->getPrimary();

        $this->insertFields($svpVG_ID);
        $this->insertContents($svpVG_ID);

        $chatGPTID = $this->database->table('pomucka')->insert(['jmenoPomucky' => 'ChatGPT'])->getPrimary();
        $wordID = $this->database->table('pomucka')->insert(['jmenoPomucky' => 'Microsoft Office Word'])->getPrimary();
        $visualStudioID = $this->database->table('pomucka')->insert(['jmenoPomucky' => 'Visual Studio 2022'])->getPrimary();

        $samostatnaPraceID = $this->database->table('typAktivity')->insert(['jmenoTypu' => 'samostatná práce', 'popisTypu' => 'Práce, kterou student vykonává sám'])->getPrimary();
        $tymovyProjektID = $this->database->table('typAktivity')->insert(['jmenoTypu' => 'týmový projekt', 'popisTypu' => 'Projekt, který studenti vykonávají ve skupině'])->getPrimary();

		$esejID = $this->database->table('vzdelavaciAktivita')->insert(['svp_svpID' => $svpVG_ID, 'jmenoAktivity' => 'Esej o informatice', 'typAktivity_typAktivityID' => $samostatnaPraceID])->getPrimary();
		$trojuhelnikyID = $this->database->table('vzdelavaciAktivita')->insert(['svp_svpID' => $svpVG_ID, 'jmenoAktivity' => 'Transformace příkladu trojúhelníky', 'typAktivity_typAktivityID' => $tymovyProjektID])->getPrimary();

        $digiTechID = $this->explorer->table('vzdelavaciObor')->where('jmenoOboru', 'Digitální technologie')->fetch();
        $infID = $this->explorer->table('vzdelavaciObor')->where('jmenoOboru', 'Informatika')->fetch();
        
        $praceSInfoID = $this->explorer->table('vzdelavaciObsah')->where('jmenoObsahu', 'Kritické myšlení a práce s informacemi')->fetch();
        $digiGramID = $this->explorer->table('vzdelavaciObsah')->where('jmenoObsahu', 'Digitální gramotnost a bezpečnost')->fetch();

        $rijenbID = $this->explorer->table('mesic')->where('jmenoMesice', 'říjen')->fetch();

        $kvintaID = $this->explorer->table('rocnik')->where('jmenoRocniku', 'kvinta')->fetch();

        $this->database->table('soucastAktivity')->insert([
			'vzdelavaciAktivita_vzdelavaciAktivitaID' => $esejID,
			'jmenoSoucasti' => 'Využití umělé inteligence při psaní',
			'vzdelavaciObor_vzdelavaciOborID' => $digiTechID,
			'vzdelavaciObsah_vzdelavaciObsahID' => $praceSInfoID,
			'rocnik_rocnikID' => $kvintaID,
			'pomucka_pomuckaID' => $chatGPTID
		]);

        $this->database->table('soucastAktivity')->insert([
			'vzdelavaciAktivita_vzdelavaciAktivitaID' => $esejID,
			'jmenoSoucasti' => 'Psaní textu v aplikaci MS Word',
			'vzdelavaciObor_vzdelavaciOborID' => $infID,
			'vzdelavaciObsah_vzdelavaciObsahID' => $digiGramID,
			'rocnik_rocnikID' => $kvintaID,
			'pomucka_pomuckaID' => $wordID
		]);

		$this->database->table('tema')->insert([
			'vzdelavaciObor_vzdelavaciOborID' => $infID,
			'jmenoTematu' => 'Základní konstrukce jazyka C# (proměnné, podmínky, cykly, vstup a výstup)',
			'rocnik_rocnikID' => $kvintaID,
			'mesicID_zacatek' => $rijenbID,
			'mesicID_konec' => $rijenbID,
			'pocetHodin' => 6
		]);
    }

    public function insertFields(int $svpID)
    {
        foreach ($this->vzdelavaciStruktura as $oblast => $obory) {
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

    public function insertContents(int $svpID)
    {
        foreach ($this->obsahy as $oblast => $obory) {
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

	private array $obsahy = [
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