<?php

declare(strict_types=1);

namespace App\UI\TabulkaPrehled;
use Nette\Application\UI\Form;
use Nette\Database\Explorer;

use Nette;

final class TabulkaPrehledPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Explorer $database) 
	{
		$this->explorer = $database;
	}

	protected $explorer;
	protected $svpID;

	public function renderDefault(int $svpID): void
	{
		$plan = $this->explorer->table('svp')->get($svpID);
		$this->template->jmenoSVP = $plan->jmenoSVP;
		$this->template->svpID = $plan->svpID;

		$this->svpID = $svpID;

        //načtení vzdělávacích oborů
        $this->template->obory = $this->getRecursiveObory($svpID, null);

        //načtení vzdělávacích obsahů
        $this->template->obsahy = $this->getRecursiveObsahy($svpID, null);

        $oboryIds = $this->getInfixIds($this->template->obory);
        $this->template->oboryIds = $oboryIds;

        //načtení součástí vzdělávacích aktivit
        $soucastiAktivit = $this->explorer->table('soucastAktivity')
            ->where('vzdelavaciObor_vzdelavaciOborID IN ?', $oboryIds)
            ->fetchAll();

        $soucasti = [];

        foreach ($soucastiAktivit as $soucastAktivity) 
        {
            $oborID = $soucastAktivity->vzdelavaciObor_vzdelavaciOborID;
            $obsahID = $soucastAktivity->vzdelavaciObsah_vzdelavaciObsahID;

            $aktivita = $this->explorer->table('vzdelavaciAktivita')->get($soucastAktivity->vzdelavaciAktivita_vzdelavaciAktivitaID);

            if(null !== $this->getParameter('typAktivity') && $aktivita->typAktivity_typAktivityID != $this->getParameter('typAktivity'))
            {
                continue;
            }

            $soucast = [
                'soucastID' => $soucastAktivity->soucastAktivityID,
                'jmenoSoucasti' => $soucastAktivity->jmenoSoucasti,
                'popisSoucasti' => $soucastAktivity->popisSoucasti,
                'aktivitaID' => $soucastAktivity->vzdelavaciAktivita_vzdelavaciAktivitaID,
                'jmenoAktivity' => $aktivita->jmenoAktivity
            ];
            
            $soucasti[$oborID][$obsahID][] = $soucast;
        }
        
        $this->template->soucastiAktivit = $soucasti;
	}
    
    private function getInfixIds(array $obory): array
    {
        $oboryIds = [];

        foreach ($obory as $obor) 
        {
            $oboryIds[] = $obor->vzdelavaciOborID;

            if (!empty($obor->children)) 
            {
                $oboryIds = array_merge($oboryIds, $this->getInfixIds($obor->children));
            }
        }

        return $oboryIds;
    }

    private function getRecursiveObory(int $svpID, ?int $rodicovskyVzdelavaciOborID): array
    {
        $query = $this->explorer->table('vzdelavaciObor');
        
        if ($rodicovskyVzdelavaciOborID != null) 
        {
            $query->where('rodicovskyVzdelavaciOborID', $rodicovskyVzdelavaciOborID);
        }
        else
        {
            $query->where('svp_svpID = ? AND rodicovskyVzdelavaciOborID IS NULL', $svpID);
        }
        
        $obory = $query->fetchAll();
        $oboryArray = [];

        foreach ($obory as $obor) 
        {
            $oborData = new VzdelavaciObor(
                $obor->vzdelavaciOborID,
                $obor->jmenoOboru,
                $obor->popisOboru,
                $obor->rodicovskyVzdelavaciOborID
            );
            $oborData->children = $this->getRecursiveObory($svpID, $obor->vzdelavaciOborID);
            $oboryArray[] = $oborData;
        }

        return $oboryArray;
    }

    private function getRecursiveObsahy(int $svpID, ?int $rodicovskyVzdelavaciObsahID): array
    {
        $query = $this->explorer->table('vzdelavaciObsah');
        
        if ($rodicovskyVzdelavaciObsahID != null) 
        {
            $query->where('rodicovskyVzdelavaciObsahID', $rodicovskyVzdelavaciObsahID);
        }
        else
        {
            $query->where('svp_svpID = ? AND rodicovskyVzdelavaciObsahID IS NULL', $svpID);
        }
        
        $obsahy = $query->fetchAll();
        $obsahyArray = [];

        foreach ($obsahy as $obsah) 
        {
            $obsahData = new VzdelavaciObsah(
                $obsah->vzdelavaciObsahID,
                $obsah->jmenoObsahu,
                $obsah->popisObsahu,
                $obsah->rodicovskyVzdelavaciObsahID
            );
            $obsahData->children = $this->getRecursiveObsahy($svpID, $obsah->vzdelavaciObsahID);
            $obsahyArray[] = $obsahData;
        }

        return $obsahyArray;
    }

	protected function createComponentFilterForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

        $form->addSelect('typAktivity', 'Typ aktivity:', $this->explorer->table('typAktivity')->fetchPairs('typAktivityID', 'jmenoTypu'))
            ->setPrompt('Vyberte typ aktivity')
            ->setDefaultValue($this->getParameter('typAktivity'));

		$form->addSubmit('send', 'Filtrovat');

        $form->onSuccess[] = function (Form $form, \stdClass $values): void
        {
            $this->redirect('this', ['typAktivity' => $values->typAktivity]);
        };

		return $form;
	}
}
    
class VzdelavaciObor
{
    public $vzdelavaciOborID;
    public $jmenoOboru;
    public $popisOboru;
    public $rodicovskyVzdelavaciOborID;

    public function __construct(
        int $vzdelavaciOborID,
        string $jmenoOboru,
        ?string $popisOboru,
        ?int $rodicovskyVzdelavaciOborID
    ) {
        $this->vzdelavaciOborID = $vzdelavaciOborID;
        $this->jmenoOboru = $jmenoOboru;
        $this->popisOboru = $popisOboru;
        $this->rodicovskyVzdelavaciOborID = $rodicovskyVzdelavaciOborID;
    }
}
    
class VzdelavaciObsah
{
    public $vzdelavaciObsahID;
    public $jmenoObsahu;
    public $popisObsahu;
    public $rodicovskyVzdelavaciObsahID;

    public function __construct(
        int $vzdelavaciObsahID,
        string $jmenoObsahu,
        ?string $popisObsahu,
        ?int $rodicovskyVzdelavaciObsahID
    ) {
        $this->vzdelavaciObsahID = $vzdelavaciObsahID;
        $this->jmenoObsahu = $jmenoObsahu;
        $this->popisObsahu = $popisObsahu;
        $this->rodicovskyVzdelavaciObsahID = $rodicovskyVzdelavaciObsahID;
    }
}