<?php

namespace Hexlet\Code;

require 'vendor/autoload.php';


/**
 * Создание класса Connection
 */
class Connection
{
    /**
     * Connection
     * тип @var
     */
    private static ?Connection $conn = null;

    /**
     * Подключение к базе данных и возврат экземпляра объекта \PDO
     * @return \PDO
     * @throws \Exception
     */
    public function connect(): \PDO
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
        $databaseUrl = parse_url($_ENV['DATABASE_URL']);
        if ($databaseUrl === false) {
            throw new \Exception("Error reading database configuration file");
        }

        $username = $databaseUrl['user']; // janedoe
        $password = $databaseUrl['pass']; // mypassword
        $host = $databaseUrl['host']; // localhost
        $port = $databaseUrl['port']; // 5432
        $dbName = ltrim($databaseUrl['path'], '/'); // mydb

        $conStr = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $host,
            $port,
            $dbName,
            $username,
            $password
        );
        
        $pdo = new \PDO($conStr);

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    /**
     * возврат экземпляра объекта Connection
     * тип @return
     */
    public static function get(): ?Connection
    {
        if (null === static::$conn) {
            static::$conn = new self();
        }

        return static::$conn;
    }

    protected function __construct()
    {

    }
}