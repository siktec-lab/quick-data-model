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
     * @param int $value_pos the position of the value to be filtered in the args array
     * @param array<mixed> $args The extra arguments to be passed to the filter
     * @param string|array<string> $types The expected return types of the filter
     */
    public function __construct(
        public string|array $call = "",
        int $value_pos = 0,
        public array $args = [],
        public string|array $types = "mixed",
        public string|array|null $ref = null
    ) {

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
            function ($arg) {
                if ($arg === self::VALUE_MARKER) {
                    return "#V";
                }
                if (is_numeric($arg) || (is_string($arg) && strlen($arg) <= 15 && !empty(trim($arg)))) {
                    return is_string($arg)
                            ? str_replace(
                                [ "\n", "\r", "\t", "\v", "\f" ],
                                [ '\n', '\r', '\t', '\v', '\f' ],
                                $arg
                            )
                            : $arg;
                }
                if (is_bool($arg)) {
                    return $arg ? "true" : "false";
                }
                return gettype($arg);
            },
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
