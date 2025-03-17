<?php

namespace PowerPdo\Tests\Core;

use PDO;
use PHPUnit\Framework\TestCase;
use PowerPdo\Core\PDOLogger;
use PowerPdo\Core\PDOStatementLogger;
use PowerPdo\Logging\LoggerInterface;

class PDOLoggerTest extends TestCase
{
    private PDOLogger $pdo;
    private $mockLogger;

    protected function setUp(): void
    {
        $this->mockLogger = $this->createMock(LoggerInterface::class);
        $this->pdo = new PDOLogger('sqlite::memory:', null, null, null, $this->mockLogger);
        
        // Create test table
        $this->pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');
    }

    public function testPrepareReturnsExtendedStatement(): void
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $this->assertInstanceOf(PDOStatementLogger::class, $stmt);
    }

    public function testExecuteLogsQuery(): void
    {
        $this->mockLogger->expects($this->exactly(3))
            ->method('log')
            ->withConsecutive(
                [$this->equalTo('prepare'), $this->arrayHasKey('query')],
                [$this->equalTo('execute'), $this->arrayHasKey('query')],
                [$this->equalTo('execute_result'), $this->arrayHasKey('query')]
            );

        $stmt = $this->pdo->prepare('INSERT INTO users (name) VALUES (:name)');
        $stmt->execute(['name' => 'Test User']);
    }

    public function testQueryProcessorModifiesQuery(): void
    {
        $processor = new class implements \PowerPdo\QueryProcessor\QueryProcessorInterface {
            public function process(string $query): string {
                return "/* Modified */ $query";
            }
        };

        $pdo = new PDOLogger('sqlite::memory:', null, null, null, $this->mockLogger, $processor);
        $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');
        
        $this->mockLogger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo('prepare'),
                $this->callback(function ($context) {
                    return strpos($context['query'], '/* Modified */') === 0;
                })
            );

        $pdo->prepare('SELECT * FROM users');
    }
}
