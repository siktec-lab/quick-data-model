<?php

declare(strict_types=1);

namespace QDM\Tests\Models\General;

use QDM\Attr;
use QDM\DataModel;
use QDM\Traits;
use ArrayAccess;

class TwoModel extends DataModel implements ArrayAccess
{
    use Traits\ArrayAccessTrait;

    #[Attr\DataPoint(extra: true)]
    public array $extra = []; // Should supprot import and export

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
