<?php

declare(strict_types=1);

namespace QDM\Tests\Models\Books;

use QDM\Attr;
use QDM\DataModel;
use QDM\Traits;
use ArrayAccess;

class FilterBookOne extends DataModel implements ArrayAccess
{
    use Traits\ArrayAccessTrait;

    #[Attr\DataPoint(required: true)]
    #[
      Attr\Filter("trim"), 
      Attr\Filter("strtolower"), 
      Attr\Filter("ucwords", args: [" \t\r\n\f\v"])
    ]
    public ?string $name = null;

    // #[Attr\DataPoint]
    // #[Attr\Filter(ref : [FilterBookOne::class, "myFilter"])]
    // public string $author = "";

    // #[Attr\DataPoint]
    // #[Attr\Filter(ref : "QDM\Tests\Models\Books\FilterBookOne::myFilter")]

    // #[Attr\DataPoint]
    // #[Attr\Filter(ref : "#name")]
    // public string $publisher = "";
}
