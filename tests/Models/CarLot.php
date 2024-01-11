<?php

declare(strict_types=1);

namespace QDM\Tests\Models;

use QDM\Attr;
use QDM\DataModel;

class CarLot extends DataModel
{
    #[Attr\DataPoint]
    public CarPool $cars;

    public function __construct(
        array $cars = [],
        #[Attr\DataPoint(required: true)]
        public ?string $name = null,
        #[Attr\DataPoint]
        public ?SimpleHuman $owner = null
    ) {
        $this->cars = new CarPool($cars);
    }
}
