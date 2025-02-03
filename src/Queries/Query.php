<?php

namespace Il4mb\Db\Queries;

use Il4mb\Db\Columns\Column;
use Il4mb\Db\Columns\JsonArray;

final class Query
{
    // static function select(string $table, string|Column $column = "id", string $alias)
    // {
    //     return Column::from(
    //         (new SelectQuery($table, [$column]))->limit(1),
    //         $alias
    //     );
    // }

    static function jsonArray(array $columns, string $alias)
    {
        return new JsonArray($columns, $alias);
    }

    static function SUM(string $column)
    {
        return new SumQuery($column);
    }
}