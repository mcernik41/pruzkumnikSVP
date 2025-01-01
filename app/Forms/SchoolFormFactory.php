<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Nette\Database\Explorer;

class SchoolFormFactory
{
    private Explorer $explorer;

    public function __construct(Explorer $explorer)
    {
        $this->explorer = $explorer;
    }

    public function create(?array $defaultValues = null): Form
    {
        $form = new Form; // means Nette\Application\UI\Form

        $form->addText('name', 'Jméno školy:')
            ->setDefaultValue($defaultValues['name'] ?? '')
            ->setRequired();

        $form->addSubmit('send', $defaultValues ? 'Upravit školu' : 'Přidat školu');

        return $form;
    }

    public function process(\stdClass $data, Explorer $database, int $skolaID = null): void
    {
        $values = [
            'jmenoSkoly' => $data->name
        ];

        if ($skolaID) {
            $database->table('skola')
                ->where('skolaID', $skolaID)
                ->update($values);
        } else {
            $database->table('skola')->insert($values);
        }
    }
}