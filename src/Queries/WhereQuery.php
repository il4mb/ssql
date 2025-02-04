<?php

namespace Il4mb\SSQL\Queries;

use Il4mb\SSQL\Abstract\Queriable;
use Il4mb\SSQL\Cores\Conditions;
use Il4mb\SSQL\Cores\Table;

class WhereQuery implements Queriable
{
    protected array $conditions;

    function __construct( array $conditions)
    {
        $this->conditions = $conditions;
    }

    function toQuery(Table|null $table = null): string
    {
        $whereBuilder = new Conditions($this->conditions);
        $whereClause = $whereBuilder->toQuery($table);
        if (!empty($whereClause)) {
            $whereClause = " WHERE {$whereClause}";
        }

        return $whereClause;
    }
}
