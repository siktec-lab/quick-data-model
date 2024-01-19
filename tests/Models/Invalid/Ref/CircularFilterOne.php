<?php

declare(strict_types=1);

namespace QDM\Tests\Models\Invalid\Ref;

use QDM\Attr;
use QDM\DataModel;
use QDM\Traits;
use ArrayAccess;

class CircularFilterOne extends DataModel implements ArrayAccess
{
    use Traits\ArrayAccessTrait;

    #[Attr\DataPoint(required: true)]
    #[
      Attr\Filter("trim"),
      Attr\Filter("strtolower"),
      Attr\Filter("ucwords", args: [" .\t\r\n\f\v"])
    ]
    public ?string $name = null;

    #[Attr\DataPoint]
    #[Attr\Filter(ref : [CircularFilterTwo::class, "author"])]
    public string $author = "";
}
