<?php

declare(strict_types=1);

namespace QDM\Tests\Models\Books;

use QDM\Attr;
use QDM\Attr\Checks\With;
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
    #[Attr\Check("::isUcFirst")]
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
    #[Attr\Check("::isUcFirst")]
    #[Attr\Check(With::MIN_LENGTH, args: [ 5 ])]
    public string $publisher = "";

    public static function isUcFirst(string $value) : bool|string
    {
        if (empty($value)) {
            return false;
        }
        return $value[0] === strtoupper($value[0])
        ? true
        : "Must start with uppercase letter";
    }
}
