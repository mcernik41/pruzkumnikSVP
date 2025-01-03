<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;
use App\Services\sqlRunner;
use Nette\Database\Connection;
use \PDO;

class Bootstrap
{
	public static function boot(): Configurator
	{
		$configurator = new Configurator;
		$appDir = dirname(__DIR__);

		//$configurator->setDebugMode('secret@23.75.345.200'); // enable for your remote IP
		$configurator->enableTracy($appDir . '/log');

		$configurator->setTempDirectory($appDir . '/temp');

		$configurator->createRobotLoader()
			->addDirectory(__DIR__)
			->register();

		$configurator->addConfig($appDir . '/config/common.neon');
		$configurator->addConfig($appDir . '/config/services.neon');
		
		$container = $configurator->createContainer();

		self::createDatabaseIfNotExists($container);

		return $configurator;
	}

	private static function createDatabaseIfNotExists($container): void
    {
        $dbHost = DatabaseConfig::$dbHost;
        $dbUser = DatabaseConfig::$dbUser;
        $dbPassword = DatabaseConfig::$dbPassword;
        $dbName = DatabaseConfig::$dbName;

        $dsn = "mysql:host=$dbHost";

        try 
		{
			$pdo = new \PDO($dsn, $dbUser, $dbPassword);
			$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

			$stmt = $pdo->query("SHOW DATABASES LIKE '$dbName'");
			$dbExists = $stmt->fetch();

			if (!$dbExists) 
			{
				$pdo->exec("CREATE DATABASE `$dbName`");

				// vytvoření tabulek
				$runner = $container->getByType(Services\sqlRunner::class);
				$runner->runSQLFile(__DIR__ . '/../createScript.sql');

				// pokud se vytváří databáze, vloží se měsíce
				$pdo->exec("USE `$dbName`");
				$stmt = $pdo->query("SELECT COUNT(*) FROM mesic");
				$count = $stmt->fetchColumn();

				if ($count == 0) 
				{
					$database = $container->getByType(\Nette\Database\Explorer::class);
					$dataInsetrer = new \App\Services\DataInserter($database);
					$dataInsetrer->insertMonths();
				}
			}
        } 
		catch (\PDOException $e) 
		{
            throw new \Exception("Database creation failed: " . $e->getMessage());
        }
    }
}
