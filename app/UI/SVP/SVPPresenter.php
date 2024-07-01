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

	public function renderDefault(int $planID): void
	{
		$plan = $this->explorer->table('svp')->get($planID);
		$this->template->jmenoSVP = $plan->jmenoSVP;
	}
}
