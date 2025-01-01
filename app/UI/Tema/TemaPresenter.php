<?php

declare(strict_types=1);

namespace App\UI\Tema;

use App\Forms\TopicFormFactory;
use Nette\Application\UI\Form;
use Nette;


final class TemaPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Nette\Database\Explorer $database, TopicFormFactory $topicFormFactory) 
	{
        parent::__construct();
		$this->explorer = $database;
        $this->topicFormFactory = $topicFormFactory;
	}

	protected $explorer;
    private TopicFormFactory $topicFormFactory;

	public function renderDefault(int $vzdelavaciOborID, int $temaID): void
	{
		$vzdelavaciObor = $this->explorer->table('vzdelavaciObor')->get($vzdelavaciOborID);
		$this->template->jmenoOboru = $vzdelavaciObor->jmenoOboru;

		$this->template->svpID = $vzdelavaciObor->svp_svpID;
		
		$tema = $this->explorer->table('tema')->get($temaID);
		$this->template->jmenoTematu = $tema->jmenoTematu;

		$this->template->temaID = $temaID;
		$this->template->vzdelavaciOborID = $vzdelavaciOborID;

		//nalezení součástí vzdělávacích aktivit - podle obsahu a aktivity
		$this->template->obsahy = \App\Services\ActivityLoader::nacistAktivity($this->explorer, $temaID, 'tema');
	}

	protected function createComponentTopicModifyForm(): Form
	{
		$temaID = (int)$this->getParameter('temaID');
        $tema = $this->explorer->table('tema')->get($temaID);

        $defaultValues = [
            'jmenoTematu' => $tema->jmenoTematu,
            'rocnik' => $tema->rocnik_rocnikID,
            'mesicZacatek' => $tema->mesicID_zacatek,
            'mesicKonec' => $tema->mesicID_konec,
            'pocetHodin' => $tema->pocetHodin,
            'popisTematu' => $tema->popisTematu
        ];

        $form = $this->topicFormFactory->create($defaultValues);
        $form->onSuccess[] = function (\stdClass $data) use ($temaID) {
            $this->topicFormFactory->process($data, $this->explorer, $temaID);
            $this->flashMessage('Téma úspěšně upraveno', 'success');
            $this->redirect('this');
        };

        return $form;
	}
}