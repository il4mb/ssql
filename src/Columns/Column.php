<?php

namespace Il4mb\SSQL\Columns;

use Il4mb\SSQL\Abstract\Queriable;
use Il4mb\SSQL\Cores\Table;

class Column implements Queriable
{
    protected mixed $prop;
    protected ?string $alias = null;

    public function __construct(mixed $prop, ?string $alias = null)
    {
        $this->prop = $prop;
        $this->alias = $alias;
    }

    public function toQuery(Table|null $table = null): string
    {
        $prefix = $table ? ($table->alias ?? $table->name) . "." : "";
        $clause = $this->prop;

        if (is_array($this->prop)) {
            $clause = implode(
                ", ",
                array_map(
                    fn($column) => $column instanceof Queriable
                        ? $column->toQuery($table) // No need to add prefix manually
                        : "{$prefix}{$column}", // Apply prefix only to string columns
                    $this->prop
                )
            );
        } elseif ($this->prop instanceof Queriable) {
            $clause = "({$this->prop->toQuery($table)})";
        } else {
            $clause = "{$prefix}{$this->prop}"; // Apply prefix only to normal strings
        }

        if (!empty($this->alias)) {
            $clause = "{$clause} AS {$this->alias}";
        }

        return preg_replace("/^\((.*?)\)$/", "$1", $clause);
    }


    public function __call(string $method, array $args): static
    {
        if (method_exists($this->prop, $method)) {
            $this->prop->$method(...$args);
        }
        return $this;
    }

    /**
     * Creates a new Column instance from a Queriable property.
     *
     * @param Queriable $prop The property to be wrapped as a Column.
     * @param string $alias The alias name associated with the column.
     * @return static The new Column instance.
     */
    public static function from(Queriable $prop, string $alias): static
    {
        return new static($prop, $alias);
    }

    /**
     * Creates an array of Column objects from an input array.
     *
     * @param array $props The array of column definitions.
     * @return Column[] The array of Column instances.
     */
    public static function fromArray(array $props): array
    {
        if (isset($props[0]) && is_array($props[0])) {
            $props = $props[0];
        }

        $columns = [];
        foreach ($props as $key => $value) {
            if (is_array($value)) {
                $columns = array_merge($columns, self::fromArray($value));
                continue;
            }
            $columns[] = is_string($key) ? new static($value, $key) : new static($value);
        }
        return $columns;
    }

    public static function arrayToQuery(array $columns): string
    {
        return implode(
            ", ",
            array_map(
                fn(Column $col) => $col->toQuery(),
                $columns
            )
        );
    }
}
