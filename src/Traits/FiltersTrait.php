<?php

declare(strict_types=1);

namespace QDM\Traits;

use ReflectionProperty;
use QDM\Attr\Filter;

trait FiltersTrait
{
    /**
     * @var array<string,array<\QDM\Attr\Filter>> The collected filters
     */
    protected array $qdm_property_filters = [];

    /**
     * Check if a property has filters
     */
    final protected function qdmHasFilters(string $name) : bool
    {
        return array_key_exists($name, $this->qdm_property_filters)  && !empty($this->qdm_property_filters[$name]);
    }

    /**
     * Get the defined filters for a given property
     *
     * @return array<\QDM\Attr\Filter>
     */
    final protected function qdmGetFilters(string $name) : array
    {
        return $this->qdm_property_filters[$name] ?? [];
    }

    /**
     * Collect and build the filters for a given property
     *
     * A simple wrapper around the buildAttributes method
     *      *
     * @throws DataModelException if the Filter declaration is invalid
     */
    final protected function qdmBuildFilters(ReflectionProperty $property) : void
    {
        $this->qdm_property_filters[$property->getName()] = [];
        Filter::buildAttributes(
            Filter::class,
            $this->qdm_property_filters[$property->getName()],
            $property
        );
    }
}
