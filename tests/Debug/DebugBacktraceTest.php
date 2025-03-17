<?php

namespace PowerPdo\Tests\Debug;

use PHPUnit\Framework\TestCase;
use PowerPdo\Debug\DebugBacktrace;

class DebugBacktraceTest extends TestCase
{
    private DebugBacktrace $debugBacktrace;

    protected function setUp(): void
    {
        $this->debugBacktrace = new DebugBacktrace();
    }

    public function testGetTraceReturnsValidStackTrace(): void
    {
        $trace = $this->getTraceFromNestedFunction();
        
        $this->assertIsArray($trace);
        $this->assertNotEmpty($trace);
        $this->assertArrayHasKey('file', $trace[0]);
        $this->assertArrayHasKey('line', $trace[0]);
        $this->assertStringContainsString('DebugBacktraceTest.php', $trace[0]['file']);
    }

    public function testGetTraceFiltersInternalCalls(): void
    {
        $trace = $this->debugBacktrace->getTrace();
        
        foreach ($trace as $entry) {
            $this->assertStringNotContainsString('php-pdo-logger/src', $entry['file']);
        }
    }

    public function testGetTraceIncludesMethodNames(): void
    {
        $trace = $this->getTraceFromNestedFunction();
        
        $this->assertArrayHasKey('function', $trace[0]);
        // In PHP 7.4, the function name might be different due to how debug_backtrace works
        $this->assertContains($trace[0]['function'], ['getTraceFromNestedFunction', 'getTrace']);
    }

    private function getTraceFromNestedFunction(): array
    {
        return $this->debugBacktrace->getTrace();
    }
}
