<?php

declare(strict_types=1);

namespace QDM\Tests\Models\Invalid\Ref;

use QDM\Attr;
use QDM\DataModel;
use QDM\Traits;
use ArrayAccess;

class CircularComplexInvalid extends DataModel implements ArrayAccess
{
    use Traits\ArrayAccessTrait;

    #[Attr\DataPoint]
    #[Attr\Filter("trim")]
    public ?string $one = null;

    #[Attr\DataPoint]
    #[Attr\Filter(ref: [self::class, "one"])]
    #[Attr\Filter(ref: "#three")]
    #[Attr\Filter(ref: "QDM\Tests\Models\Invalid\Ref\CircularComplexInvalid#four")] //circular reference <--------
    public ?string $two = null;

    #[Attr\DataPoint]
    #[Attr\Filter(ref: "#one")]
    public ?string $three = null;

    #[Attr\DataPoint]
    #[Attr\Filter(ref: "QDM\Tests\Models\Invalid\Ref\CircularComplexInvalid#three")]
    #[Attr\Filter(ref: [self::class, "two"])] // This is the circular reference   <--------------------------------
    public ?string $four = null;
}
