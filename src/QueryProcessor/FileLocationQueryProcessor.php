<?php

namespace PowerPdo\QueryProcessor;

use PowerPdo\Debug\DebugBacktrace;

/**
 * Query processor that adds file location comments to SQL queries
 */
class FileLocationQueryProcessor implements QueryProcessorInterface
{
    private DebugBacktrace $debugBacktrace;
    
    public function __construct()
    {
        $this->debugBacktrace = new DebugBacktrace();
    }
    
    public function process(string $query): string
    {
        $trace = $this->debugBacktrace->getTrace();
        
        if (empty($trace)) {
            return $query;
        }
        
        // Get the first trace entry (closest to the application code)
        $caller = $trace[0];
        $file = $caller['file'] ?? 'unknown';
        $line = $caller['line'] ?? 0;
        
        return "/* Called from: {$file}:{$line} */ {$query}";
    }
}
