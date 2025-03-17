<?php

namespace PowerPdo\Tests\Integration;

use PDO;
use PHPUnit\Framework\TestCase;
use PowerPdo\Core\PDOLogger;
use PowerPdo\Logging\FileLogger;
use PowerPdo\QueryProcessor\FilterableQueryProcessor;

class SQLiteIntegrationTest extends TestCase
{
    private PDOLogger $pdo;
    private string $logFile;
    private FilterableQueryProcessor $queryProcessor;

    protected function setUp(): void
    {
        $this->logFile = __DIR__ . '/sqlite_test.log';
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }

        $this->queryProcessor = new FilterableQueryProcessor();
        $logger = new FileLogger($this->logFile);
        
        $this->pdo = new PDOLogger('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ], $logger, $this->queryProcessor);

        // Create test tables
        $this->pdo->exec('CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE
        )');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    public function testBasicCRUDOperations(): void
    {
        // Test INSERT
        $stmt = $this->pdo->prepare('INSERT INTO users (name, email) VALUES (:name, :email)');
        $stmt->execute(['name' => 'John Doe', 'email' => 'john@example.com']);
        $userId = $this->pdo->lastInsertId();
        $this->assertNotEmpty($userId);

        // Test SELECT
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('John Doe', $user['name']);

        // Test UPDATE
        $stmt = $this->pdo->prepare('UPDATE users SET name = :name WHERE id = :id');
        $stmt->execute(['name' => 'Jane Doe', 'id' => $userId]);
        
        // Verify UPDATE
        $stmt = $this->pdo->prepare('SELECT name FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $this->assertEquals('Jane Doe', $stmt->fetchColumn());

        // Test DELETE
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        
        // Verify DELETE
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $this->assertEquals(0, $stmt->fetchColumn());
    }

    public function testQueryProcessorModification(): void
    {
        $this->queryProcessor->addFilter(function(string $query): string {
            return "/* Modified Query */ $query";
        });

        $stmt = $this->pdo->prepare('SELECT * FROM users');
        $logContent = file_get_contents($this->logFile);
        $this->assertStringContainsString('/* Modified Query */', $logContent);
    }

    public function testErrorHandling(): void
    {
        $this->expectException(\PDOException::class);
        $this->expectExceptionMessageMatches('/no such table/');
        
        $stmt = $this->pdo->prepare('SELECT * FROM nonexistent_table');
        $stmt->execute();
    }

    public function testDebugBacktraceLogging(): void
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users');
        $stmt->execute();
        
        $logContent = file_get_contents($this->logFile);
        $logEntries = array_values(array_filter(array_map(function($entry) {
            return trim($entry);
        }, explode("---\n", $logContent)), 'strlen'));
        
        foreach ($logEntries as $entry) {
            $data = json_decode($entry, true);
            $this->assertArrayHasKey('data', $data);
            $this->assertArrayHasKey('trace', $data['data']);
            $this->assertStringContainsString('SQLiteIntegrationTest.php', $data['caller']);
        }
    }

    public function testTransactionHandling(): void
    {
        $this->pdo->beginTransaction();
        
        try {
            $stmt = $this->pdo->prepare('INSERT INTO users (name, email) VALUES (:name, :email)');
            $stmt->execute(['name' => 'User 1', 'email' => 'user1@example.com']);
            $stmt->execute(['name' => 'User 2', 'email' => 'user2@example.com']);
            
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        $stmt = $this->pdo->query('SELECT COUNT(*) FROM users');
        $this->assertEquals(2, $stmt->fetchColumn());
    }
}
