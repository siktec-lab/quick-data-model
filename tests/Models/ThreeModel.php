<?php

declare(strict_types=1);

namespace QDM\Tests\Models;

use QDM\DataPoint;
use QDM\DataModel;
use QDM\Traits;
use ArrayAccess;

class ThreeModel extends DataModel implements ArrayAccess
{
    use Traits\ArrayAccessTrait;

    public function __construct(
        #[DataPoint(required: true)]
        public ?string $name = "three",
        #[DataPoint(required: true)]
        public ?string $value = null,
        #[DataPoint]
        public ?OneModel $one = null,
        #[DataPoint]
        public ?TwoModel $two = null
    ) {
    }
}
