<?php

namespace PowerPdo\Core;

use PDOStatement;
use PowerPdo\Debug\DebugBacktrace;
use PowerPdo\Logging\LoggerInterface;

class PDOStatementLogger extends PDOStatement
{
    private ?LoggerInterface $logger = null;
    private ?DebugBacktrace $debugBacktrace = null;
    private ?string $originalQuery = null;
    private array $boundParams = [];

    protected function __construct()
    {
        // Constructor must be protected for PDO internally
    }

    public function init(LoggerInterface $logger, DebugBacktrace $debugBacktrace, string $query): bool
    {
        $this->logger = $logger;
        $this->debugBacktrace = $debugBacktrace;
        $this->originalQuery = $query;
        return true;
    }

    /**
     * @param array|null $params
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function execute($params = null)
    {
        if (!isset($this->logger)) {
            return parent::execute($params);
        }

        $trace = $this->debugBacktrace->getTrace();
        
        // Let PDO handle parameter validation natively

        $this->logger->log('execute', [
            'query' => $this->originalQuery,
            'params' => $params,
            'trace' => $trace
        ]);

        try {
            $result = parent::execute($params);

            $this->logger->log('execute_result', [
                'query' => $this->originalQuery,
                'params' => $params,
                'result' => $result,
                'trace' => $trace
            ]);

            return $result;
        } catch (\PDOException $e) {
            $this->logger->log('execute_error', [
                'query' => $this->originalQuery,
                'params' => $params,
                'error' => $e->getMessage(),
                'trace' => $trace
            ]);
            throw $e;
        }
    }

    private function parameterExists(string $param): bool
    {
        $paramWithoutColon = ltrim($param, ':');
        return strpos($this->originalQuery, ':' . $paramWithoutColon) !== false;
    }

    public function bindValue($parameter, $value, $type = \PDO::PARAM_STR): bool
    {
        if (!isset($this->logger)) {
            return parent::bindValue($parameter, $value, $type);
        }

        $this->boundParams[$parameter] = true;

        $this->logger->log('bind_value', [
            'parameter' => $parameter,
            'value' => $value,
            'type' => $type,
            'trace' => $this->debugBacktrace->getTrace()
        ]);

        return parent::bindValue($parameter, $value, $type);
    }

    public function bindParam($parameter, &$variable, $type = \PDO::PARAM_STR, $length = null, $options = null): bool
    {
        if (!isset($this->logger)) {
            return parent::bindParam($parameter, $variable, $type, $length, $options);
        }

        $this->boundParams[$parameter] = true;

        $this->logger->log('bind_param', [
            'parameter' => $parameter,
            'type' => $type,
            'length' => $length,
            'options' => $options,
            'trace' => $this->debugBacktrace->getTrace()
        ]);

        return parent::bindParam($parameter, $variable, $type, $length, $options);
    }
}
