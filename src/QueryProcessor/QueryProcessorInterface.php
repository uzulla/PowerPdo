<?php

namespace PowerPdo\QueryProcessor;

interface QueryProcessorInterface
{
    public function process(string $query): string;
}
