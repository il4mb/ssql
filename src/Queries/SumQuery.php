<?php

namespace Il4mb\SSQL\Queries;

use Il4mb\SSQL\Abstract\Queriable;
use Il4mb\SSQL\Cores\Conditions;
use Il4mb\SSQL\Cores\Table;

class SumQuery implements Queriable
{
    protected string $column;
    protected ?string $alias = null;
    protected array $whenQueries = [];
    protected string $trueValue = "1";
    protected string $falseValue = "0";
    protected string $partitionOrder = "";
    protected ?string $partition = null;

    public function __construct(string $column, ?string $alias = null)
    {
        if (empty($column)) {
            throw new \InvalidArgumentException("Column name cannot be empty.");
        }

        $this->column = $column;
        $this->alias = $alias ?? $column;
    }

    public function when(...$conditions): static
    {
        $this->whenQueries[] = new Conditions($conditions);
        return $this;
    }

    /**
     * SUM::then
     * @param string $trueValue - The value to return if the condition is true. 
     * @param string $falseValue - Optional.
     * @return SumQuery - The updated SumQuery instance.
     */
    public function then($trueValue, $falseValue = null): static
    {
        $this->trueValue = $trueValue;
        $this->falseValue = $falseValue ?? $this->falseValue;
        return $this;
    }

    /**
     * SumQuery::over 
     * @param string $partition - The column 
     */
    public function over(string $partition, string|array $order = []): static
    {
        $this->partition = $partition;
        if (is_array($order) && !empty($order)) {
            $this->partitionOrder = " ORDER BY {$order[0]} " . strtoupper($order[1] ?? "ASC");
        } else if (is_string($order)) {
            $this->partitionOrder = " ORDER BY {$order} ASC";
        }
        return $this;
    }

    public function toQuery(?Table $table = null): string
    {
        $overClause = "";
        if (!empty($this->partition)) {
            $overClause = " OVER(PARTITION BY {$this->partition}{$this->partitionOrder})";
        }

        if (!empty($this->whenQueries)) {
            $whenClause = implode(
                " OR ",
                array_map(
                    fn(Conditions $query): string => $query->toQuery($table),
                    $this->whenQueries
                )
            );

            return "SUM(CASE WHEN {$whenClause} THEN '{$this->trueValue}' ELSE '{$this->falseValue}' END){$overClause} AS " . ($this->alias ?? $this->column);
        }

        return "SUM({$this->column}){$overClause} AS {$this->alias}";
    }
}
