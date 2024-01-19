<?php

declare(strict_types=1);

namespace QDM\Attr;

use Attribute;
use Exception;
use ReflectionProperty;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionIntersectionType;
use QDM\Interfaces\IDataModel;

// An attribute class to mark if property is a data point its called DataPoint and it accepts a boolean flag
#[Attribute(Attribute::TARGET_PROPERTY)]
class DataPoint extends BaseAttr
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
     * Check if the a given value is allowed for this DataPoint
     */
    final public function isTypeAllowed(mixed &$value) : bool
    {
        if (is_null($value)) {
            return $this->nullable;
        }
        return $this->hasType(gettype($value));
    }

    /**
     * Check if the a given type is allowed for this DataPoint
     */
    final public function hasType(string $type) : bool
    {
        return in_array(self::typeName($type), $this->types);
    }

    /**
     * Describe the data point and nested data model if any
     * @param array<string> $found_nested will be filled with any nested data models found
     * @return array<string,array|string|null> self descrption dictionary
     */
    final public function describe(array &$found_nested = []) : array
    {
        $flags = array_filter(
            [!$this->required ?: "required",
             !$this->export ?: "export",
             !$this->import ?: "import",
             !$this->extra ?: "extra"],
            fn($f) => is_string($f)
        );
        return [
            "name"   => $this->name,
            "types"  => implode("|", $this->types),
            "flags"  => implode(",", $flags),
            "nested" => $this->describeNested($found_nested)
        ];
    }

    /**
     * The type of the collection or mixed for any type
     * that implements the IDataModel interface
     *
     * @param bool $required If true this data point is required (cannot be null even if nullable)
     * @param bool|null $export If true this data point will be exported regardless of its access modifier
     * @param bool|null $import If false this data point will not be imported regardless of its access modifier
     * @param bool $extra If true this will catch all extra data passed to the data model when importing
     */
    public function __construct(
        public bool $required = false,
        public ?bool $export = null,
        public ?bool $import = null,
        public bool $extra = false // If true this will catch all extra data passed to the data model
    ) {
    }

    /**
     * Describe the nested data model if any
     * @param array<string> $found_nested used to detect circular references
     * @return array<mixed>|string|null self model description or error message
     */
    private function describeNested(array &$found_nested) : array|string|null
    {
        // Try to describe the nested data model:
        if ($this->is_data_model && is_a($this->types[0], IDataModel::class, true)) {
            // Make sure we don't have a circular reference:
            $check = is_array($this->types[0]) ? implode("::", $this->types[0]) : $this->types[0];
            if (in_array($check, $found_nested)) {
                return "Circular reference";
            }
            $found_nested[] = $check;
            // Try to describe the nested data model:
            try {
                $reflection_class = new \ReflectionClass($this->types[0]);
                $inst = $reflection_class->newInstanceWithoutConstructor();
                $init = $inst->initialize();
                if ($init) {
                    return $inst->describe($found_nested);
                }
            } catch (Exception $e) {
                ;
            }
            return "Cannot describe";
        }
        return null;
    }
}
