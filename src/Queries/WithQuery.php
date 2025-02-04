<?php

namespace Il4mb\SSQL\Queries;

use Il4mb\SSQL\Abstract\Queriable;
use Il4mb\SSQL\Cores\Table;

class WithQuery implements Queriable
{

    protected Table $table;
    protected string $defineName;
    protected ?string $alias = null;

    function __construct(string $defineName, ?string $alias = null)
    {
        $this->defineName = $defineName;
        $this->alias = $alias;
    }


    protected array $columns = [];
    protected SelectQuery $selecQuery;
    function select(string|array $tableName, array $columns): static
    {
        $this->table = new Table(...(
            is_array($tableName) && count($tableName) > 1
            ? ["name" => $tableName[0], "alias" => $tableName[1]] : (
                is_array($tableName)
                ? ["name" => $tableName[0] ?? null]
                : [$tableName]
            )
        ));
        $this->selecQuery = new SelectQuery($this->table, $columns);
        return $this;
    }

    function __call($name, $arguments)
    {
        $this->selecQuery->{$name}(...$arguments);
        return $this;
    }

    function toQuery(?Table $table = null): string
    {
        $selectClause = $this->selecQuery->toQuery($this->table);
        return "WITH {$this->defineName} AS ($selectClause)";
    }
}
