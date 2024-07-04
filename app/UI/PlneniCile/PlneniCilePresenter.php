<?php

declare(strict_types=1);

namespace App\UI\PlneniCile;
use Nette\Application\UI\Form;

use Nette;


final class PlneniCilePresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Nette\Database\Explorer $database) 
	{
		$this->explorer = $database;
	}

	protected $explorer;

	public function renderDefault(int $plneniCileID, int $cilID, int $svpID): void
	{
		//informace o součásti
		$plneni = $this->explorer->table('plneniCile')->get($plneniCileID);
		$this->template->popisPlneni = $plneni->popisPlneniCile;
		
		//informace o aktivitě
		$aktivita = $this->explorer->table('cil')->get($cilID);
		$this->template->jmenoCile = $aktivita->jmenoCile;
		$this->template->cilID = $cilID;

		$this->template->svpID = $svpID;

		//informace o obsahu
		$this->template->vzdelavaciObsah = $this->explorer->table('vzdelavaciObsah')->get($plneni->vzdelavaciObsah_vzdelavaciObsahID);
	}
}
