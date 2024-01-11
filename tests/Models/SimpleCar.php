<?php

declare(strict_types=1);

namespace QDM\Tests\Models;

use QDM\Attr;
use QDM\DataModel;
use QDM\Traits;
use ArrayAccess;

class SimpleCar extends DataModel implements ArrayAccess
{
    use Traits\ArrayAccessTrait;

    public bool $is_valid = false; // not a datapoint for our intents and purposes

    #[Attr\DataPoint(required: true)]
    public ?string $brand = null;

    #[Attr\DataPoint(required: true)]
    public ?string $model = null;

    #[Attr\DataPoint(export: false)]
    public ?int $year = null; // public but forced to not be exported

    #[Attr\DataPoint(import: false)]
    public ?string $color = "white"; // Will not be imported only exported

    #[Attr\DataPoint]
    protected ?string $code = null; // Private should not be exported

    #[Attr\DataPoint(export: true)]
    protected ?string $owner = null; // Private but forced to be exported
}
