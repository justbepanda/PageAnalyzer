<?php

namespace Hexlet\Code;

use PDO;

class UrlRepository
{
    /**
     * Объект PDO
     * @var PDO
     */
    private PDO $pdo;

    /**
     * инициализация объекта с объектом PDO
     * @param mixed $pdo
     */
    public function __construct(mixed $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(string $name, string $created): false|string
    {
        $sql = "INSERT INTO urls (name, created_at) VALUES(:name, :created)";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':created', $created);

        $stmt->execute();

        return $this->pdo->lastInsertId();
    }

    /**
     * @return array<string, mixed>|false
     */
    public function findById(int $id): false|array
    {
        $sql = "SELECT * FROM urls WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('id', $id);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @return array<array<string, mixed>>|false
     */
    public function all(): false|array
    {
        $stmt = $this->pdo->query("SELECT * FROM urls");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @return array<array<string, mixed>>|false
     */
    public function findByName(string $name): false|array
    {
        $sql = "SELECT * FROM urls WHERE name = :name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('name', $name);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function normalize(string $url): string|false
    {
        $parsedUrl = parse_url($url);
        if ($parsedUrl === false || !isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
            return false;
        }
        $normalizedUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        return $normalizedUrl;
    }
}
