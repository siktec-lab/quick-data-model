<?php

declare(strict_types=1);

namespace QDM\Tests\Models\Humans;

use QDM\Attr;
use QDM\DataModel;

class SimpleHuman extends DataModel
{
    public function __construct(
        #[Attr\DataPoint(required: true)]
        public ?string $name = "John Doe",
        #[Attr\DataPoint]
        public ?int $age = -1,
        #[Attr\DataPoint]
        public ?string $nickname = null
    ) {
    }
}
