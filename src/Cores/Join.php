<?php

namespace Il4mb\SSQL\Cores;

use Il4mb\SSQL\Abstract\Queriable;
use Il4mb\SSQL\Columns\Column;

class Join implements Queriable
{
    protected Table $table;
    protected string $strategy;
    protected array $columns = [];
    protected array $whereQueries = [];
    protected array $onQueries = [];

    public function __construct(string|array $table, string $strategy = "LEFT")
    {
        if (is_array($table) && count($table) === 2) {
            [$table, $alias] = $table;
        }

        $this->table = new Table($table, $alias ?? null);
        $this->strategy = strtoupper($strategy); // Normalize case (LEFT, RIGHT, INNER, etc.)
    }

    public function select(array $columns): static
    {
        $this->columns = Column::fromArray($columns);
        return $this;
    }

    public function where(...$conditions): static
    {
        $this->whereQueries[] = new Conditions($conditions);
        return $this;
    }

    public function on(...$conditions): static
    {
        $this->onQueries[] = new Conditions($conditions);
        return $this;
    }

    private function formatConditions(array $conditions, string $separator = " AND ", Table|null $table = null): string
    {
        return implode($separator, array_map(fn($query) => $query->toQuery($table), $conditions));
    }

    public function toQuery(Table|null $table = null): string
    {
        $columnClause = $this->formatConditions($this->columns, ", ", $this->table);
        $whereClause = $this->formatConditions($this->whereQueries, " AND ", $this->table);
        $onClause = $this->formatConditions($this->onQueries, " AND ", $this->table);
        $query = "{$this->strategy} JOIN";

        $tableName = $this->table->name . (empty($this->table->alias) ? "" : " {$this->table->alias}");

        if (!empty($columnClause)) {
            $query .= " (SELECT {$columnClause} FROM {$tableName}";
            if (!empty($whereClause)) {
                $query .= " WHERE {$whereClause}";
            }
            $query .= ")";
        } elseif (!empty($whereClause)) {
            $query .= " (SELECT * FROM {$this->table->name} WHERE {$whereClause})";
        } else {
            $query .= " {$this->table->name}";
        }

        if (!empty($this->table->alias)) {
            $query .= " {$this->table->alias}";
        } else {
            $query .= " {$this->table->name}";
        }

        if (!empty($onClause)) {
            $query .= " ON {$onClause}";
        }

        return $query;
    }
}
