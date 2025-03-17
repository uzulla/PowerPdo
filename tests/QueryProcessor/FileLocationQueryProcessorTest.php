<?php

namespace PowerPdo\Tests\QueryProcessor;

use PHPUnit\Framework\TestCase;
use PowerPdo\QueryProcessor\FileLocationQueryProcessor;

class FileLocationQueryProcessorTest extends TestCase
{
    private FileLocationQueryProcessor $processor;
    
    protected function setUp(): void
    {
        $this->processor = new FileLocationQueryProcessor();
    }
    
    public function testProcessAddsFileLocationComment(): void
    {
        $query = "SELECT * FROM users";
        $processedQuery = $this->processor->process($query);
        
        $this->assertStringContainsString('/* Called from:', $processedQuery);
        $this->assertStringContainsString('FileLocationQueryProcessorTest.php', $processedQuery);
        $this->assertStringContainsString($query, $processedQuery);
    }
    
    public function testProcessPreservesOriginalQuery(): void
    {
        $query = "INSERT INTO products (name, price) VALUES ('Test', 10.99)";
        $processedQuery = $this->processor->process($query);
        
        $this->assertStringEndsWith($query, substr($processedQuery, strpos($processedQuery, '*/ ') + 3));
    }
}
