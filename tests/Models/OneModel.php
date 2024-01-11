<?php

declare(strict_types=1);

namespace QDM\Tests\Models;

use QDM\Attr;
use QDM\DataModel;
use QDM\Traits;
use ArrayAccess;

class OneModel extends DataModel implements ArrayAccess
{
    use Traits\ArrayAccessTrait;

    public function __construct(
        #[Attr\DataPoint(required: true)]
        public ?string $name = "one",
        #[Attr\DataPoint(required: true)]
        public ?string $value = null
    ) {
    }
}
