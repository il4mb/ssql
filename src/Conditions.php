<?php

namespace Il4mb\Db;

use Il4mb\Db\Abstract\Queriable;
use InvalidArgumentException;

class Conditions implements Queriable
{
    /**
     * The conditions to build.
     *
     * @var array
     */
    protected array $conditions;

    /**
     * Constructor.
     *
     * @param array $conditions The conditions to build.
     * @param array|null $collector Optional reference to an array for collecting bind values.
     */
    public function __construct(array $conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * Build the conditions string.
     *
     * @param array $conditions The conditions to build.
     * @param string $operand The operand to use between conditions (default is "AND").
     * @return string The formatted conditions string.
     * @throws InvalidArgumentException If the conditions are invalid.
     */
    private function buildConditions(?Table $table, array $conditions, string $operand = "AND"): string
    {
        $prefix = $table ? ($table->alias ?? $table->name) . "." : "";

        // Validate operand
        if (!in_array(strtoupper($operand), ["AND", "OR"])) {
            throw new InvalidArgumentException("Operand must be 'AND' or 'OR'.");
        }

        // Handle single condition
        if (!$this->hasNestedArray($conditions) && (count($conditions) >= 2 && count($conditions) <= 3)) {
            if (empty($conditions[0]) || empty($conditions[1])) {
                throw new InvalidArgumentException("Condition cannot be empty.");
            }

            // Normalize condition to [column, operator, value] format
            if (count($conditions) == 2) {
                $conditions = [$conditions[0], "=", $conditions[1]];
            }

            $prop = $conditions[0];
            $operator = $conditions[1];
            $value = $conditions[2];
            if (!preg_match("/^\w+\./", $prop)) {
                $prop = $prefix . $prop;
            }

            // Add bind value if a bind collector is provided
            if ($table && ($index = $table->addBind($conditions[2]))) {
                return "{$prop} {$operator} {$index}";
            }
            return "{$prop} {$operator} {$conditions[2]}";
        }

        // Handle multiple conditions
        if ($this->hasNestedArray($conditions)) {
            $conditionsClauseGroup = [];
            $previousWasCondition = false;

            foreach ($conditions as $value) {
                if (is_array($value)) {
                    // Recursively build sub-conditions
                    $subClause = $this->buildConditions($table, $value, $operand);
                    if (!empty($subClause)) {
                        // Add operand if the previous item was a condition
                        if ($previousWasCondition) {
                            $conditionsClauseGroup[] = $operand;
                        }
                        $conditionsClauseGroup[] = $subClause;
                        $previousWasCondition = true;
                    }
                } elseif (is_string($value) && in_array(strtoupper($value), ["AND", "OR"])) {
                    // If the value is an operand (AND/OR), add it to the group
                    $conditionsClauseGroup[] = strtoupper($value);
                    $previousWasCondition = false;
                } elseif (is_string($value)) {
                    // If the value is a string (not an operand), treat it as a condition
                    if ($previousWasCondition) {
                        $conditionsClauseGroup[] = $operand;
                    }
                    $conditionsClauseGroup[] = $value;
                    $previousWasCondition = true;
                } else {
                    throw new InvalidArgumentException("Invalid condition format. Conditions must be arrays or strings.");
                }
            }

            // Combine the conditions into a group
            if (!empty($conditionsClauseGroup)) {
                return "(" . implode(" ", $conditionsClauseGroup) . ")";
            }
        } else {
            throw new InvalidArgumentException("Condition format is invalid. Conditions should be an array with 2 or 3 elements, or a nested array.");
        }

        return "";
    }

    /**
     * Check if the array contains nested arrays.
     *
     * @param array $array The array to check.
     * @return bool True if the array contains nested arrays, false otherwise.
     */
    private function hasNestedArray(array $array): bool
    {
        foreach ($array as $value) {
            if (is_array($value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Build the final conditions string.
     *
     * @return string The formatted conditions string.
     */
    public function toQuery(Table|null $table = null): string
    {
        $whereClause = $this->buildConditions($table, $this->conditions);
        return preg_replace("/^\(|\)$/", "", $whereClause);
    }
}
