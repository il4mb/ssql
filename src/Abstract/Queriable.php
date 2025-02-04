<?php

namespace Il4mb\SSQL\Abstract;

use Il4mb\SSQL\Cores\Table;

interface Queriable
{
    function toQuery(Table|null $table = null): string;
}