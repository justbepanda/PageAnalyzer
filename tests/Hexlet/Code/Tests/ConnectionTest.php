<?php

namespace Hexlet\Code\Tests;

use PHPUnit\Framework\TestCase;
use Hexlet\Code\Connection;

class ConnectionTest extends TestCase
{
    public function testConnect()
    {
        // Подготовка данных для теста (предполагается, что конфигурация базы данных передана в окружение)
        putenv('DATABASE_URL=postgresql://mof:postgres@localhost:5432/project9_dev');

        // Создание объекта Connection
        $connection = Connection::get();

        // Вызываем метод connect
        $pdo = $connection->connect();

        // Проверяем, что полученный объект является экземпляром \PDO
        $this->assertInstanceOf(\PDO::class, $pdo);
    }
    public function testGet()
    {
        $connection = Connection::get();

        // Проверяем, что полученный объект является экземпляром Connection
        $this->assertInstanceOf(Connection::class, $connection);

        // Проверяем, что повторные вызовы возвращают один и тот же объект
        $this->assertSame($connection, Connection::get());
    }
}
