<?php

declare(strict_types=1);

namespace QDM\Tests\Models;

use QDM\DataPoint;
use QDM\DataModel;
use QDM\Traits;
use ArrayAccess;

class SimpleCar extends DataModel implements ArrayAccess
{
    use Traits\ArrayAccessTrait;

    public bool $is_valid = false; // not a datapoint for our intents and purposes

    #[DataPoint(required: true)]
    public ?string $brand = null;

    #[DataPoint(required: true)]
    public ?string $model = null;

    #[DataPoint(export: false)]
    public ?int $year = null; // public but forced to not be exported

    #[DataPoint(import: false)]
    public ?string $color = "white"; // Will not be imported only exported

    #[DataPoint]
    protected ?string $code = null; // Private should not be exported

    #[DataPoint(export: true)]
    protected ?string $owner = null; // Private but forced to be exported
}
