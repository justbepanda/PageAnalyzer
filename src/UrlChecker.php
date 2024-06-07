<?php

namespace Hexlet\Code;

use PDO;

class UrlChecker
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert($url_id, $createdAt)
    {
        $sql = "INSERT INTO url_checks(url_id, created_at) VALUES(:url_id, :created_at)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':url_id', $url_id);
        $stmt->bindParam(':created_at', $createdAt);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function all()
    {
        $sql = "SELECT * FROM url_checks";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function lastByUrlId($urlId)
    {
        $sql = "SELECT * FROM url_checks WHERE url_id = :url_id ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':url_id', $urlId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
