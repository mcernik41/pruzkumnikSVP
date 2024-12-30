<?php

declare(strict_types=1);

namespace App\UI\TabulkaPrehled;
use Nette\Application\UI\Form;
use Nette\Database\Explorer;
use App\Services\RecursiveGetters;

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

        $recursiveGetters = new RecursiveGetters($this->explorer);

        //načtení vzdělávacích oborů
        $this->template->obory = $recursiveGetters->getRecursiveObory($svpID, null);

        //načtení vzdělávacích obsahů
        $this->template->obsahy = $recursiveGetters->getRecursiveObsahy($svpID, null);

        $oboryIds = $this->getFieldIds($this->template->obory);
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
    
    private function getFieldIds(array $obory): array
    {
        $oboryIds = [];

        foreach ($obory as $obor) 
        {
            $oboryIds[] = $obor->vzdelavaciOborID;

            if (!empty($obor->children)) 
            {
                $oboryIds = array_merge($oboryIds, $this->getFieldIds($obor->children));
            }
        }

        return $oboryIds;
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