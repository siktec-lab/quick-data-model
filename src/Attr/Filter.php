<?php

declare(strict_types=1);

namespace QDM\Attr;

use Attribute;
use Throwable;
use QDM\Attr\ReferableDataModelAttr;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Filter extends ReferableDataModelAttr
{
    /**
     * Execute a filter
     *
     * @return array{bool,string} Exec success status and the value or error message
     */
    final public static function execFilter(mixed $callable, array $args, array|string $types) : array
    {
        $success = true;
        $return = null;

        try {
            $return = $callable(...$args);
            $type = self::typeName(gettype($return));
            if (
                !empty($types) && 
                !in_array($type, is_string($types) ? self::typesArrayFromString($types) : $types)
            ) {
                $return = "Invalid return type {$type}' from filter";
                $success = false;
            }
        } catch (Throwable $th) {
            $success = false;
            $return = "Filter failed with internal error";
            // Emit a warning this should not happen:
            trigger_error(
                sprintf(
                    "Filter failed with internal error '%s' in %s on line %d",
                    $th->getMessage(),
                    $th->getFile(),
                    $th->getLine()
                ),
                E_USER_WARNING
            );
        }
        return [$success, $return];
    }

    /**
     * Apply filters to a value
     *
     * Will mutate the given value and return true if all filters were applied successfully
     * Otherwise will return false and populate the given errors array
     *
     * @param mixed $value The value to apply filters to
     * @param array<\QDM\Attr\Filter> $filters The filters to apply
     * @param array<string> $errors The errors array to populate
     */
    final public static function applyFilters(
        mixed &$value,
        array $filters,
        array &$errors = []
    ) : bool {

        foreach ($filters as $filter) {
            $method = $filter->call;
            $args   = $filter->args;
            $types  = $filter->types;

            // TODO: what happens if the method is self :: ? check that ASAP
            // Check if filter is callable
            $call_filter = Filter::isCallable($method);

            if ($call_filter === false) {
                $name = is_array($method) ? implode("::", $method) : $method;
                $filter->qdmAppendError(
                    $filter->parent_data_point_name,
                    "Filter '{$name}' is not callable",
                    $errors
                );
                return false;
            }

            // Apply value to the args array
            $args = Filter::applyValueToArgs($value, $args);

            // Execute filter
            [$status, $after] = Filter::execFilter($method, $args, $types);

            // Check if filter was applied successfully
            if (!$status) {
                $filter->qdmAppendError(
                    $filter->parent_data_point_name,
                    $after,
                    $errors
                );
                return false;
            }

            // Update value
            $value = $after;
        }
        return true;
    }

    /**
     * Describe the filter
     *
     * return a string representation of the filter definition
     */
    final public function describe() : string
    {
        return $this->__toString();
    }

    /**
     * A filter definition
     *
     * @param string|array<string> $call The callable to be used as a filter
     * @param array<mixed> $args The extra arguments to be passed to the filter
     * @param int $value_pos the position of the value to be filtered in the args array
     * @param string|array<string> $types The expected return types of the filter
     * @param string|array<string> $ref A reference to a data point to inherit its filter
     */
    public function __construct(
        public string|array $call = "",
        public array $args = [],
        int $value_pos = 0,
        public string|array $types = "mixed",
        public string|array|null $ref = null
    ) {
        //TODO: we should do this only if its not a reference.
        $this->args = self::placeMarkerInArgs($value_pos, $this->args);
        $this->types = is_string($this->types) ? self::typesArrayFromString($this->types) : $this->types;
        // Mixed means any type so we set it to an empty array
        if (in_array("mixed", $this->types)) {
            $this->types = [];
        } else {
            // Normalize the types
            $this->types = array_map(
                fn($type) => DataPoint::typeName($type),
                $this->types
            );
        }
    }

    /**
     * Describes the filter
     */
    final public function __toString() : string
    {
        $call = is_array($this->call) ? implode("::", $this->call) : $this->call;
        $args = array_map(
            fn($arg) => self::argStringable($arg),
            $this->args
        );
        $types = implode("|", $this->types) ?: "mixed";
        //TODO: maybe its a reference to a data point
        return sprintf(
            "%s(%s) -> %s",
            $call,
            implode(",", $args),
            $types
        );
    }
}
