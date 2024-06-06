<?php

namespace Hexlet\Code\Tests;

use PHPUnit\Framework\TestCase;
use Mockery;
use Hexlet\Code\UrlRepository;
use PDO;
use PDOStatement;

class UrlRepositoryTest extends TestCase
{
    protected $pdoMock;
    protected $pdoStmtMock;
    protected $UrlRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdoMock = Mockery::mock(PDO::class);

        $this->pdoStmtMock = Mockery::mock(PDOStatement::class);

        $this->UrlRepository = new UrlRepository($this->pdoMock);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Убеждаемся, что все ожидаемые вызовы были выполнены
        Mockery::close();
    }

    public function testInsert(): void
    {
        // Устанавливаем ожидаемые значения
        $name = "https://ya.ru";
        $created = "2020-10-10 10:10:10";

        $this->pdoMock->shouldReceive('prepare')
            ->once()
            ->with("INSERT INTO urls (name, created_at) VALUES(:name, :created)")
            ->andReturn($this->pdoStmtMock);

        $this->pdoStmtMock->shouldReceive('bindParam')
            ->with(':name', $name)
            ->once();

        $this->pdoStmtMock->shouldReceive('bindParam')
            ->with(':created', $created)
            ->once();

        $this->pdoStmtMock->shouldReceive('execute')
            ->once();

        $this->pdoMock->shouldReceive('lastInsertId')
            ->once()
            ->andReturn('100');

        $result = $this->UrlRepository->insert($name, $created);

        $this->assertEquals('100', $result);
    }

    public function testFindById(): void
    {
        $id = 100;
        $expected = [
            ['id' => 100, 'name' => 'https://hexlet.io', 'created_at' => '2024-06-03 10:10:10']
        ];

        $this->pdoMock->shouldReceive('prepare')
            ->once()
            ->with("SELECT * FROM urls WHERE id = :id")
            ->andReturn($this->pdoStmtMock);

        $this->pdoStmtMock->shouldReceive('bindParam')
            ->with('id', $id)
            ->once();

        $this->pdoStmtMock->shouldReceive('execute')
            ->once();

        $this->pdoStmtMock->shouldReceive('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->once()
            ->andReturn($expected);

        $result = $this->UrlRepository->findById($id);

        $this->assertEquals($expected, $result);
    }

    public function testAll(): void
    {
        $expected = [
            ['id' => 100, 'name' => 'https://hexlet.io', 'created_at' => '2024-06-03 10:10:10'],
            ['id' => 101, 'name' => 'https://ya.ru', 'created_at' => '2024-06-03 10:10:10']
        ];

        $this->pdoMock->shouldReceive('query')
            ->with("SELECT * FROM urls")
            ->once()
            ->andReturn($this->pdoStmtMock);

        $this->pdoStmtMock->shouldReceive('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->andReturn($expected);

        $result = $this->UrlRepository->all();

        $this->assertEquals($expected, $result);
    }

    public function testFindByName(): void
    {
        $name = "https://hexlet.io";

        $expected = [
            ['id' => 100, 'name' => 'https://hexlet.io', 'created_at' => '2024-06-03 10:10:10']
        ];

        $this->pdoMock->shouldReceive('prepare')
            ->once()
            ->with("SELECT * FROM urls WHERE name = :name")
            ->andReturn($this->pdoStmtMock);

        $this->pdoStmtMock->shouldReceive('bindParam')
            ->with('name', $name)
            ->andReturn(true);

        $this->pdoStmtMock->shouldReceive('execute')
            ->andReturn(true);

        $this->pdoStmtMock->shouldReceive('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->andReturn($expected);

        $result = $this->UrlRepository->findByName($name);

        $this->assertEquals($expected, $result);
    }
}
