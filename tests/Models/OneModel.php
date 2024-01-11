<?php

declare(strict_types=1);

namespace QDM\Tests\Models;

use QDM\DataPoint;
use QDM\DataModel;
use QDM\Traits;
use ArrayAccess;

class OneModel extends DataModel implements ArrayAccess
{
    use Traits\ArrayAccessTrait;

    public function __construct(
        #[DataPoint(required: true)]
        public ?string $name = "one",
        #[DataPoint(required: true)]
        public ?string $value = null
    ) {
    }
}
