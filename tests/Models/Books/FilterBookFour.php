<?php

declare(strict_types=1);

namespace QDM\Tests\Models\Books;

use QDM\Attr;
use QDM\DataModel;
use QDM\Traits;
use ArrayAccess;

class FilterBookFour extends FilterBookThree
{

    // Inherited from FilterBookThree
    // name (string) (DataPoint) (required) (filters: trim, strtolower, ucwords) (checks: ::isUcFirst)
    // author (string) (DataPoint) (filters: ::name) (checks: min_length 3)
    // co_author (string) (DataPoint) (filters: ::name) (checks: ::name, min_length 3)
    // publisher (string) (DataPoint) (filters: trim) (checks: ::isUcFirst, min_length 5)
}
