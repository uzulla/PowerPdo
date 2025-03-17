<?php

namespace PowerPdo\Core;

use PDO;
use PowerPdo\Debug\DebugBacktrace;
use PowerPdo\Logging\LoggerInterface;
use PowerPdo\QueryProcessor\QueryProcessorInterface;
use PowerPdo\QueryProcessor\DefaultQueryProcessor;
use PowerPdo\Logging\NullLogger;

class PDOLogger extends PDO
{
    private LoggerInterface $logger;
    private QueryProcessorInterface $queryProcessor;
    private DebugBacktrace $debugBacktrace;

    public function __construct(
        string $dsn,
        ?string $username = null,
        ?string $password = null,
        ?array $options = null,
        ?LoggerInterface $logger = null,
        ?QueryProcessorInterface $queryProcessor = null
    ) {
        $options = $options ?? [];
        // Force exception mode before setting statement class to ensure errors propagate correctly
        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        $options[PDO::ATTR_EMULATE_PREPARES] = false; // Disable emulation for proper error handling
        $options[PDO::ATTR_STATEMENT_CLASS] = [PDOStatementLogger::class, []];
        
        parent::__construct($dsn, $username, $password, $options);
        
        $this->logger = $logger ?? new NullLogger();
        $this->queryProcessor = $queryProcessor ?? new DefaultQueryProcessor();
        $this->debugBacktrace = new DebugBacktrace();
    }

    /**
     * @return \PDOStatement|false
     */
    #[\ReturnTypeWillChange]
    public function prepare($query, $options = [])
    {
        $processedQuery = $this->queryProcessor->process($query);
        $trace = $this->debugBacktrace->getTrace();

        $this->logger->log('prepare', [
            'query' => $processedQuery,
            'options' => $options,
            'trace' => $trace
        ]);

        try {
            $stmt = parent::prepare($processedQuery, $options);

            if ($stmt === false) {
                return false;
            }

            if ($stmt instanceof PDOStatementLogger) {
                $stmt->init($this->logger, $this->debugBacktrace, $processedQuery);
            }

            return $stmt;
        } catch (\PDOException $e) {
            $this->logger->log('prepare_error', [
                'query' => $processedQuery,
                'error' => $e->getMessage(),
                'trace' => $trace
            ]);
            throw $e;
        }
    }

    /**
     * @return int|false
     */
    #[\ReturnTypeWillChange]
    public function exec($statement)
    {
        $processedStatement = $this->queryProcessor->process($statement);
        $trace = $this->debugBacktrace->getTrace();

        $this->logger->log('exec', [
            'query' => $processedStatement,
            'trace' => $trace
        ]);

        try {
            $result = parent::exec($processedStatement);

            $this->logger->log('exec_result', [
                'query' => $processedStatement,
                'result' => $result,
                'trace' => $trace
            ]);

            return $result;
        } catch (\PDOException $e) {
            $this->logger->log('exec_error', [
                'query' => $processedStatement,
                'error' => $e->getMessage(),
                'trace' => $trace
            ]);
            throw $e;
        }
    }

    /**
     * @return \PDOStatement|false
     */
    #[\ReturnTypeWillChange]
    public function query($query, $fetchMode = null, ...$fetchModeArgs)
    {
        $processedQuery = $this->queryProcessor->process($query);
        $trace = $this->debugBacktrace->getTrace();

        $this->logger->log('query', [
            'query' => $processedQuery,
            'fetchMode' => $fetchMode,
            'trace' => $trace
        ]);

        try {
            if ($fetchMode !== null) {
                $stmt = parent::query($processedQuery, $fetchMode, ...$fetchModeArgs);
            } else {
                $stmt = parent::query($processedQuery);
            }

            if ($stmt === false) {
                return false;
            }

            if ($stmt instanceof PDOStatementLogger) {
                $stmt->init($this->logger, $this->debugBacktrace, $processedQuery);
            }

            return $stmt;
        } catch (\PDOException $e) {
            $this->logger->log('query_error', [
                'query' => $processedQuery,
                'error' => $e->getMessage(),
                'trace' => $trace
            ]);
            throw $e;
        }
    }
}
