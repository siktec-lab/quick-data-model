<?php

declare(strict_types=1);

namespace QDM\Tests\Models\General;

use QDM\Attr;
use QDM\DataModel;
use QDM\Traits;
use ArrayAccess;

class ThreeModel extends DataModel implements ArrayAccess
{
    use Traits\ArrayAccessTrait;

    #[Attr\DataPoint(extra: true)]
    protected array $extra = []; // Should supprot import but not export

    public function __construct(
        #[Attr\DataPoint(required: true)]
        public ?string $name = "three",
        #[Attr\DataPoint(required: true)]
        public ?string $value = null,
        #[Attr\DataPoint]
        public ?OneModel $one = null,
        #[Attr\DataPoint]
        public ?TwoModel $two = null,
    ) {
    }
}
