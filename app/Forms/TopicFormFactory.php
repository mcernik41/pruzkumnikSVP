<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Nette\Database\Explorer;

class TopicFormFactory
{
    private Explorer $explorer;

    public function __construct(Explorer $explorer)
    {
        $this->explorer = $explorer;
    }

    public function create(?array $defaultValues = null): Form
    {
        $form = new Form; // means Nette\Application\UI\Form

        $form->addText('jmenoTematu', 'Jméno tématu:')
            ->setDefaultValue($defaultValues['jmenoTematu'] ?? '')
            ->setRequired();

        $form->addSelect('rocnik', 'Ročník:', $this->explorer->table('rocnik')->fetchPairs('rocnikID', 'jmenoRocniku'))
            ->setDefaultValue($defaultValues['rocnik'] ?? null)
            ->setRequired()
            ->setPrompt('Vyberte ročník');

        $form->addSelect('mesicZacatek', 'Měsíc začátku:', $this->explorer->table('mesic')->fetchPairs('mesicID', 'jmenoMesice'))
            ->setDefaultValue($defaultValues['mesicZacatek'] ?? null)
            ->setRequired()
            ->setPrompt('Vyberte měsíc začátku');

        $form->addSelect('mesicKonec', 'Měsíc konce:', $this->explorer->table('mesic')->fetchPairs('mesicID', 'jmenoMesice'))
            ->setDefaultValue($defaultValues['mesicKonec'] ?? null)
            ->setRequired()
            ->setPrompt('Vyberte měsíc konce');

        $form->addInteger('pocetHodin', 'Počet hodin:')
            ->setDefaultValue($defaultValues['pocetHodin'] ?? null)
            ->setRequired()
            ->setRequired();

        $form->addTextarea('popisTematu', 'Popis tématu:')
            ->setDefaultValue($defaultValues['popisTematu'] ?? '');

        $form->addSubmit('send', $defaultValues ? 'Upravit téma' : 'Přidat téma');

        return $form;
    }

    public function process(\stdClass $data, Explorer $database, int $temaID = null, int $vzdelavaciOborID = null): void
    {
        $values = [
            'jmenoTematu' => $data->jmenoTematu,
            'popisTematu' => $data->popisTematu,
            'rocnik_rocnikID' => $data->rocnik,
            'mesicID_zacatek' => $data->mesicZacatek,
            'mesicID_konec' => $data->mesicKonec,
            'pocetHodin' => $data->pocetHodin
        ];

        if ($temaID) {
            $database->table('tema')
                ->where('temaID', $temaID)
                ->update($values);
        } else {
            $values['vzdelavaciObor_vzdelavaciOborID'] = ($vzdelavaciOborID == -1) ? null : $vzdelavaciOborID;
            $database->table('tema')->insert($values);
        }
    }

    public function delete(Explorer $database, int $temaID): void
    {
        $database->table('tema')->where('temaID', $temaID)->delete();
    }
}