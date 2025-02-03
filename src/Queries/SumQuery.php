<?php

namespace Il4mb\Db\Queries;

use Il4mb\Db\Abstract\Queriable;
use Il4mb\Db\Conditions;
use Il4mb\Db\Table;

class SumQuery implements Queriable
{

    protected string $column;

    public function __construct(string $column)
    {
        $this->column = $column;
    }


    protected array $whenQueries = [];
    function when(...$conditions): static
    {
        $this->whenQueries[] = new Conditions($conditions);
        return $this;
    }

    protected string $trueValue = "0";
    protected string $falseValue = "0";
    function then($trueValue, $falseValue = null): static
    {
        $this->trueValue = $trueValue;
        $this->falseValue = $falseValue ?? $this->falseValue;
        return $this;
    }


    public function toQuery(Table|null $table = null): string
    {
        if (!empty($this->whenQueries)) {
            $whenClause = implode(
                " OR ",
                array_map(
                    fn(Conditions $query): string => $query->toQuery($table),
                    $this->whenQueries
                )
            );
            return "SUM(CASE WHEN {$whenClause} THEN '{$this->trueValue}' ELSE '{$this->falseValue}' END) AS {$this->column}";
        }
        return "SUM({$this->column})";
    }
}
