<?php

namespace Il4mb\SSQL;

use Il4mb\SSQL\Columns\JsonArray;
use Il4mb\SSQL\Queries\SumQuery;

final class Query
{
    static function jsonArray(array $columns, string $alias)
    {
        return new JsonArray($columns, $alias);
    }

    static function SUM(string $column, ?string $alias = null)
    {
        return new SumQuery($column, $alias ?? $column);
    }
}
