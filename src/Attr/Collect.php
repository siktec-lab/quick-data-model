<?php

declare(strict_types=1);

namespace QDM\Attr;

use Attribute;

// An attribute class to mark if property is a data point its called DataPoint and it accepts a boolean flag
#[Attribute(Attribute::TARGET_CLASS)]
class Collect extends BaseAttr
{
    /**
     * The type of the collection or mixed for any type
     * that implements the IDataModel interface
     */
    public function __construct(
        public string|array $models = "mixed"
    ) {
        //TODO: All the types building should happen here
    }

    /**
     * Describe the attribute as a dictionary
     * @return array<string,array|string|null> self descrption dictionary
     */
    public function describe() : array|string
    {
        return [
            "models" => $this->models
        ];
    }
}
