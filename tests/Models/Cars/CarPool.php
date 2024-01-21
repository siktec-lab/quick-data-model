<?php

declare(strict_types=1);

namespace QDM\Tests\Models\Cars;

use QDM\Attr;
use QDM\Collection;

#[Attr\Collect(
    models: SimpleCar::class // Or "mixed"
)]
class CarPool extends Collection
{
}
