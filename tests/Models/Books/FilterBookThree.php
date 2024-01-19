<?php

declare(strict_types=1);

namespace QDM\Tests\Models\Books;

use QDM\Attr;
use QDM\DataModel;
use QDM\Traits;
use ArrayAccess;

class FilterBookThree extends DataModel implements ArrayAccess
{
    use Traits\ArrayAccessTrait;

    #[Attr\DataPoint(required: true)]
    #[
      Attr\Filter("trim"),
      Attr\Filter("strtolower"),
      Attr\Filter("ucwords", args: [" .\t\r\n\f\v"])
    ]
    #[Attr\Check("#is_ucfirst")]
    public ?string $name = null;

    #[Attr\DataPoint]
    #[Attr\Filter(ref : "#name")]
    #[Attr\Check("strlen", args: [], against : [ ">=", 3 ])]
    public string $author = "";

    #[Attr\DataPoint]
    #[Attr\Filter(ref : "#name")]
    #[
      Attr\Check(ref: "#author"),
      Attr\Check("strlen", args: [], against : [ ">=", 3 ])
    ]
    public string $co_author = "";

    #[Attr\DataPoint]
    #[Attr\Filter(ref : "#name")]
    #[Attr\Check("#is_ucfirst", args: [], against : [ "===", true ])]
    public string $publisher = "";

    public static function is_ucfirst(string $value) : bool
    {
      return $value[0] === strtoupper($value[0]);
    }
}
