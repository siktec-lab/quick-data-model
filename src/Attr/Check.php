<?php

declare(strict_types=1);

namespace QDM\Attr;

use Attribute;
use Throwable;
use QDM\Attr\ReferableDataModelAttr;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Check extends ReferableDataModelAttr
{
    /**
     * Execute a check
     *
     * @return array{bool,string} Exec success status and the value or error message
     */
    final public static function execCheck(mixed $callable, array $args, array|string $types) : array
    {
        try {
            $value = call_user_func_array($callable, $args);
            $type = self::typeName(gettype($value));
            $valid = empty($types) || in_array(
                $type,
                is_string($types) ? self::typesArrayFromString($types) : $types
            );
            return [
                $valid,
                $valid ? $value : "Invalid return type {$type}' from filter"
            ];
        } catch (Throwable $th) {
            return [false, "Filter failed with error '{$th->getMessage()}'"];
        }
    }

    /**
     * Apply checks to a value
     *
     * Will run the given value through the given checks and return true if all checks were applied successfully
     * Otherwise will return false and populate the given errors array
     *
     * @param mixed $value The value to apply checks to
     * @param array<\QDM\Attr\Check> $checks The checks to apply
     * @param array<string> $errors The errors array to populate
     */
    final protected static function applyChecks(
        mixed &$value,
        array $checks,
        array &$errors = []
    ) : bool {

        // foreach ($filters as $filter) {
        //     $method = $filter->call;
        //     $args   = $filter->args;
        //     $types  = $filter->types;

        //     // Check if filter is callable
        //     $call_filter = Filter::isCallable($method);

        //     if ($call_filter === false) {
        //         $name = is_array($method) ? implode("::", $method) : $method;
        //         $errors[] = "Filter '{$name}' is not callable";
        //         return false;
        //     }

        //     // Apply value to the args array
        //     $args = Filter::applyValueToArgs($value, $args);

        //     // Execute filter
        //     [$status, $after] = Filter::execFilter($method, $args, $types);

        //     // Check if filter was applied successfully
        //     if (!$status) {
        //         $errors[] = $after;
        //         return false;
        //     }

        //     // Update value
        //     $value = $after;
        // }
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
     * @param string|array<string>|callable $call The callable to be used as this check
     * @param array{string,mixed} $against the comparison operator and the value to be compared against
     * @param array<mixed> $args The extra arguments to be passed to the check callable
     * @param int $value_pos the position of the value to be checked in the args array defaults to 0
     * @param string|array<string> $ref A reference to a data point to inherit its check callable
     */
    public function __construct(
        public string|array|callable $call = "",
        public array $against = [],
        public array $args = [],
        int $value_pos = 0,
        public string|array|null $ref = null
    ) {
        // Place the value marker in the args array
        $this->args = self::placeMarkerInArgs($value_pos, $this->args);
    }

    /**
     * Describes the Check definition
     */
    final public function __toString() : string
    {
        $call = is_array($this->call) ? implode("::", $this->call) : $this->call;
        $args = array_map(
            fn($arg) => self::argStringable($arg),
            $this->args
        );
        $agains = match (true) {
            is_array($this->against) && count($this->against) === 2 => 
                self::argStringable($this->against[0]) . " " . self::argStringable($this->against[1]),
            default => "true"
        };
        //TODO: maybe its a reference to a data point
        return sprintf(
            "%s(%s) is %s",
            $call,
            implode(",", $args),
            $agains
        );
    }
}
