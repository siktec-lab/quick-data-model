<?php

declare(strict_types=1);

namespace QDM\Traits;

use ReflectionProperty;
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
     * Build a DataPoint from a property:
     */
    private function buildDataPoint(ReflectionProperty $property, int $position) : bool
    {
        // Collect
        $attributes = $property->getAttributes(DataPoint::class);
        if (count($attributes) > 0) {
            // Basic data point info:
            $dp = $attributes[0]->newInstance();
            $dp->name = $property->getName();
            $dp->default = DataPoint::detectDefaultValue($property, $this);
            $dp->position = $position++;

            // Parse the types and set the nullable flag:
            DataPoint::parseTypes($property->getType(), $dp);

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
