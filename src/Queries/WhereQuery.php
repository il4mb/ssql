<?php

namespace Il4mb\Db\Queries;

use Il4mb\Db\Abstract\Queriable;
use Il4mb\Db\Conditions;
use Il4mb\Db\Table;

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
