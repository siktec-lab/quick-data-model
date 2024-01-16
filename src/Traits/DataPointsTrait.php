<?php

declare(strict_types=1);

namespace QDM\Traits;

use ReflectionProperty;
use QDM\DataModelException;
use QDM\Attr\DataPoint;

trait DataPointsTrait
{
    /**
     * @var array<int,string> The data point index
     */
    protected array $data_point_index = [];

    /**
     * @var array<string,DataPoint> The collected data points
     */
    private array $data_points = [];

    /**
     * If it has an extra data point this means all extra data will be stored in this data point
     * It must be an array.
     * @var DataPoint The extra data point
     */
    private ?DataPoint $extra_data = null;

    /**
     * return all DataPoints names
     * @return array<string>
     */
    private function getDataPointsNames() : array
    {
        return array_keys($this->data_points);
    }

    /**
     * return a DataPoint by key name or position
     */
    private function getDataPoint(string|int $key) : ?DataPoint
    {
        return is_int($key)
            ? $this->data_points[$this->data_point_index[$key]] ?? null
            : $this->data_points[$key] ?? null;
    }

    /**
     * Saves an extra key value if extra data point is set
     */
    private function saveExtra(string|int $key, mixed $value, bool $import = false) : bool
    {
        if (
            $this->extra_data && // If it has an extra data point
            (!$import || $this->extra_data->import) // Whether its importable or not
        ) {
            // No need to check if it's an array because it's already checked in the constructor
            $this->{$this->extra_data->name}[$key] = $value;
            return true;
        }
        return false;
    }

    /**
     * Build a DataPoint from a property:
     *
     * @throws DataModelException if the a DataPoint declaration is invalid
     */
    private function buildDataPoint(ReflectionProperty $property, int $position) : bool
    {
        // Collect
        $attributes = $property->getAttributes(DataPoint::class);
        if (count($attributes) > 0) {
            // Basic data point info:
            $dp = $attributes[0]->newInstance();

            //Is it an extra data point?
            $dp->name = $property->getName();
            $dp->default = DataPoint::detectDefaultValue($property, $this);
            $dp->position = $position;

            // Parse the types and set the nullable flag:
            DataPoint::parseTypes($property->getType(), $dp);

            // Determine visibility: First throw if any thing other than public or protected is used:
            if (!$property->isProtected() && !$property->isPublic()) {
                throw new DataModelException(DataModelException::CODE_ACCESS_MODIFIER, [$dp->name]);
            }

            $dp->visible = $property->isPublic();
            $dp->export = $dp->export ?? $dp->visible;
            $dp->import = $dp->import ?? true;

            // Save the instance of the data point:
            if ($dp->extra) {
                if (!$dp->hasType("array")) {
                    throw new DataModelException(DataModelException::CODE_EXTRA_DATAPOINT_TYPE, [$dp->name]);
                }

                // Save the extra data point:
                $this->extra_data = $dp;

                // Make its initialize:
                if (!isset($this->{$dp->name}) || !is_array($this->{$dp->name})) {
                    $this->{$dp->name} = [];
                }

                // We don't want to add it to the data points:
                return false;
            }

            $this->data_points[$dp->name] = $dp;
            return true;
        }
        return false;
    }

    /**
     * Builds the data point index for easier access by position
     * Usefull while exporting data
     */
    private function buildDataPointIndex() : void
    {
        $this->data_point_index = array_column($this->data_points, "name", "position");
    }
}
