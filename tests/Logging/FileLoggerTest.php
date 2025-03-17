<?php

namespace PowerPdo\Tests\Logging;

use PHPUnit\Framework\TestCase;
use PowerPdo\Logging\FileLogger;
use PowerPdo\Core\PDOLogger;

class FileLoggerTest extends TestCase
{
    private string $logFile;
    private PDOLogger $pdo;

    protected function setUp(): void
    {
        $this->logFile = __DIR__ . '/test.log';
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }

        $logger = new FileLogger($this->logFile);
        $this->pdo = new PDOLogger('sqlite::memory:', null, null, null, $logger);
        $this->pdo->exec('CREATE TABLE test (id INTEGER PRIMARY KEY, name TEXT)');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    public function testLogsQueryExecution(): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO test (name) VALUES (:name)');
        $stmt->execute(['name' => 'Test User']);

        $logContent = file_get_contents($this->logFile);
        $logEntries = array_values(array_filter(array_map(function($entry) {
            return trim($entry);
        }, explode("---\n", $logContent)), 'strlen'));
        
        // We expect: exec, exec_result, prepare, execute, execute_result
        $this->assertCount(5, $logEntries);
        
        $actions = array_map(function($entry) {
            $data = json_decode($entry, true);
            return $data['action'];
        }, $logEntries);
        
        $this->assertEquals([
            'exec',
            'exec_result',
            'prepare',
            'execute',
            'execute_result'
        ], $actions, 'Log entries should be in correct order');
        
        foreach ($logEntries as $entry) {
            $data = json_decode($entry, true);
            $this->assertArrayHasKey('timestamp', $data);
            $this->assertArrayHasKey('action', $data);
            $this->assertArrayHasKey('caller', $data);
            $this->assertArrayHasKey('data', $data);
            
            if ($data['action'] === 'execute') {
                $this->assertArrayHasKey('params', $data['data']);
                $this->assertEquals(['name' => 'Test User'], $data['data']['params']);
            }
        }
    }

    public function testLogsDebugBacktrace(): void
    {
        $this->pdo->exec('SELECT * FROM test');
        
        $logContent = file_get_contents($this->logFile);
        $logEntries = array_values(array_filter(array_map(function($entry) {
            return trim($entry);
        }, explode("---\n", $logContent)), 'strlen'));
        
        foreach ($logEntries as $entry) {
            $data = json_decode($entry, true);
            $this->assertNotNull($data, 'Failed to decode JSON: ' . json_last_error_msg());
            $this->assertArrayHasKey('caller', $data);
            $this->assertStringContainsString('FileLoggerTest.php', $data['caller']);
            $this->assertArrayHasKey('data', $data);
            $this->assertArrayHasKey('trace', $data['data']);
        }
    }
}
