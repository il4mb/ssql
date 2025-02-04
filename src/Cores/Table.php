<?php

namespace Il4mb\SSQL\Cores;

use Il4mb\SSQL\Queries\SelectQuery;

class Table
{
    public readonly string $name;
    public readonly ?string $alias;
    protected array $collector = [];
    protected Table|null $parent = null;

    function __construct(string $name, string $alias = null)
    {
        $this->name = $name;
        $this->alias = $alias;
    }

    function addBind($value): string
    {
        if ($this->parent) {
            return $this->parent->addBind($value);
        }
        $index = "v" . count($this->collector);
        $this->collector[$index] = $value;
        return ":{$index}";
    }

    function select(...$columns)
    {
        $columns = empty($columns) ? ["*"] : $columns;
        return new SelectQuery($this, $columns);
    }

    function insert(...$columns) {}

    function update(...$columns) {}

    function delete(...$columns) {}


    static function leftJoin($name, $alias = null)
    {
        return new Join([$name, $alias], "LEFT");
    }

    static function rightJoin($name, $alias = null)
    {
        return new Join([$name, $alias], "RIGHT");
    }

    static function innerJoin($name, $alias = null)
    {
        return new Join([$name, $alias], "INNER");
    }

    static function fullJoin($name, $alias = null)
    {
        return new Join([$name, $alias], "FULL");
    }
}
