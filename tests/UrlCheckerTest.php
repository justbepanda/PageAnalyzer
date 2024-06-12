<?php

namespace Hexlet\Code\Tests;

use DiDom\Document;
use DiDom\Element;
use Hexlet\Code\UrlChecker;
use Hexlet\Code\UrlRepository;
use PHPUnit\Framework\TestCase;
use Mockery;
use PDO;
use PDOStatement;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

class UrlCheckerTest extends TestCase
{
    protected mixed $pdoMock;
    protected mixed $pdoStmtMock;
    protected mixed $urlChecker;
    protected mixed $urlRepo;
    protected mixed $h1ElementMock;
    protected mixed $documentMock;
    protected mixed $titleElementMock;
    protected mixed $metaElementMock;


    protected function setUp(): void
    {
        parent::setUp();

        $this->pdoMock = Mockery::mock(PDO::class);
        $this->pdoStmtMock = Mockery::mock(PDOStatement::class);
        $this->urlChecker = Mockery::mock(UrlChecker::class, [$this->pdoMock])->makePartial();
        $this->urlRepo = Mockery::mock(UrlRepository::class);
        $this->documentMock = Mockery::mock(Document::class);
        $this->titleElementMock = Mockery::mock(Element::class);
        $this->h1ElementMock = Mockery::mock(Element::class);
        $this->metaElementMock = Mockery::mock(Element::class);
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


    public function testGetUrlResponseSuccess()
    {
        $url = 'https://hexlet.io';
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], 'Hello, World')
        ]);
        $handlerStack = HandlerStack::create($mock);
        $clientMock = new Client(['handler' => $handlerStack]);
        $result = $this->urlChecker->getUrlResponse($url, $clientMock);
        $this->assertEquals(200, $result['statusCode']);
        $this->assertEquals('success', $result['flash']['type']);
        $this->assertEquals('Страница успешно проверена', $result['flash']['text']);
    }

    public function testGetUrlResponseWarning()
    {
        $url = 'https://hexlet.io';
        $mock = new MockHandler([
            new Response(403, ['X-Foo' => 'Bar'], 'Hello, World')
        ]);
        $handlerStack = HandlerStack::create($mock);
        $clientMock = new Client(['handler' => $handlerStack]);
        $result = $this->urlChecker->getUrlResponse($url, $clientMock);
        $this->assertEquals(403, $result['statusCode']);
        $this->assertEquals('warning', $result['flash']['type']);
        $this->assertEquals('Проверка была выполнена успешно, но сервер ответил с ошибкой', $result['flash']['text']);
    }

    public function testGetUrlResponseWarningException()
    {
        $url = 'https://hexlet.io';
        $mock = new MockHandler([
            new RequestException('Error Communicating with Server', new Request('GET', $url))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $clientMock = new Client(['handler' => $handlerStack]);
        $result = $this->urlChecker->getUrlResponse($url, $clientMock);
        $this->assertNull($result['statusCode']);
        $this->assertEquals('warning', $result['flash']['type']);
        $this->assertEquals('Проверка была выполнена успешно, но сервер ответил с ошибкой', $result['flash']['text']);
    }



    public function testGetDocumentData()
    {

        $this->documentMock->shouldReceive('first')
            ->with('title')
            ->andReturn($this->titleElementMock);

        $this->documentMock->shouldReceive('first')
            ->with('h1')
            ->andReturn($this->h1ElementMock);

        $this->documentMock->shouldReceive('first')
            ->with('meta[name=description]')
            ->andReturn($this->metaElementMock);

        $this->titleElementMock->shouldReceive('text')
            ->andReturn('Example Title');

        $this->h1ElementMock->shouldReceive('text')
            ->andReturn('Example H1');

        $this->metaElementMock->shouldReceive('getAttribute')
            ->with('content')
            ->andReturn('Example Description');

        $result = $this->urlChecker->getDocumentData('https://hexlet.io', $this->documentMock);

        $this->assertEquals('Example Title', $result['title']);
        $this->assertEquals('Example H1', $result['h1']);
        $this->assertEquals('Example Description', $result['description']);
    }
}
