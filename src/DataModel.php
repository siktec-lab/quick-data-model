<?php

declare(strict_types=1);

namespace QDM;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionIntersectionType;
use ReflectionProperty;
use QDM\Attr;
use QDM\Traits;
use QDM\Interfaces\IDataModel;

abstract class DataModel implements IDataModel
{
    use Traits\SafeJsonTrait;

    protected array $data_point_index = [];
    protected bool $is_initialized = false;
    private array $data_points = [];

    /**
     * Create a new data model
     * You can pass an array, object or json string to initialize the data model
     * This is the same as calling 'from' on the data model.
     * @throws \Exception if a data points are not public or protected
     */
    public function __construct(array|string|object $data = [])
    {

        $this->initialize(true);

        if (!empty($data)) {
            $this->from($data);
        }
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
        if (is_object($data) && $data instanceof IDataModel) {
            return $this->fromArray($data->toArray(), $errors);
        }
        if (is_object($data)) {
            return $this->fromArray((array)$data, $errors);
        }
        if (is_string($data)) {
            $data = $this->jsonDecodeCatch($data, assoc: true, errors : $errors);
            if (is_array($data)) {
                return $this->fromArray($data, $errors);
            }
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

        // Filter the data points to revert:
        $datapoints = array_filter($datapoints, fn($name) => $this->has($name));

        // Revert to the default value of the data points:
        $revert = count($datapoints) ? $datapoints : array_keys($this->data_points);
        foreach ($revert as $name) {
            $this->{$name} = $this->data_points[$name]->default;
        }
    }

    /**
     * Check if a data point exists
     */
    public function has(string $name, bool $export = false, bool $import = false) : bool
    {
        if (!array_key_exists($name, $this->data_points)) {
            return false;
        }
        if ($export && !$this->data_points[$name]->export) {
            return false;
        }
        if ($import && !$this->data_points[$name]->import) {
            return false;
        }
        return true;
    }

    /**
     * Get a data point value
     */
    public function get(string $name, bool $export = false) : mixed
    {
        if (($dp = $this->data_points[$name] ?? null) == null) {
            return null;
        }
        return !$export ? $this->{$name} : ($dp->export ? $this->{$name} : null);
    }

    /**
     * Set a data point
     * $errors will be filled with any errors that occurred during the initialization
     */
    public function set(mixed $value, string $name, array &$errors = [], bool $import = false) : bool
    {
        // If its not initialized then initialize it:
        if (!$this->is_initialized && !$this->initialize(false)) {
            $errors[] = "Data model could not be initialized declaration error";
            return false;
        }

        if (!$this->has($name)) {
            $errors[] = "Data point '{$name}' does not exist";
            return false;
        }

        $dp = $this->data_points[$name];

        // Should we import this data point:
        if ($import && !$dp->import) {
            return false; // We are not raising an error here because its not an error we just ignore it.
        }

        // Apply the custom filter if any:
        if (!is_null($dp->filter) && is_callable($dp->filter)) {
            $value = $dp->filter($value);
        }

        //If its a custom setter then use it:
        if (!is_null($dp->setter) && is_callable($dp->setter)) {
            $value = $dp->setter($value, $errors);
            if (!empty($errors)) {
                return false;
            }
            $this->{$name} = $value;
            return true;
        }

        // If its required and its null then we have an error:
        if ($dp->required && is_null($value)) {
            $errors[] = "Required data point '{$name}' cannot be null";
            return false;
        }

        // If its an array or object maybe its a nested data model:
        if ($dp->is_data_model) {
            // Only object array or string are allowed:
            if (!is_array($value) && !is_object($value) && !is_string($value)) {
                $errors[] = "Data point '{$name}' must be an array, object or string";
                return false;
            }

            // Build the data model:
            $dm = new $dp->types[0]();
            if (!$dm->from($value, $errors)) {
                return false;
            }
            $this->{$name} = $dm;
            return true;
        }

        // Type check:
        if (
            !in_array($my_type = self::typeName(gettype($value)), $dp->types) &&
            !(is_null($value) && $dp->nullable)
        ) {
            $errors[] = "Data point '{$name}' must be of type " .
                        implode("|", $dp->types) . " - Got " . $my_type;
            return false;
        }

        // Set the value:
        $this->{$name} = $value;
        return true;
    }

    /**
     * Convert the data model to an array
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        // If its not initialized then initialize it:
        if (!$this->is_initialized && !$this->initialize(false)) {
            return [];
        }

        // Build the array:
        $data = [];
        foreach ($this->data_points as $dp) {
            // We don't use get for performance reasons:
            // get will check if the data point exists and then get it
            // we already know it exists so we just get it
            if (!$dp->export) {
                continue;
            }

            // Distinction between data model and other types:
            if ($dp->is_data_model) {
                // We only export data if they are not null:
                $data[$dp->name] = $this->{$dp->name}?->toArray();
            } else {
                $data[$dp->name] = $this->{$dp->name};
            }
        }

        return $data;
    }

    /**
     * Convert the data model to a json string
     * pretty will make the json string with indentation
     * null will be returned if the data model is invalid
     */
    public function toJson(bool $pretty = false) : ?string
    {
        return $this->jsonEncodeCatch($this->toArray(), $pretty ? JSON_PRETTY_PRINT : 0);
    }

    /**
     * Initialize the data model
     * This will initialize the data points and set the default values
     * @throws \Exception if a data point is not public or protected unless throw is false
     */
    protected function initialize(bool $throw = true) : bool
    {
        try {
            $this->data_points    = $this->dataPoints();
            $this->is_initialized = true;
        } catch (\Exception $e) {
            if ($throw) {
                throw $e;
            }
            return false;
        }
        return true;
    }

    /**
     * Populate the data model from an array
     * $errors will be filled with any errors that occurred during the initialization
     * Will return true if no errors occurred
     */
    protected function fromArray(array $data, array &$errors = []) : bool
    {
        // If its not initialized then initialize it:
        if (!$this->is_initialized && !$this->initialize(false)) {
            $errors[] = "Data model could not be initialized declaration error";
            return false;
        }
        $init_errors = count($errors);
        $keys = array_keys($data);
        foreach ($this->data_points as $key => $dp) {
            if (!in_array($key, $keys)) {
                continue;
            }
            // We set import to true because we are importing from an array
            // and we want to respect the visibility of the data point
            $this->set($data[$key], $key, $errors, import: true);
        }

        return count($errors) == $init_errors;
    }

    /**
     * Get the data points of the data model
     * @return array<string, DataPoint>
     * @throws \Exception if a data point is not public or protected
     */
    private function dataPoints() : array
    {
        $data_points = [];
        $position = 0;
        $reflection = new ReflectionClass($this);
        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(Attr\DataPoint::class);
            if (count($attributes) > 0) {
                // Basic data point info:
                $dp = $attributes[0]->newInstance();
                $dp->name = $property->getName();
                $dp->default = self::detectDefault($property, $this);
                $dp->position = $position++;

                // Parse the types and set the nullable flag:
                self::parseTypes($property->getType(), $dp);

                // Determine visibility: First throw if any thing other than public or protected is used:
                if (!$property->isProtected() && !$property->isPublic()) {
                    throw new \Exception(
                        "Data point '{$dp->name}' cannot be anything other than 'public' or 'protected'"
                    );
                }
                $dp->visible = $property->isPublic();
                $dp->export = $dp->export ?? $dp->visible;
                $dp->import = $dp->import ?? true;

                // Save the instance of the data point:
                $data_points[$dp->name] = $dp;
            }
        }

        // A column of positions with names:
        $this->data_point_index = array_column($data_points, "name", "position");

        // var_dump($data_points);
        return $data_points;
    }

    /**
     * parse the types of a data point and set the nullable flag
     */
    private static function parseTypes(
        ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $type,
        Attr\DataPoint $point
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

    /**
     * Convert the data model to a string (json)
     */
    public function __toString() : string
    {
        return $this->toJson();
    }
}
