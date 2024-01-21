<?php

declare(strict_types=1);

namespace QDM;

use ReflectionClass;
use Exception;
use QDM\Attr;
use QDM\Traits;
use QDM\Interfaces\IDataModel;

abstract class DataModel implements IDataModel
{
    use Traits\SafeJsonTrait;
    use Traits\AppendErrorTrait;
    use Traits\AutoInitializeTrait;
    use Traits\DataPointsTrait;
    use Traits\FiltersTrait;
    use Traits\ChecksTrait;

    protected bool $is_initialized = false;

    /**
     * Populate the data model from an object, array or json string
     * each call to from will revert the data model to its default values
     * $errors will be filled with any errors that occurred during the initialization
     * Will return true if no errors occurred
     */
    final public function from(object|array|string $data, array &$errors = []) : bool
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
    final public function extend(object|array|string $data, array &$errors = []) : bool
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
            $data = $this->jsonDecodeCatch($data, assoc: true);
            if (is_array($data)) {
                return $this->fromArray($data, $errors);
            }
        }

        // If we reached here then we have an error:
        $this->qdmAppendError(
            message: "Received data is invalid, must be an Array, IDataModel object or JSON string",
            to: $errors
        );
        return false;
    }

    /**
     * Revert the data model to its default values from latest initialization
     * if no data points are passed it will revert all data points
     */
    final public function revert(...$datapoints) : void
    {
        // Nothing to revert to...
        if (!$this->is_initialized) {
            return;
        }

        // Filter the data points to revert:
        $datapoints = array_filter($datapoints, fn($name) => $this->has($name));

        // Revert to the default value of the data points:
        $revert = count($datapoints) ? $datapoints : $this->qdmGetDataPointsNames();
        foreach ($revert as $name) {
            $this->{$name} = $this->qdmGetDataPoint($name)?->default;
        }
    }

    /**
     * Check if a data point exists
     */
    final public function has(string $name, bool $export = false, bool $import = false) : bool
    {
        $dp = $this->qdmGetDataPoint($name);
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
    final public function get(string $name, bool $export = false) : mixed
    {
        $dp = $this->qdmGetDataPoint($name);
        if (is_null($dp)) {
            return null;
        }
        return !$export ? $this->{$name} : ($dp->export ? $this->{$name} : null);
    }

    /**
     * Validate the data model "manually"
     * Will perform:
     *  - Required checks
     *  - Custom checks that are defined in the data points
     * 
     * Will not perform:
     *  - Type checks they always performed when setting a value
    */
    final public function validate(array &$errors = []) : bool
    {
        // Auto initialize the data model:
        if (!$this->qdmAutoInitialize($errors, throw: false)) {
            return false;
        }

        // Loop through the data points:
        $valid = true;
        foreach ($this->qdm_data_points as $dp) {

            // If its required and its null then we have an error:
            if (!$this->qdmValidateRequiredDataPoint($dp, $this->{$dp->name})) {
                $this->qdmAppendError(of: $dp->name, message: "Required DataPoint cannot be null", to: $errors);
                $valid = false;
            }

            // Its a nested data model:
            if ($dp->is_data_model) {
                // We only validate if the data model is not null:
                if (!is_null($this->{$dp->name})) {
                    // Validate the nested data model:
                    $nested_errors = []; // Report back the errors
                    if (!$this->{$dp->name}->validate($nested_errors)) {
                        $this->qdmAppendError(
                            of : $dp->name,
                            message : $nested_errors,
                            to :$errors
                        );
                        $valid = false;
                    }
                }
                continue;
            }

            // Apply the custom check if any:
            if (
                $this->qdmHasChecks($dp->name) &&
                !Attr\Check::applyChecks($this->{$dp->name}, $this->qdmGetChecks($dp->name), $errors)
            ) {
                $valid = false;
            }
        }
        return $valid;
    }

    /**
     * Convert the data model to an array
     * @return array<string,mixed>
     */
    final public function toArray() : array
    {

        if (!$this->qdmAutoInitialize(throw: false)) {
            return [];
        }

        // Build the array:
        $data = [];
        foreach ($this->qdm_data_points as $dp) {
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

        // Add extra data only if its set to export:
        if ($this->qdm_extra_data && $this->qdm_extra_data->export) {
            $data[$this->qdm_extra_data->name] = $this->{$this->qdm_extra_data->name};
        }

        return $data;
    }

    /**
     * Convert the data model to a json string
     * pretty will make the json string with indentation
     * null will be returned if the data model is invalid
     */
    final public function toJson(bool $pretty = false) : ?string
    {
        return $this->jsonEncodeCatch($this->toArray(), $pretty ? JSON_PRETTY_PRINT : 0);
    }

    /**
     * Initialize the data model
     * This will initialize the data points and set the default values
     * @throws DataModelException if the a DataPoint declaration is invalid
     */
    final public function initialize(bool $throw = true) : bool
    {
        try {
            $this->qdmParseAttributes();
            $this->is_initialized = true;
        } catch (DataModelException $e) {
            if ($throw) {
                throw $e;
            }
            return false;
        } catch (Exception $e) {
            if ($throw) {
                throw new DataModelException(DataModelException::CODE_UNKNOWN_ERROR, [$e->getMessage()], $e);
            }
            return false;
        }
        return true;
    }

    /**
     * Describe will return an array with the data model composition
     *
     * @return array<string,array|string|null> self descrption dictionary
     */
    final public function describe(array &$found_nested = []) : array
    {
        // Check if initialized otherwise initialize it:
        if (!$this->qdmAutoInitialize(throw: false)) {
            return [];
        }

        $struct = [];
        foreach ($this->qdm_data_points as $dp) {
            $filters = array_map(fn($f) => $f->describe(), $this->qdmGetFilters($dp->name));
            $checks = array_map(fn($c) => $c->describe(), $this->qdmGetChecks($dp->name));
            $struct[$dp->name] = array_merge(
                $dp->describe($found_nested),
                [
                    // TODO: Add setter and getter checks...
                    // TODO: Add model scope applied actions
                    "filters"   => $filters ?: null,
                    "checks"    => $checks ?: null,
                    "setter"    => "TODO",
                    "getter"    => "TODO",
                ]
            );
        }
        return $struct;
    }

    /**
     * Populate the data model from an array
     * $errors will be filled with any errors that occurred during the initialization
     * Will return true if no errors occurred
     */
    final protected function fromArray(array $data, array &$errors = []) : bool
    {
        // Auto initialize the data model:
        if (!$this->qdmAutoInitialize($errors, throw: false)) {
            return false;
        }

        $dps = $this->qdmGetDataPointsNames();
        $valid = true;
        // Loop through the data:
        foreach ($data as $key => $value) {
            if (!in_array($key, $dps)) {
                // We call this manually because we want to avoid unnecessary checks and errors:
                $this->qdmSaveExtraValue($key, $value, import: true);
                continue;
            }
            if (!$this->set($value, $key, $errors, import: true)) {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Create a new data model
     * You can pass an array, object or json string to initialize the data model
     * This is the same as calling 'from' on the data model.
     *
     * @throws DataModelException if the a DataPoint declaration is invalid
     */
    public function __construct(array|string|object $data = [])
    {

        $this->initialize(true);

        if (!empty($data)) {
            $this->from($data);
        }
    }

        /**
         * Set a data point
         * $errors will be filled with any errors that occurred during the initialization
         */
    public function set(mixed $value, string $name, array &$errors = [], bool $import = false) : bool
    {
        // Auto initialize the data model:
        if (!$this->qdmAutoInitialize($errors, throw: false)) {
            return false;
        }

        // Get the data point:
        $dp = $this->qdmGetDataPoint($name);
        if (is_null($dp)) {
            // If its an extra data point then save it:
            if (!$this->qdmSaveExtraValue($name, $value, $import)) {
                $this->qdmAppendError(message: "DataPoint '{$name}' does not exist", to: $errors);
                return false;
            }
            // We saved it so we are done:
            return true;
        }

        // Should we import this data point:
        if ($import && !$dp->import) {
            return true; // We are not raising an error here because its not an error we just ignore it.
        }

        // Apply the custom filter if any:
        // Filters are always applied no matter what, import or not.
        if (
            $this->qdmHasFilters($dp->name) &&
            !Attr\Filter::applyFilters($value, $this->qdmGetFilters($dp->name), $errors)
        ) {
            // Errors are already populated by the filter
            return false;
        }

        // If its required and its null then we have an error:
        if (!$this->qdmValidateRequiredDataPoint($dp, $value)) {
            $this->qdmAppendError(of: $dp->name, message: "Required DataPoint cannot be null", to: $errors);
            return false;
        }

        // If its an array or object maybe its a nested data model:
        if ($dp->is_data_model) {
            // Only object array or string are allowed:
            if (!is_array($value) && !is_object($value) && !is_string($value)) {
                $this->qdmAppendError(
                    of : $dp->name,
                    message: "Value for nested DataPoint must be an Array, IDataModel object or JSON string",
                    to: $errors
                );
                return false;
            }

            // Build the data model:
            $dm = new $dp->types[0]();
            $nested_errors = []; // Report back the errors
            if (!$dm->from($value, $nested_errors)) {
                $this->qdmAppendError(
                    of : $dp->name,
                    message : $nested_errors,
                    to :$errors
                );
                return false;
            }

            // Set the value:
            $this->{$name} = $dm;
            return true;
        }

        // Type check:
        if (!$dp->isTypeAllowed($value)) {
            $this->qdmAppendError(
                of : $dp->name,
                message: sprintf(
                    "Value for DataPoint must be of type '%s' -> Got '%s'",
                    $name,
                    implode("|", $dp->types),
                    gettype($value)
                ),
                to: $errors
            );

            return false;
        }

        // Apply the custom check if any:
        if (
            $import &&
            $this->qdmHasChecks($dp->name) &&
            Attr\Check::applyChecks($value, $this->qdmGetChecks($dp->name), $errors) !== true
        ) {
            return false;
        }

        // Set the value:
        $this->{$name} = $value;
        return true;
    }

    /**
     * Get the data points of the data model
     *
     * @throws DataModelException if the a DataPoint declaration is invalid
     */
    private function qdmParseAttributes() : void
    {
        $property_position = 0;
        $reflection_class  = new ReflectionClass($this);
        foreach ($reflection_class->getProperties() as $property) {
            // Data point:
            if ($this->qdmBuildDataPoint($property, $property_position)) {
                // Parse the filters for the data point:
                $this->qdmBuildFilters($property);
                // Parse the checks for the data point:
                $this->qdmBuildChecks($property);
                // Next DataPoint:
                $property_position++;
            }
        }
        // Build the data point index:
        $this->qdmBuildDataPointIndex();
    }

    /**
     * Convert the data model to a string (json)
     */
    public function __toString() : string
    {
        return $this->toJson();
    }
}
