<?php

namespace Il4mb\Db\Queries;

use Doctrine\SqlFormatter\SqlFormatter as SqlFormatterSqlFormatter;
use Il4mb\Db\Abstract\Queriable;
use Il4mb\Db\Columns\Column;
use Il4mb\Db\Join;
use Il4mb\Db\Queries\WhereQuery;
use Il4mb\Db\Table;
use ReflectionClass;

class SelectQuery implements Queriable
{

    /**
     * @var Table $table
     */
    protected Table $table;

    /**
     * @var array<Column> $columns
     */
    protected array $columns = [];

    protected array $bindCollections = [];

    /**
     * Constructor.
     * @param string $table The table to select from.
     * @param array<string|Column> $columns The properties to select (default is ["*"]).
     * @throws \InvalidArgumentException If columns contain invalid values.
     */
    public function __construct(Table $table, array $columns = ["*"])
    {
        $this->table = $table;
        $this->setProps($columns);
    }

    /**
     * Set the select properties.
     * @param array<string|Column> $columns The properties to select.
     * @throws \InvalidArgumentException If columns contain invalid values.
     */
    protected function setProps(array $columns): void
    {
        $this->columns = [];

        foreach ($columns as $key => $value) {
            $prop = is_numeric($key)
                ? new Column($value)
                : new Column($value, $key);
            $this->columns[] = $prop;
        }
    }

    private array $joins = [];
    function join(string|Join $table): static
    {
        $join = is_string($table) ? new Join($table) : $table;
        $reflector = new ReflectionClass($join);
        if ($reflector->hasProperty("table")) {
            $tableProperty = $reflector->getProperty("table");
            $tableProperty->setAccessible(true);
            $table = $tableProperty->getValue($join);
            $tableReflector = new ReflectionClass($table);
            if ($tableReflector->hasProperty("parent")) {
                $parentProperty = $tableReflector->getProperty("parent");
                $parentProperty->setAccessible(true);
                $parentProperty->setValue($table, $this->table);
                $parentProperty->setAccessible(false);
            }
        }
        $this->joins[] = $join;
        return $this;
    }
    

    protected array $whereQueries = [];
    /**
     * Add a WHERE clause to the query.
     *
     * @param array $conditions The conditions for the WHERE clause.
     * @return SelectQuery The updated SelectQuery instance.
     */
    public function where(...$conditions): static
    {
        $this->whereQueries[] = new WhereQuery($conditions);
        return $this;
    }


    protected array $groupQueries = [];
    function group(...$conditions): SelectQuery
    {

        $this->groupQueries = array_merge(
            $this->groupQueries,
            $conditions
        );
        return $this;
    }


    protected ?int $limit = null;
    function limit($size): static
    {
        $this->limit = $size;
        return $this;
    }

    /**
     * Build the SELECT query.
     * @return string The generated SQL query.
     */
    public function toQuery(Table|null $table = null): string
    {
        $selectClause = implode(
            ', ',
            array_map(
                fn(Column $prop): string => $prop->toQuery($table),
                $this->columns
            )
        );

        $joinClause = implode(
            ' ',
            array_map(
                fn(Join $join): string => $join->toQuery($table),
                $this->joins
            )
        );
        if (!empty($joinClause)) {
            $joinClause = " {$joinClause}";
        }
        $whereClause = implode(
            " AND ",
            array_map(
                fn(WhereQuery $query): string => $query->toQuery($table),
                $this->whereQueries
            )
        );
        $groupClause = implode(
            ", ",
            $this->groupQueries
        );
        if (!empty($groupClause)) {
            $groupClause = " GROUP BY {$groupClause}";
        }
        $limitClause = $this->limit ? " LIMIT {$this->limit}" : "";
        $tableName = "{$this->table->name}" . (isset($this->table->alias) ? " {$this->table->alias}" : "");
        return "SELECT {$selectClause} FROM {$tableName}{$joinClause}{$whereClause}{$groupClause}{$limitClause}";
    }

    function all()
    {
        echo ((new SqlFormatterSqlFormatter(null))->format($this->toQuery($this->table)));
    }
}
