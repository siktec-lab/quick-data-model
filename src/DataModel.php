<?php

declare(strict_types=1);

namespace QDM;

use ReflectionClass;
use QDM\Attr\DataPoint;
use QDM\Traits;
use QDM\Interfaces\IDataModel;

abstract class DataModel implements IDataModel
{
    use Traits\SafeJsonTrait;
    use Traits\DataPointsTrait;
    use Traits\FiltersTrait;

    protected bool $is_initialized = false;

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
        $revert = count($datapoints) ? $datapoints : $this->getDataPointsNames();
        foreach ($revert as $name) {
            $this->{$name} = $this->getDataPoint($name)?->default;
        }
    }

    /**
     * Check if a data point exists
     */
    public function has(string $name, bool $export = false, bool $import = false) : bool
    {
        $dp = $this->getDataPoint($name);
        if (is_null($dp)) {
            return false;
        }
        if ($export && !$dp->export) {
            return false;
        }
        if ($import && !$dp->import) {
            return false;
        }
        return true;
    }

    /**
     * Get a data point value
     */
    public function get(string $name, bool $export = false) : mixed
    {
        $dp = $this->getDataPoint($name);
        if (is_null($dp)) {
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

        $dp = $this->getDataPoint($name);
        if (is_null($dp)) {
            $errors[] = "Data point '{$name}' does not exist";
            return false;
        }

        // Should we import this data point:
        if ($import && !$dp->import) {
            return false; // We are not raising an error here because its not an error we just ignore it.
        }

        // Apply the custom filter if any:
        if (
            $this->hasFilters($dp->name) &&
            !self::applyFilters($value, $this->getFilters($dp->name), $errors)
        ) {
            return false;
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
            !in_array($my_type = DataPoint::typeName(gettype($value)), $dp->types) &&
            !(is_null($value) && $dp->nullable)
        ) {
            $errors[] =  sprintf(
                "DataPoint '%s' must be of type '%s' -> Got '%s'",
                $name,
                implode("|", $dp->types),
                $my_type
            );
            return false;
        }

        // Set the value:
        $this->{$name} = $value;
        return true;
    }

    /**
     * Convert the data model to an array
     * @return array<string,mixed>
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
            $this->parseAttributes();
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
        foreach ($this->getDataPointsNames() as $dp_name) {
            if (!in_array($dp_name, $keys)) {
                continue;
            }
            // We set import to true because we are importing from an array
            // and we want to respect the visibility of the data point
            $this->set($data[$dp_name], $dp_name, $errors, import: true);
        }

        return count($errors) == $init_errors;
    }

    /**
     * Get the data points of the data model
     *
     * @throws \Exception if a data point is not public or protected
     */
    private function parseAttributes() : void
    {
        $property_position = 0;
        $reflection_class  = new ReflectionClass($this);
        foreach ($reflection_class->getProperties() as $property) {
            // Data point:
            if ($this->buildDataPoint($property, $property_position)) {
                // Parse the filters for the data point:
                $this->buildFilters($property, $this->getDataPoint($property->getName()));
                // Next DataPoint:
                $property_position++;
            }
        }
        // Build the data point index:
        $this->buildDataPointIndex();
    }

    /**
     * Convert the data model to a string (json)
     */
    public function __toString() : string
    {
        return $this->toJson();
    }
}
