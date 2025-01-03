<?php

declare(strict_types=1);

namespace App;

class DatabaseConfig
{
	public static string $dbHost;
	public static string $dbUser;
	public static string $dbPassword;
	public static string $dbName;

	public static function setConfig(): void
	{
		if($_SERVER['HTTP_HOST'] == 'localhost')
        {
            self::$dbHost = 'localhost';
            self::$dbUser = 'root';
            self::$dbPassword = '';
            self::$dbName = 'pruzkumniksvp';
        }
        else
        {
            $adress = "innodb.endora.cz";
            $name = "pruzkumniksvp";
            $password = "8B-KbVLqky:FeZ:";
            $db = "pruzkumniksvp";		
        }
	}
}