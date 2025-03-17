<?php

namespace PowerPdo\Logging;

class NullLogger implements LoggerInterface
{
    public function log(string $action, array $context): void
    {
        // Do nothing
    }
}
