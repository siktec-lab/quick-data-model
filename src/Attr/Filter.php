<?php

declare(strict_types=1);

namespace QDM\Attr;

use Attribute;
use Throwable;
use QDM\Attr\DataPoint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Filter
{
    public const VALUE_MARKER = "!VALUE!";

    /**
     * Check if a callable is valid and return the full callable
     *
     * @param string|array<string> $callable
     */
    final public static function isCallable(array|string $callable) : string|bool
    {
        $call_filter = $callable;
        return is_callable($callable, false, $call_filter) ? $call_filter : false;
    }

    /**
     * Replaces the special value marker with the given value inside the args array
     *
     * @return array<mixed>
     */
    final public static function applyValueToArgs(mixed $value, array $args) : array
    {
        return array_map(
            fn($arg) => $arg === self::VALUE_MARKER ? $value : $arg,
            $args
        );
    }

    /**
     * Execute a filter
     *
     * @return array{bool,string} Exec success status and the value or error message
     */
    final public static function execFilter(mixed $callable, array $args, array|string $types) : array
    {
        try {
            $value = call_user_func_array($callable, $args);
            $type = DataPoint::typeName(gettype($value));
            $valid = empty($types) || in_array(
                $type,
                is_string($types) ? self::typesFromString($types) : $types
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
     * Convert a string of types to an array of types
     * e.g. "int|string" -> [ "int", "string" ]
     *
     * @return array<string>
     */
    final public static function typesFromString(string $types) : array
    {
        return array_filter(
            explode("|", $types),
            fn($type) => !empty(trim($type))
        );
    }

    /**
     * A filter definition
     *
     * @param string|array<string> $call The callable to be used as a filter
     * @param int $value_pos The position of the value marker in the args array
     * @param array<mixed> $args The arguments to be passed to the filter
     * @param string|array<string> $types The types of the return value to be accepted
     */
    public function __construct(
        public string|array $call,
        int $value_pos = 0,
        public array $args = [],
        public string|array $types = "mixed"
    ) {

        $this->args = self::placeMarkerInArgs($value_pos, $this->args);
        $this->types = is_string($this->types) ? self::typesFromString($this->types) : $this->types;
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
     * Place a special value marker in the args array
     *
     * @return array<mixed>
     */
    private static function placeMarkerInArgs($position, $args) : array
    {
        // Ensure the position is within the valid range
        $position = max(min($position, count($args)), -count($args) - 1);
        // Insert the marker at the specified position
        array_splice($args, $position, 0, [self::VALUE_MARKER]);
        return $args;
    }
}
