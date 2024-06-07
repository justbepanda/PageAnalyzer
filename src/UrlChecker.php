<?php

namespace Hexlet\Code;

use PDO;
use GuzzleHttp\Client;
use DiDom\Document;

class UrlChecker
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function checkUrl($url)
    {
        $client = new Client();
        $res = $client->request('GET', $url);
        $statusCode = $res->getStatusCode();
        $document = new Document($url, true);
        $title = $document->first('title');
        $h1 = $document->first('h1');
        $meta = $document->first('meta[name="description"]');

        $data = [
            'statusCode' => $statusCode,
            'title' => $title ? $title->text() : null,
            'h1' => $h1 ? $h1->text() : null,
            'description' => $meta ? $meta->getAttribute('content') : null,
        ];

        return $data;
    }

    public function insert($url_id, $statusCode, $h1, $title, $description, $createdAt)
    {
        $sql = "INSERT INTO url_checks(url_id, status_code, h1, title, description, created_at) VALUES(:url_id, :status_code, :h1, :title, :description, :created_at)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':url_id', $url_id);
        $stmt->bindParam(':status_code', $statusCode);
        $stmt->bindParam(':h1', $h1);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':created_at', $createdAt);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function allByUrlId($urlId)
    {
        $sql = "SELECT * FROM url_checks WHERE url_id = :url_id ORDER BY created_at";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':url_id', $urlId);
        $stmt->execute();
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
