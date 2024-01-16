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

    #[Attr\DataPoint(extra: true, export: false)]
    public array $extra = []; // Should supprot import but not export

    public function __construct(
        #[Attr\DataPoint(required: true)]
        public ?string $name = "one",
        #[Attr\DataPoint(required: true)]
        public ?string $value = null
    ) {
    }
}
