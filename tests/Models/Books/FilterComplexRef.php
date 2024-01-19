<?php

declare(strict_types=1);

namespace QDM\Tests\Models\Books;

use QDM\Attr;
use QDM\DataModel;
use QDM\Traits;
use ArrayAccess;

class FilterComplexRef extends DataModel implements ArrayAccess
{
    use Traits\ArrayAccessTrait;

    #[Attr\DataPoint]
    #[Attr\Filter("trim")]
    public ?string $one = null;

    #[Attr\DataPoint]
    #[Attr\Filter(ref: [self::class, "one"])]
    #[Attr\Filter(ref: "#three")]
    #[Attr\Filter(ref: "QDM\Tests\Models\Books\FilterComplexRef#four")] //circular reference <--------
    public ?string $two = null;

    #[Attr\DataPoint]
    #[Attr\Filter(ref: "#one")]
    #[Attr\Filter("strtolower")]
    public ?string $three = null;

    #[Attr\DataPoint]
    #[Attr\Filter(ref: "QDM\Tests\Models\Books\FilterComplexRef#three")]
    #[Attr\Filter("ucwords")]
    public ?string $four = null;
}
