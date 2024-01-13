<?php

declare(strict_types=1);

namespace QDM\Traits;

use ReflectionProperty;
use QDM\Attr\Filter;
use QDM\Attr\DataPoint;

trait FiltersTrait
{
    /**
     * @var array<string,array<\QDM\Attr\Filter>> The collected filters
     */
    private array $property_filters = [];


    /**
     * Apply filters to a value
     *
     * Will mutate the given value and return true if all filters were applied successfully
     * Otherwise will return false and populate the given errors array
     *
     * @param mixed $value The value to apply filters to
     * @param array<\QDM\Attr\Filter> $filters The filters to apply
     * @param array<string> $errors The errors array to Populate
     */
    final protected static function applyFilters(
        mixed &$value,
        array $filters,
        array &$errors = []
    ) : bool {
        // Loop through filters
        foreach ($filters as $filter) {
            $method = $filter->call;
            $args   = $filter->args;
            $types  = $filter->types;

            // Check if filter is callable
            $call_filter = Filter::isCallable($method);

            if ($call_filter === false) {
                $name = is_array($method) ? implode("::", $method) : $method;
                $errors[] = "Filter '{$name}' is not callable";
                return false;
            }

            // Apply value marker use the replac
            $args = Filter::applyValueToArgs($value, $args);

            // Execute filter
            [$status, $value] = Filter::execFilter($method, $args, $types);

            // Check if filter was applied successfully
            if (!$status) {
                $errors[] = $value;
                return false;
            }
        }
        return true;
    }

    /**
     * Check if has filters
     */
    final public function hasFilters(string $name) : bool
    {
        return array_key_exists($name, $this->property_filters);
    }

    /**
     * Get the defined filters for a given property
     *
     * @return array<\QDM\Attr\Filter>
     */
    private function getFilters(string $name) : array
    {
        return $this->property_filters[$name] ?? [];
    }

    /**
     * Build a data point from a property:
     */
    private function buildFilters(
        ReflectionProperty $property,
        DataPoint $dp
    ) : bool {
        // Collect
        $attributes = $property->getAttributes(Filter::class);
        $filters = [];
        foreach ($attributes as $attribute) {
            $filters[] = $attribute->newInstance();
        }
        if (!empty($filters)) {
            $this->property_filters[$dp->name] = $filters;
        }
        return !empty($filters);
    }
}
