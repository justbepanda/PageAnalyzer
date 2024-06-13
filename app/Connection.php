<?php

namespace Hexlet\Code;

use Dotenv\Dotenv;
use PDO;
use Exception;

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
     * @return PDO
     * @throws Exception
     */
    public function connect(): PDO
    {
        $dotenvPath = __DIR__ . '/../.env';
        if (file_exists($dotenvPath)) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();
        }

        $databaseUrl = getenv('DATABASE_URL') ? getenv('DATABASE_URL') : $_ENV['DATABASE_URL'];
        dump($databaseUrl);
        $urlParts = parse_url($databaseUrl);
        $username = $urlParts['user'];
        $password = $urlParts['pass'];
        $host = $urlParts['host'];
        $port = $urlParts['port'] ?? null;
        $dbName = ltrim($urlParts['path'], '/');

        $conStr = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $host,
            $port,
            $dbName,
            $username,
            $password
        );

        $pdo = new PDO($conStr);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    /**
     * возврат экземпляра объекта Connection
     * тип @return
     */
    /**
     * Возврат экземпляра объекта Connection
     * @return Connection|null
     */
    public static function get(): ?Connection
    {
        if (null === self::$conn) {
            self::$conn = new self();
        }

        return self::$conn;
    }

    /**
     * Возвращает текущее подключение к базе данных
     * @return Connection|null
     */
    public static function getConnection(): ?Connection
    {
        return self::$conn;
    }

    protected function __construct()
    {
    }
}
