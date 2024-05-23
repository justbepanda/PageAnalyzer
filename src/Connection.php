<?php

namespace Hexlet\Code;

use PDO;
use PDOException;
use Dotenv\Dotenv;

/**
 * Создание класса Connection
 */
class Connection
{
    /**
     * Connection
     * тип @var
     */
    private static ?Connection $instance = null;
    private ?PDO $pdo = null;

    /**
     * Подключение к базе данных и возврат экземпляра объекта \PDO
     * @return \PDO
     * @throws \Exception
     */
    public function connect(): \PDO
    {
        if ($this->pdo === null) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();

            $databaseUrl = parse_url($_ENV['DATABASE_URL']);
            if ($databaseUrl === false) {
                throw new \Exception("Error reading database configuration file");
            }

            $username = $databaseUrl['user'];
            $password = $databaseUrl['pass'];
            $host = $databaseUrl['host'];
            $port = $databaseUrl['port'];
            $dbName = ltrim($databaseUrl['path'], '/');

            $dsn = sprintf(
                "pgsql:host=%s;port=%d;dbname=%s",
                $host,
                $port,
                $dbName
            );

            try {
                $this->pdo = new PDO($dsn, $username, $password);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                throw new \Exception('Connection failed: ' . $e->getMessage());
            }
        }

        return $this->pdo;
    }

    /**
     * Возврат экземпляра объекта Connection
     * @return \PDO|null
     */
    public static function get(): ?PDO
    {
        if (static::$instance === null) {
            static::$instance = new self();
        }

        return static::$instance->connect();
    }

    protected function __construct()
    {
    }
}
