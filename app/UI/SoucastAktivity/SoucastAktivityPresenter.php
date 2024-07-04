<?php

declare(strict_types=1);

namespace App\UI\SoucastAktivity;
use Nette\Application\UI\Form;

use Nette;


final class SoucastAktivityPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Nette\Database\Explorer $database) 
	{
		$this->explorer = $database;
	}

	protected $explorer;

	public function renderDefault(int $soucastID, int $aktivitaID, int $svpID): void
	{
		//informace o součásti
		$soucast = $this->explorer->table('soucastAktivity')->get($soucastID);
		$this->template->jmenoSoucasti = $soucast->jmenoSoucasti;
		
		//informace o aktivitě
		$aktivita = $this->explorer->table('vzdelavaciAktivita')->get($aktivitaID);
		$this->template->jmenoAktivity = $aktivita->jmenoAktivity;
		$this->template->aktivitaID = $aktivitaID;

		$this->template->svpID = $svpID;

		//informace o oboru
		$this->template->vzdelavaciObor = $this->explorer->table('vzdelavaciObor')->get($soucast->vzdelavaciObor_vzdelavaciOborID);

		//informace o obsahu
		$this->template->vzdelavaciObsah = $this->explorer->table('vzdelavaciObsah')->get($soucast->vzdelavaciObsah_vzdelavaciObsahID);
	}
}
