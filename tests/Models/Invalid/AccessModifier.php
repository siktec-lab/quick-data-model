<?php

declare(strict_types=1);

namespace QDM\Tests\Models\Invalid;

use QDM\Attr;
use QDM\DataModel;
use QDM\Traits;
use ArrayAccess;

class AccessModifier extends DataModel implements ArrayAccess
{
    use Traits\ArrayAccessTrait;


    public function __construct(
        #[Attr\DataPoint(required: true)]
        public ?string $name = "one",
        #[Attr\DataPoint(required: true)]
        public ?string $value = null,
        #[Attr\DataPoint(export: true, import: true)]
        private array $value2 = [] // SHOULD NOT BE SUPPORTED will throw error
    ) {
    }
}
