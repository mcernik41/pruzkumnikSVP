<?php

namespace App\Services;

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

class Obor
{
	public int $oborID;
	public string $jmenoOboru;
	public array $aktivity;
	
	public function __construct(int $oborID, string $jmenoOboru)
	{
		$this->oborID = $oborID;
		$this->jmenoOboru = $jmenoOboru;
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

class ActivityLoader
{
    public static function nacistAktivity($explorer, $entityID, $entityType)
    {
        $column = '';
		$link = '';
		$supEntityName = '';
        switch ($entityType) 
		{
			case 'tema':
				$column = 'tema_temaID';
				$link = 'vzdelavaciObsah_vzdelavaciObsahID';
				$supEntityName = 'vzdelavaciObsah';
				break;
            case 'vzdelavaciObor':
                $column = 'vzdelavaciObor_vzdelavaciOborID';
				$link = 'vzdelavaciObsah_vzdelavaciObsahID';
				$supEntityName = 'vzdelavaciObsah';
                break;
            case 'vzdelavaciObsah':
                $column = 'vzdelavaciObsah_vzdelavaciObsahID';
				$link = 'vzdelavaciObor_vzdelavaciOborID';
				$supEntityName = 'vzdelavaciObor';
                break;
        }

		//najdu všechny součásti aktivit, které se váží k dané entitě
        $soucastiAktivit = $explorer->table('soucastAktivity')
            ->where("$column = ?", $entityID)
            ->fetchAll();

		//najdu ID obsahů/oborů, do kterých patří nalezené aktivity
        $relatedIDs = [];
        foreach ($soucastiAktivit as $soucastAktivity) 
		{
            $relatedIDs[] = $soucastAktivity->$link;
        }
        $relatedIDs = array_unique($relatedIDs);

		//najdu příslušné obsahy/obory
        $relatedEntities = $explorer->table($supEntityName)
            ->where($supEntityName . 'ID', $relatedIDs)
            ->fetchAll();

        $entitiesWithActivities = [];

		//prohledání všech obsahů/oborů
        foreach ($relatedEntities as $entity) 
		{
            $soucastiAktivit = $explorer->table('soucastAktivity')
                ->where($entityType . '_' . $entityType . 'ID = ?', $entityID)
                ->where($link . ' = ?', $entity)
                ->fetchAll();

            $activityIDs = [];
            foreach ($soucastiAktivit as $soucastAktivity) 
			{
                $activityIDs[] = $soucastAktivity->vzdelavaciAktivita_vzdelavaciAktivitaID;
            }
            $activityIDs = array_unique($activityIDs);

            $activities = $explorer->table('vzdelavaciAktivita')
                ->where('vzdelavaciAktivitaID', $activityIDs)
                ->fetchAll();

			switch ($entityType) 
			{
				case 'vzdelavaciObsah':
					$entityWithActivities = new \App\Services\Obor($entity->vzdelavaciOborID, $entity->jmenoOboru);
					break;
				case 'vzdelavaciObor':
				case 'tema':
					$entityWithActivities = new \App\Services\Obsah($entity->vzdelavaciObsahID, $entity->jmenoObsahu);
					break;
			}

            foreach ($activities as $activity) 
			{
                $activityWithDetails = new \App\Services\Aktivita($activity->vzdelavaciAktivitaID, $activity->jmenoAktivity);

                $soucastiAktivit = $explorer->table('soucastAktivity')
                    ->where("$column = ?", $entityID)
                    ->where($link . ' = ?', $entity)
                    ->where('vzdelavaciAktivita_vzdelavaciAktivitaID = ?', $activity)
                    ->fetchAll();

                foreach ($soucastiAktivit as $soucastAktivity) 
				{
                    $activityWithDetails->soucastiAktivity[] = $soucastAktivity;
                }

                $entityWithActivities->aktivity[] = $activityWithDetails;
            }

            $entitiesWithActivities[] = $entityWithActivities;
        }

        return $entitiesWithActivities;
    }
}