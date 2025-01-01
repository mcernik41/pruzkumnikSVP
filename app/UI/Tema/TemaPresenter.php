<?php

declare(strict_types=1);

namespace App\UI\Tema;
use Nette\Application\UI\Form;

use Nette;


final class TemaPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private Nette\Database\Explorer $database) 
	{
		$this->explorer = $database;
	}

	protected $explorer;

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
		$temaID = $this->getParameter('temaID');

		$form = new Form; // means Nette\Application\UI\Form
		$tema = $this->explorer->table('tema')->get($temaID);

		$form->addText('jmenoTematu', 'Jméno tématu:')
			->setDefaultValue($tema->jmenoTematu)
			->setRequired();

		$form->addSelect('rocnik', 'Ročník:', $this->explorer->table('rocnik')->fetchPairs('rocnikID', 'jmenoRocniku'))
			->setDefaultValue($tema->rocnik_rocnikID)
			->setPrompt('Vyberte ročník');

		$form->addSelect('mesicZacatek', 'Měsíc začátku:', $this->explorer->table('mesic')->fetchPairs('mesicID', 'jmenoMesice'))
			->setDefaultValue($tema->mesicID_zacatek)
			->setPrompt('Vyberte měsíc začátku');

		$form->addSelect('mesicKonec', 'Měsíc konce:', $this->explorer->table('mesic')->fetchPairs('mesicID', 'jmenoMesice'))
			->setDefaultValue($tema->mesicID_konec)
			->setPrompt('Vyberte měsíc konce');

		$form->addInteger('pocetHodin', 'Počet hodin:')
			->setDefaultValue($tema->pocetHodin)
			->setRequired();

		$form->addTextarea('popisTematu', 'Popis tématu:')
			->setDefaultValue($tema->popisTematu);

		$form->addSubmit('send', 'Upravit téma');

		$form->onSuccess[] = $this->topicModifyFormSucceeded(...);

		return $form;
	}

	private function topicModifyFormSucceeded(\stdClass $data): void
	{
		$temaID = $this->getParameter('temaID');

		$this->database->table('tema')
			->where('temaID', $temaID)
			->update([
				'jmenoTematu' => $data->jmenoTematu,
				'popisTematu' => $data->popisTematu,
				'rocnik_rocnikID' => $data->rocnik,
				'mesicID_zacatek' => $data->mesicZacatek,
				'mesicID_konec' => $data->mesicKonec,
				'pocetHodin' => $data->pocetHodin
			]);

		$this->flashMessage('Téma úspěšně upraveno', 'success');
		$this->redirect('this');
	}
}