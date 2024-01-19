<?php

declare(strict_types=1);

namespace QDM\Traits;

use ReflectionProperty;
use QDM\Attr\Check;

trait ChecksTrait
{
    /**
     * @var array<string,array<\QDM\Attr\Check>> The collected checks
     */
    protected array $property_checks = [];

    /**
     * Check if a DataPoint has checks:
     */
    final protected function qdmHasChecks(string $name) : bool
    {
        return array_key_exists($name, $this->property_checks) && !empty($this->property_checks[$name]);
    }

    /**
     * Get the defined checks for a given property
     * @return array<\QDM\Attr\Check>
     */
    final protected function qdmGetChecks(string $name) : array
    {
        return $this->property_checks[$name] ?? [];
    }

    /**
     * Collect and build the checks for a given property
     *
     * A simple wrapper around the buildAttributes method
     *
     * @throws DataModelException if the Check declaration is invalid
     */
    final protected function qdmBuildChecks(ReflectionProperty $property) : void
    {
        $this->property_checks[$property->getName()] = [];
        Check::buildAttributes(
            Check::class,
            $this->property_checks[$property->getName()],
            $property
        );
    }
}
