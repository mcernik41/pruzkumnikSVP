<?php

declare(strict_types=1);

namespace App\UI\SVP;
use Nette\Application\UI\Form;
use App\Services\sqlRunner;
use Nette\Database\Explorer;

use Nette;

final class SVPPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Explorer $database, sqlRunner $runner) 
	{
		$this->explorer = $database;
		$this->sqlRunner = $runner;
	}

	protected $explorer;
	protected $svpID;
	protected $sqlRunner;

	public function renderDefault(int $svpID): void
	{
		$plan = $this->explorer->table('svp')->get($svpID);
		$this->template->jmenoSVP = $plan->jmenoSVP;
		$this->template->svpID = $plan->svpID;

		$this->svpID = $svpID;
	}

}
