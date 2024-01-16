<?php

declare(strict_types=1);

namespace QDM\Tests\Models\Invalid;

use QDM\Attr;
use QDM\Collection;
use stdClass;

/**
 * A collection of models with an incompatible type
*/
#[Attr\Collect(models: stdClass::class)]
class CollectionTypes extends Collection
{
    public function __construct()
    {
    }
}
