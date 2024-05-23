<?php

namespace Hexlet\Code;

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
        
        $databaseUrl = $_ENV['DATABASE_URL'];
        $urlParts = parse_url($databaseUrl);
        $username = $urlParts['user'];
        $password = $urlParts['pass'];
        $host = $urlParts['host'];
        $port = isset($urlParts['port']) ? $urlParts['port'] : null;  // Проверка наличия ключа 'port'
        $dbName = ltrim($urlParts['path'], '/');


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
