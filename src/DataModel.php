<?php

declare(strict_types=1);

namespace QDM;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionIntersectionType;
use ReflectionProperty;

abstract class DataModel
{
    private bool $is_initialized = false;
    private array $data_points = [];

    /**
     * DataModel constructor is used to initialize the data points as the current defaults
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Populate the data model from an object, array or json string
     * each call to from will revert the data model to its default values
     * $errors will be filled with any errors that occurred during the initialization
     * Will return true if no errors occurred
     */
    public function from(object|array|string $data, array &$errors = []) : bool
    {

        // Reset the data model:
        $this->revert();

        // Now extend the data model:
        return $this->extend($data, $errors);
    }

    /**
     * Extend the data model from an object, array or json string
     * $errors will be filled with any errors that occurred during the initialization
     * Will return true if no errors occurred
     */
    public function extend(object|array|string $data, array &$errors = []) : bool
    {
        if (is_array($data)) {
            return $this->fromArray($data, $errors);
        }
        if (is_object($data) && $data instanceof DataModel) {
            return $this->fromArray($data->toArray(), $errors);
        }
        if (is_object($data)) {
            return $this->fromArray((array)$data, $errors);
        }
        if (is_string($data)) {
            return $this->fromArray(json_decode($data, true), $errors);
        }
        $errors[] = "Data is not a valid type that can be converted to a data model";

        return false;
    }

    /**
     * Revert the data model to its default values from latest initialization
     * if no data points are passed it will revert all data points
     */
    public function revert(...$datapoints) : void
    {
        // Nothing to revert to...
        if (!$this->is_initialized) {
            return;
        }

        // Revert to the default value of the data points:
        $revert = count($datapoints) ? $datapoints : array_keys($this->data_points);
        foreach ($revert as $name) {
            $this->{$name} = $this->data_points[$name]->default;
        }
    }

    /**
     * Convert the data model to an array
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        // If its not initialized then initialize it:
        if (!$this->is_initialized) {
            $this->initialize();
        }

        // Build the array:
        $data = [];
        foreach ($this->data_points as $dp) {
            $data[$dp->name] = $this->{$dp->name};
        }

        return $data;
    }

    /**
     * Convert the data model to a json string
     * pretty will make the json string with indentation
     */
    public function toJson(bool $pretty = false) : string
    {
        return json_encode($this->toArray(), $pretty ? JSON_PRETTY_PRINT : 0);
    }

    /**
     * Initialize the data model
     * This will initialize the data points and set the default values
     */
    protected function initialize() : void
    {
        $this->data_points    = $this->dataPoints();
        $this->is_initialized = true;
    }

    /**
     * Populate the data model from an array
     * $errors will be filled with any errors that occurred during the initialization
     * Will return true if no errors occurred
     */
    protected function fromArray(array $data, array &$errors = []) : bool
    {
        // If its not initialized then initialize it:
        if (!$this->is_initialized) {
            $this->initialize();
        }

        $init_errors = count($errors);
        $keys = array_keys($data);
        foreach ($this->data_points as $key => $dp) {
            // Skip if not in the update list:
            if (!in_array($key, $keys)) {
                continue;
            }
            $value = $data[$key];

            // Apply the custom filter if any:
            if (!is_null($dp->filter) && is_callable($dp->filter)) {
                $value = $dp->filter($value);
            }

            //If its a custom setter then use it:
            if (!is_null($dp->setter) && is_callable($dp->setter)) {
                $this->{$key} = $dp->setter($value);
                continue;
            }

            // If its required and its null then we have an error:
            if ($dp->required && is_null($value)) {
                $errors[] = "Required data point '{$key}' cannot be null";
                continue;
            }

            // If its an array or object maybe its a nested data model:
            if ($dp->is_data_model) {
                // Only object array or string are allowed:
                if (!is_array($value) && !is_object($value) && !is_string($value)) {
                    $errors[] = "Data point '{$key}' must be an array, object or string";
                    continue;
                }
                // Build the data model:
                $dm = new $dp->types[0]();
                if ($dm->from($value, $errors)) {
                    $this->{$key} = $dm;
                }
                continue;
            }
            // Type check:
            if (!in_array(self::typeName(gettype($value)), $dp->types) && !(is_null($value) && $dp->nullable)) {
                $errors[] = "Data point '{$key}' must be of type " . implode("|", $dp->types);
                continue;
            }
            // Set the value:
            $this->{$key} = $value;
        }

        return count($errors) == $init_errors;
    }

    /**
     * Get the data points of the data model
     * @return array<string, DataPoint>
     */
    private function dataPoints() : array
    {
        $data_points = [];
        $reflection = new ReflectionClass($this);
        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(DataPoint::class);
            if (count($attributes) > 0) {
                $dp = $attributes[0]->newInstance();
                $dp->name = $property->getName();
                $dp->default = self::detectDefault($property, $this);
                self::parseTypes($property->getType(), $dp);
                $data_points[$dp->name] = $dp;
            }
        }
        // var_dump($data_points);
        return $data_points;
    }

    /**
     * parse the types of a data point and set the nullable flag
     */
    private static function parseTypes(
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
            return;
        }
        // Union type:
        if ($type instanceof ReflectionUnionType) {
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
        foreach ($point->types as &$type) {
            if (is_subclass_of($type, DataModel::class)) {
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
    private static function detectDefault(ReflectionProperty $property, ?object $instance = null) : mixed
    {
        return $property->isInitialized($instance)
                ? $property->getValue($instance)
                : $property->getDefaultValue();
    }

    /**
     * Normalize the type name
     * e.g. int -> integer, bool -> boolean, double -> float
     */
    private static function typeName(string|array $type) : string|array
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
}
