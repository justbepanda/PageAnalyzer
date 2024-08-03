<?php

namespace Hexlet\Code;

use Dotenv\Dotenv;
use PDO;
use Exception;
use PDOException;

/**
 * Создание класса Connection
 */
class Connection
{
    private mixed $conn;

    public function connect(): ?PDO
    {
        $this->conn = null;
        $dotenvPath = __DIR__ . '/../.env';
        if (file_exists($dotenvPath)) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();
        }

        $databaseUrl = getenv('DATABASE_URL') ? getenv('DATABASE_URL') : $_ENV['DATABASE_URL'];
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

        try {
            $this->conn = new PDO($conStr);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }
        return $this->conn;
    }
}
