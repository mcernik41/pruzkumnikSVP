<?php

namespace App\Services;

class RecursiveGetters
{
    public function __construct(private \Nette\Database\Explorer $database) 
    {
        $this->explorer = $database;
    }

    protected $explorer;

    public function getRecursiveObory(int $svpID, ?int $rodicovskyVzdelavaciOborID): array
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

    public function getRecursiveObsahy(int $svpID, ?int $rodicovskyVzdelavaciObsahID): array
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