<?php

declare(strict_types=1);

namespace QDM;

use Attribute;

// An attribute class to mark if property is a data point its called DataPoint and it accepts a boolean flag
#[Attribute(Attribute::TARGET_PROPERTY)]
class DataPoint
{

    public int $position = 0;

    public string $name = "";

    public array $types = [];

    public mixed $default = null;

    public bool $nullable = false;

    public bool $is_data_model = false;

    public bool $visible = true;

    public function __construct(
        public bool $required = false,
        public mixed $filter = null,
        public mixed $setter = null,
        public ?bool $export = null,
        public ?bool $import = null
    ) {
    }
}
