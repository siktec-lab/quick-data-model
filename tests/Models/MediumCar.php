<?php

declare(strict_types=1);

namespace QDM\Tests\Models;

use QDM\DataPoint;
use QDM\DataModel;
use QDM\Traits;

use ArrayAccess;

class MediumCar extends DataModel implements ArrayAccess
{
    use Traits\ArrayAccessTrait;


    #[DataPoint(required: true)]
    public ?string $brand = null;

    #[DataPoint(required: true)]
    public ?string $model = null;

    #[DataPoint(required: true)]
    public ?int $year = null;

    #[DataPoint]
    public ?SimpleCar $old_model = null;
}