<?php

namespace Hexlet\Code\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Hexlet\Code\UrlChecker;
use Hexlet\Code\UrlRepository;
use PHPUnit\Framework\TestCase;
use Mockery;
use PDO;
use PDOStatement;
use Psr\Http\Message\ResponseInterface;
use DiDom\Document;
use DiDom\Element;

class UrlCheckerTest extends TestCase
{
    protected $pdoMock;
    protected $pdoStmtMock;
    protected $urlChecker;
    protected $urlRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdoMock = Mockery::mock(PDO::class);
        $this->pdoStmtMock = Mockery::mock(PDOStatement::class);
        $this->urlChecker = Mockery::mock(UrlChecker::class, [$this->pdoMock])->makePartial();
        $this->urlRepo = Mockery::mock(UrlRepository::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testInsert(): void
    {
        $urlId = '2';
        $createdAt = '2020-10-10 10:10:10';
        $h1 = 'h1 test';
        $title = 'title test';
        $description = 'desc test';
        $statusCode = '200';

        $this->pdoMock->shouldReceive('prepare')
            ->once()
            ->with("INSERT INTO url_checks(url_id, status_code, h1, title, description, created_at) VALUES(:url_id, :status_code, :h1, :title, :description, :created_at)")
            ->andReturn($this->pdoStmtMock);

        $this->pdoStmtMock->shouldReceive('bindParam')
            ->with(':url_id', $urlId)
            ->once();

        $this->pdoStmtMock->shouldReceive('bindParam')
            ->with(':status_code', $statusCode)
            ->once();

        $this->pdoStmtMock->shouldReceive('bindParam')
            ->with(':h1', $h1)
            ->once();

        $this->pdoStmtMock->shouldReceive('bindParam')
            ->with(':title', $title)
            ->once();

        $this->pdoStmtMock->shouldReceive('bindParam')
            ->with(':description', $description)
            ->once();

        $this->pdoStmtMock->shouldReceive('bindParam')
            ->with(':created_at', $createdAt)
            ->once();

        $this->pdoStmtMock->shouldReceive('execute')
            ->once();

        $this->pdoMock->shouldReceive('lastInsertId')
            ->once()
            ->andReturn('100');

        $result = $this->urlChecker->insert($urlId, $statusCode, $h1, $title, $description, $createdAt);

        $this->assertEquals('100', $result);
    }

    public function testAllByUrlId(): void
    {
        $urlId = 5;
        $expected = [
            ['id' => 100,
                'url_id' => 5,
                'status_code' => 200,
                'h1' => 'test-h1',
                'title' => 'test-title',
                'description' => 'test-desc',
                'created_at' => '2024-06-03 10:10:10'],
            ['id' => 101,
                'url_id' => 5,
                'status_code' => 200,
                'h1' => 'test-h1',
                'title' => 'test-title',
                'description' => 'test-desc',
                'created_at' => '2024-06-03 10:10:10'],
        ];

        $this->pdoMock->shouldReceive('prepare')
            ->with("SELECT * FROM url_checks WHERE url_id = :url_id ORDER BY created_at")
            ->once()
            ->andReturn($this->pdoStmtMock);

        $this->pdoStmtMock->shouldReceive('bindParam')
            ->with(':url_id', $urlId)
            ->once();

        $this->pdoStmtMock->shouldReceive('execute')
            ->once();

        $this->pdoStmtMock->shouldReceive('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->andReturn($expected);

        $result = $this->urlChecker->allByUrlId($urlId);

        $this->assertEquals($expected, $result);
    }

    public function testLastByUrlId(): void
    {
        $urlId = 5;
        $expected = [
            ['id' => 100,
                'url_id' => 5,
                'status_code' => 200,
                'h1' => 'test-h1',
                'title' => 'test-title',
                'description' => 'test-desc',
                'created_at' => '2024-06-03 10:10:10']
        ];

        $this->pdoMock->shouldReceive('prepare')
            ->with("SELECT * FROM url_checks WHERE url_id = :url_id ORDER BY created_at DESC LIMIT 1")
            ->once()
            ->andReturn($this->pdoStmtMock);

        $this->pdoStmtMock->shouldReceive('bindParam')
            ->with(':url_id', $urlId)
            ->once();

        $this->pdoStmtMock->shouldReceive('execute')
            ->once();

        $this->pdoStmtMock->shouldReceive('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->andReturn($expected);

        $result = $this->urlChecker->lastByUrlId($urlId);

        $this->assertEquals($expected, $result);
    }
}
