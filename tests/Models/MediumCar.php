<?php

declare(strict_types=1);

namespace QDM\Tests\Models;

use QDM\Attr;
use QDM\DataModel;
use QDM\Traits;
use ArrayAccess;

class MediumCar extends DataModel implements ArrayAccess
{
    use Traits\ArrayAccessTrait;

    #[Attr\DataPoint(required: true)]
    public ?string $brand = null;

    #[Attr\DataPoint(required: true)]
    public ?string $model = null;

    #[Attr\DataPoint(required: true)]
    public ?int $year = null;

    #[Attr\DataPoint]
    public ?SimpleCar $old_model = null;
}
