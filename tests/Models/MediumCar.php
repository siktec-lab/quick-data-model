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
    #[Attr\Filter("trim"),
      Attr\Filter("ucfirst"),
      Attr\Filter([MediumCar::class, "myFilter"], args : [10], value_pos : 0)
    ]
    public ?string $brand = null;

    #[Attr\DataPoint(required: true)]
    #[Attr\Filter("trim")]
    public ?string $model = null;

    #[Attr\DataPoint(required: true)]
    #[Attr\Filter("intval", types: [ "integer" ])]
    public ?int $year = null;

    #[Attr\DataPoint]
    public ?SimpleCar $old_model = null;

    public static function myFilter(string $value, int $max_length = 10) : string
    {
        return substr($value, 0, $max_length);
    }
}
