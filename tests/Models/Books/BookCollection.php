<?php

declare(strict_types=1);

namespace QDM\Tests\Models\Books;

use QDM\Attr;
use QDM\Collection;

#[Attr\Collect(
    models: FilterBookThree::class // Or "mixed"
)]
class BookCollection extends Collection
{
}
