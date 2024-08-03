<?php

namespace Hexlet\Code;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use PDO;
use GuzzleHttp\Client;
use DiDom\Document;
use DiDom\Exceptions\InvalidSelectorException;
use Psr\Http\Message\ResponseInterface;

class UrlChecker
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }


    public function getUrlResponse(string $url, ?Client $client = null): array
    {
        $response = [
            'statusCode' => null,
            'flash' => [
                'type' => '',
                'text' => ''
            ]
        ];

        $client = $client ?? new Client();

        try {
            $res = $client->request('GET', $url);
            if ($res instanceof ResponseInterface) {
                $response['statusCode'] = $res->getStatusCode();
                $response['flash']['type'] = 'success';
                $response['flash']['text'] = 'Страница успешно проверена';
            }
        } catch (RequestException $e) {
            if ($e->hasResponse() && $e->getResponse() instanceof ResponseInterface) {
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

    /**
     * Получить данные документа по URL
     *
     * @param string $url
     * @param Document|null $document
     * @return array
     * @throws InvalidSelectorException
     */
    public function getDocumentData(string $url, ?Document $document = null): array
    {
        $document = $document ?? new Document($url, true);
        $title = Optional($document->first('title'))->text();
        $h1 = Optional($document->first('h1'))->text();
        $description = Optional($document->first('meta[name=description]'))->getAttribute('content');

        $data = [
            'title' => $title,
            'h1' => $h1,
            'description' =>  $description,
        ];

        return $data;
    }

    /**
     * Вставка данных в таблицу url_checks
     *
     * @param int $urlId
     * @param int|null $statusCode
     * @param string|null $h1
     * @param string|null $title
     * @param string|null $description
     * @param string $createdAt
     * @return int
     */
    public function insert(int $urlId, ?int $statusCode, ?string $h1, ?string $title, ?string $description, string $createdAt): int
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

    /**
     * Получение всех записей по URL ID
     *
     * @param int $urlId
     * @return array
     */
    public function allByUrlId(int $urlId): array
    {
        $sql = "SELECT * FROM url_checks WHERE url_id = :url_id ORDER BY created_at";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':url_id', $urlId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Получение последней записи по URL ID
     *
     * @param int $urlId
     * @return array
     */
    public function lastByUrlId(int $urlId): array
    {
        $sql = "SELECT * FROM url_checks WHERE url_id = :url_id ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':url_id', $urlId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
