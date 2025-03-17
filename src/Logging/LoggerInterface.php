<?php

namespace PowerPdo\Logging;

interface LoggerInterface
{
    public function log(string $action, array $context): void;
}
