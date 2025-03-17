<?php

namespace PowerPdo\Tests\QueryProcessor;

use PHPUnit\Framework\TestCase;
use PowerPdo\QueryProcessor\DefaultQueryProcessor;
use PowerPdo\Core\PDOLogger;

class QueryProcessorTest extends TestCase
{
    private PDOLogger $pdo;

    protected function setUp(): void
    {
        $processor = new \PowerPdo\QueryProcessor\FilterableQueryProcessor();
        $processor->addFilter(function(string $query): string {
            return "/* Query Start */ $query /* Query End */";
        });

        $this->pdo = new PDOLogger('sqlite::memory:', null, null, null, null, $processor);
        $this->pdo->exec('CREATE TABLE test (id INTEGER PRIMARY KEY, value TEXT)');
    }

    public function testQueryProcessorModifiesQueries(): void
    {
        $stmt = $this->pdo->prepare('SELECT * FROM test');
        $this->assertStringContainsString('/* Query Start */', $stmt->queryString);
        $this->assertStringContainsString('/* Query End */', $stmt->queryString);
    }

    public function testQueryProcessorChainedFilters(): void
    {
        $processor = new \PowerPdo\QueryProcessor\FilterableQueryProcessor();

        $processor->addFilter(fn(string $q) => "/* First */ $q");
        $processor->addFilter(fn(string $q) => "$q /* Second */");

        $pdo = new PDOLogger('sqlite::memory:', null, null, null, null, $processor);
        $stmt = $pdo->prepare('SELECT 1');
        
        $this->assertStringStartsWith('/* First */', $stmt->queryString);
        $this->assertStringEndsWith('/* Second */', $stmt->queryString);
    }
}
