<?php

namespace Hexlet\Code\Tests;

use Exception;
use Hexlet\Code\Connection;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    public function testGet(): void
    {
        $connection = Connection::get();
        $this->assertInstanceOf(Connection::class, $connection);
    }

    /**
     * @throws Exception
     */
    public function testConnect(): void
    {
        $connection = Connection::get();
        $pdo = $connection->connect();
        $this->assertInstanceOf(\PDO::class, $pdo);
    }
}
