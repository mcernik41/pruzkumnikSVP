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