<?php

declare(strict_types=1);

namespace QDM\Tests\Models;

use QDM\Attr;
use QDM\DataModel;
use QDM\Traits;
use ArrayAccess;

class TwoModel extends DataModel implements ArrayAccess
{
    use Traits\ArrayAccessTrait;

    public function __construct(
        #[Attr\DataPoint(required: true)]
        public ?string $name = "two",
        #[Attr\DataPoint(required: true)]
        public ?string $value = null,
        #[Attr\DataPoint]
        public ?OneModel $one = null
    ) {
    }
}
