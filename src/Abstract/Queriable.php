<?php

namespace Il4mb\Db\Abstract;

use Il4mb\Db\Table;

interface Queriable
{
    function toQuery(Table|null $table = null): string;
}