<?php

namespace Il4mb\Db\Columns;
use Il4mb\Db\Table;

class JsonArray extends Column
{
    function __construct(array $props, string $alias)
    {
        $this->prop = $props;
        $this->alias = $alias;
        if (empty($this->alias)) {
            throw new \InvalidArgumentException("Alias cannot be empty");
        }
    }

    public function toQuery(Table|null $table = null): string
    {
        $keys = array_keys($this->prop);
        $propClause = implode(
            separator: ", ",
            array: array_map(
                callback: fn($key) => "'{$key}', {$this->prop[$key]}",
                array: $keys
            )
        );
        return "JSON_ARRAYAGG(JSON_OBJECT({$propClause})) AS {$this->alias}";
    }
}
