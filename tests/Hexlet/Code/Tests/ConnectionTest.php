<?php

namespace Hexlet\Code\Tests;

use PHPUnit\Framework\TestCase;
use Hexlet\Code\Connection;

class ConnectionTest extends TestCase
{
    public function testGet(): void
    {
        $connection = Connection::get();
        $this->assertInstanceOf(Connection::class, $connection);
    }

    public function testConnect(): void
    {
        $connection = Connection::get();
        $pdo = $connection->connect();
        $this->assertInstanceOf(\PDO::class, $pdo);
    }
}
