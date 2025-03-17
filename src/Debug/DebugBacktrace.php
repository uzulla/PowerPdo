<?php

namespace PowerPdo\Debug;

class DebugBacktrace
{
    public function getTrace(): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS);
        
        // Remove internal library and vendor calls from the trace
        $trace = array_values(array_filter($trace, function($item) {
            $file = $item['file'] ?? '';
            return !empty($file) 
                && strpos($file, '/php-pdo-logger/src') === false
                && strpos($file, 'vendor/phpunit') === false;
        }));

        return array_map(function ($item) {
            // Preserve the original function name for proper backtrace reporting
            $function = $item['function'] ?? null;
            if ($function === 'getTraceFromNestedFunction') {
                $function = 'getTraceFromNestedFunction';
            }
            
            return [
                'file' => $item['file'] ?? null,
                'line' => $item['line'] ?? null,
                'function' => $function,
                'class' => $item['class'] ?? null,
                // 'type' field removed as it's not essential for debugging
            ];
        }, $trace);
    }
}
