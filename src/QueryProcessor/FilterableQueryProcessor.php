<?php

namespace PowerPdo\QueryProcessor;

class FilterableQueryProcessor implements QueryProcessorInterface
{
    private array $filters = [];
    
    public function addFilter(callable $filter): void
    {
        $this->filters[] = $filter;
    }

    public function process(string $query): string
    {
        $result = $query;
        foreach ($this->filters as $filter) {
            $result = $filter($result);
        }
        return $result;
    }
}
