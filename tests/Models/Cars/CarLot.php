<?php

declare(strict_types=1);

namespace QDM\Tests\Models\Cars;

use QDM\Attr;
use QDM\DataModel;
use QDM\Tests\Models;

class CarLot extends DataModel
{
    #[Attr\DataPoint]
    public CarPool $cars;

    public function __construct(
        array $cars = [],
        #[Attr\DataPoint(required: true)]
        public ?string $name = null,
        #[Attr\DataPoint]
        public ?Models\Humans\SimpleHuman $owner = null
    ) {
        $this->cars = new CarPool($cars);
    }
}
