<?php

declare(strict_types=1);

namespace QDM\Attr;

use Attribute;
use ReflectionProperty;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionIntersectionType;
use QDM\Interfaces\IDataModel;

// An attribute class to mark if property is a data point its called DataPoint and it accepts a boolean flag
#[Attribute(Attribute::TARGET_PROPERTY)]
class DataPoint
{
    public int $position = 0;

    public string $name = "";

    public array $types = [];

    public mixed $default = null;

    public bool $nullable = false;

    public bool $is_data_model = false;

    public bool $visible = true;

    /**
     * parse the types of a data point and set the nullable flag
     */
    final public static function parseTypes(
        ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $type,
        DataPoint $point
    ) : void {
        // Null no type assigned:
        if (is_null($type)) {
            $point->types    = [ "mixed" ];
            $point->nullable = true;
            return;
        }
        // Is nullable:
        if ($type->allowsNull()) {
            $point->nullable = true;
        }
        // Named type:
        if ($type instanceof ReflectionNamedType) {
            $types[] = self::typeName($type->getName());
            $point->types = $types;
        } elseif ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $union_type) {
                $point->types[] = self::typeName($union_type->getName());
            }

        // Intersection type: not supported so we just save them as their string representation
        } elseif ($type instanceof ReflectionIntersectionType) {
            $intersected = [];
            foreach ($type->getTypes() as $intersection_type) {
                $intersected[] = (string)$intersection_type;
            }
            $point->types[] = implode("&", self::typeName($intersected));
        }

        // We only support one data model type not multiple:
        foreach ($point->types as $type) {
            if (is_a($type, IDataModel::class, true)) {
                $point->is_data_model = true;
                $point->types = [ $type ];
                break;
            }
        }
        // Just in case:
        if (count($point->types) == 0 || in_array("mixed", $point->types)) {
            $point->types = [ "mixed" ];
            $point->nullable = true;
        }
    }

    /**
     * detect the default value of a property
     * if the class is initialized it will use the default value of the property
     * otherwise it will use the default value of the ReflectionProperty
     */
    final public static function detectDefaultValue(ReflectionProperty $property, ?object $instance = null) : mixed
    {
        return $property->isInitialized($instance)
                ? $property->getValue($instance)
                : $property->getDefaultValue();
    }

    /**
     * Normalize the type name
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

    final public function isTypeAllowed(mixed &$value) : bool
    {
        if (is_null($value)) {
            return $this->nullable;
        }
        return $this->hasType(gettype($value));
    }

    final public function hasType(string $type) : bool
    {
        return in_array(self::typeName($type), $this->types);
    }

    public function __construct(
        public bool $required = false,
        public ?bool $export = null,
        public ?bool $import = null,
        public ?bool $extra = false // If true this will catch all extra data passed to the data model
    ) {
    }
}
