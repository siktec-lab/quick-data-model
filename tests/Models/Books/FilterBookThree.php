<?php

declare(strict_types=1);

namespace QDM\Tests\Models\Books;

use QDM\Attr;
use QDM\Attr\Filters\With;
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
    #[Attr\Check("::is_ucfirst")]
    public ?string $name = null;

    #[Attr\DataPoint]
    #[Attr\Filter(ref : "::name")]
    #[Attr\Check("min_length", args: [ 3 ])]
    public string $author = "";

    #[Attr\DataPoint]
    #[Attr\Filter(ref : "::name")]
    #[
      Attr\Check(ref: "::name"),
      Attr\Check(With::MIN_LENGTH, args: [ 3 ])
    ]
    public string $co_author = "";

    #[Attr\DataPoint]
    #[Attr\Filter("trim")]
    #[Attr\Check("::is_ucfirst")]
    #[Attr\Check(With::MIN_LENGTH, args: [ 5 ])]
    public string $publisher = "";

    public static function is_ucfirst(string $value) : bool|string
    {
      return $value[0] === strtoupper($value[0])
        ? true
        : "Must start with uppercase letter";
    }
}
