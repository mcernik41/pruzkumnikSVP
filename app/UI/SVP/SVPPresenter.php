<?php

declare(strict_types=1);

namespace App\UI\SVP;
use Nette\Application\UI\Form;

use Nette;


final class SVPPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Nette\Database\Explorer $database) 
	{
		$this->explorer = $database;
	}

	protected $explorer;
	protected $rodicovskyObsah;

	public function renderDefault(int $svpID): void
	{
		$plan = $this->explorer->table('svp')->get($svpID);
		$this->template->jmenoSVP = $plan->jmenoSVP;
		$this->template->svpID = $plan->svpID;
	}
}
