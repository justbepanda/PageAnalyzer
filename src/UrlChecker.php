<?php

namespace Hexlet\Code;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
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


    public function getUrlResponse(string $url): array
    {
        $response = [
            'statusCode' => null,
            'flash' => [
                'type' => '',
                'text' => ''
            ]
        ];
        $client = new Client();
        try {
            $res = $client->request('GET', $url);
            $response['statusCode'] = $res->getStatusCode();
            $response['flash']['type'] = 'success';
            $response['flash']['text'] = 'Страница успешно проверена';
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response['statusCode'] = $e->getResponse()->getStatusCode();
            }
            $response['flash']['type'] = 'warning';
            $response['flash']['text'] = 'Проверка была выполнена успешно, но сервер ответил с ошибкой';
        } catch (ConnectException $e) {
            $response['statusCode'] = null;
            $response['flash']['type'] = 'danger';
            $response['flash']['text'] = 'Произошла ошибка при проверке, не удалось подключиться';
        } catch (\Exception $e) {
            // Обработка прочих исключений
            $response['statusCode'] = null;
            $response['flash']['type'] = 'danger';
            $response['flash']['text'] = 'Произошла неизвестная ошибка';
        }

        return $response;
    }

    public function getDocumentData(string $url): array
    {
        $document = new Document($url, true);
        $title = $document->first('title');
        $h1 = $document->first('h1');
        $meta = $document->first('meta[name="description"]');

        $data = [
            'title' => $title ? $title->text() : null,
            'h1' => $h1 ? $h1->text() : null,
            'description' => $meta ? $meta->getAttribute('content') : null,
        ];

        return $data;
    }

    public function insert($urlId, $statusCode, $h1, $title, $description, $createdAt)
    {
        $sql = "INSERT INTO url_checks(url_id, status_code, h1, title, description, created_at) VALUES(:url_id, :status_code, :h1, :title, :description, :created_at)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':url_id', $urlId);
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
