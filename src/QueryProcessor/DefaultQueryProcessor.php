<?php

namespace PowerPdo\QueryProcessor;

class DefaultQueryProcessor implements QueryProcessorInterface
{
    public function process(string $query): string
    {
        return $query;
    }
}
