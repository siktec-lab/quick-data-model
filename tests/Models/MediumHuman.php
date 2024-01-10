<?php

declare(strict_types=1);

namespace QDM\Tests\Models;

use QDM\DataPoint;
use QDM\DataModel;
use QDM\Traits;
use ArrayAccess;
use Iterator;

class MediumHuman extends DataModel implements ArrayAccess, Iterator
{
    use Traits\ArrayAccessTrait;
    use Traits\IteratorTrait;

    #[DataPoint(required: true)]
    public ?string $name = "John Doe";

    #[DataPoint]
    public ?int $age = -1;

    #[DataPoint]
    public ?string $nickname = null;

    #[DataPoint]
    public string|float|int $height = 6.;

    public string $im_not_a_datapoint = "I'm not a datapoint";
}
