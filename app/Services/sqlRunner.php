<?php

namespace App\Services;

class sqlRunner
{
    public function __construct(private \Nette\Database\Explorer $database) 
    {
        $this->explorer = $database;
    }

    protected $explorer;

    public function runSQLFile($sqlFile)
    {
        $sqlContent = file_get_contents($sqlFile);

        // Rozdělení SQL souboru na jednotlivé příkazy
        $sqlCommands = explode(';', $sqlContent);
        
        foreach ($sqlCommands as $command) 
        {
            // Odstranění bílých znaků a kontrola, zda příkaz není prázdný
            $trimmedCommand = trim($command);
            
            if ($trimmedCommand) 
            {
                // Spuštění jednotlivých SQL příkazů
                $this->explorer->query($trimmedCommand);
            }
        }
    }
}