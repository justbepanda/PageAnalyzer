<?php

namespace Hexlet\Code;

class UrlRepository
{
    /**
     * Объект PDO
     * @var \PDO
     */
    private $pdo;

    /**
     * инициализация объекта с объектом \PDO
     * @тип параметра $pdo
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert($name, $created): false|string
    {
        $sql = "INSERT INTO urls (name, created_at) VALUES(:name, :created)";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':created', $created);

        $stmt->execute();

        return $this->pdo->lastInsertId();
    }


    public function findById($id): false|array
    {
        $sql = "SELECT * FROM urls WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('id', $id);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function all(): false|array
    {
        $stmt = $this->pdo->query("SELECT * FROM urls");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findByName($name): false|array
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
