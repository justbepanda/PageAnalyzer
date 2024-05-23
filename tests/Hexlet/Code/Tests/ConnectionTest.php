<?php

namespace Hexlet\Code\Tests;

use Hexlet\Code\Connection;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOException;

class ConnectionTest extends TestCase
{
    /**
     * Тест успешного подключения к базе данных
     */
    public function testConnect()
    {
        $pdo = Connection::get();

        $this->assertInstanceOf(PDO::class, $pdo, "Connection::get() should return instance of PDO");

        try {
            $pdo->query('SELECT 1');
            $this->assertTrue(true, "Connection is successful and SELECT 1 query executed");
        } catch (PDOException $e) {
            $this->fail("Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Тест выполнения SQL-запроса
     */
    public function testExecuteQuery()
    {
        $pdo = Connection::get();

        // Убедитесь, что таблица существует
        $createQuery = 'CREATE TABLE IF NOT EXISTS test_table (id SERIAL PRIMARY KEY, name VARCHAR(255))';
        $pdo->exec($createQuery);

        // Вставка записи
        $insertQuery = 'INSERT INTO test_table (name) VALUES (:name)';
        $stmt = $pdo->prepare($insertQuery);
        $stmt->execute(['name' => 'Test Name']);

        // Проверка вставки записи
        $selectQuery = 'SELECT name FROM test_table WHERE name = :name';
        $stmt = $pdo->prepare($selectQuery);
        $stmt->execute(['name' => 'Test Name']);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($result, "The inserted row should be found in the database");
        $this->assertEquals('Test Name', $result['name'], "The inserted name should match the query");

        // Очистка таблицы
        $pdo->exec('DROP TABLE test_table');
    }
}
