<?php

namespace Hexlet\Code\Tests;

use Exception;
use Hexlet\Code\Connection;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testConnect(): void
    {
        $connection = Connection::get();
        $this->assertInstanceOf(Connection::class, $connection);
        $pdo = $connection->connect();
        $this->assertInstanceOf(\PDO::class, $pdo);
    }
}
