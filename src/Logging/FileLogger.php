<?php

namespace PowerPdo\Logging;

class FileLogger implements LoggerInterface
{
    private string $logFile;
    private bool $prettyPrint;

    public function __construct(string $logFile, bool $prettyPrint = true)
    {
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->logFile = $logFile;
        $this->prettyPrint = $prettyPrint;
    }

    public function log(string $action, array $context): void
    {
        $timestamp = date('Y-m-d\TH:i:sP'); // ISO 8601 format with timezone
        $trace = $context['trace'] ?? [];
        $caller = !empty($trace) ? "{$trace[0]['file']}:{$trace[0]['line']}" : 'unknown location';
        
        $logEntry = [
            'timestamp' => $timestamp,
            'action' => $action,
            'caller' => $caller,
            'data' => $context
        ];

        // Remove circular references and non-serializable data
        $context = array_filter($context, function($value) {
            return !is_resource($value) && !is_object($value);
        });
        
        $logEntry['data'] = $context;
        
        try {
            $output = json_encode($logEntry, $this->prettyPrint 
                ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES 
                : 0);

            if ($output === false) {
                throw new \RuntimeException('Failed to encode log entry: ' . json_last_error_msg());
            }

            file_put_contents($this->logFile, $output . "\n---\n", FILE_APPEND | LOCK_EX);
        } catch (\Exception $e) {
            error_log('Failed to write log entry: ' . $e->getMessage());
        }
    }
}
