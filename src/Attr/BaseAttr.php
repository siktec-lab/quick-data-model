<?php

declare(strict_types=1);

namespace QDM\Attr;

abstract class BaseAttr
{
    /**
     * Normalize the types to use across the library
     * e.g. int -> integer, bool -> boolean, double -> float
     */
    final public static function typeName(string|array $type) : string|array
    {
        if (is_array($type)) {
            return array_map(fn($t) => self::typeName($t), $type);
        }
        switch ($type) {
            case "int":
                return "integer";
            case "bool":
                return "boolean";
            case "double":
                return "float";
            default:
                return $type;
        }
    }

    /**
     * Convert a string of types to an array of types
     * e.g. "int|string" -> [ "int", "string" ]
     *
     * @return array<string> The types array
     */
    final public static function typesArrayFromString(string $types) : array
    {
        return array_filter(
            explode("|", $types),
            fn($type) => !empty(trim($type))
        );
    }

    /**
     * Describe the attribute as a dictionary
     * @return array<string,array|string|null> self descrption dictionary
     */
    abstract public function describe() : array|string;
}
