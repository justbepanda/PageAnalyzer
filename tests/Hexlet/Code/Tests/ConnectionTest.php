<?php

namespace Hexlet\Code\Tests;

use PHPUnit\Framework\TestCase;
use Hexlet\Code\Connection;

class ConnectionTest extends TestCase
{
    public function testGet(): void
    {
        putenv("DATABASE_URL=postgresql://mof:postgres@localhost:5432/project9_dev");
        $connection = Connection::get();
        $this->assertInstanceOf(Connection::class, $connection);
    }

    public function testConnect(): void
    {
        putenv("DATABASE_URL=postgresql://mof:postgres@localhost:5432/project9_dev");
        $connection = Connection::get();
        $pdo = $connection->connect();
        $this->assertInstanceOf(\PDO::class, $pdo);
    }

    public function testConnectException(): void
    {
        // Mocking the $_ENV['DATABASE_URL'] to simulate a bad configuration
        $_ENV['DATABASE_URL'] = 'bad_url';

        $connection = Connection::get();
        $this->expectException(\Exception::class);
        $connection->connect();
    }
}
