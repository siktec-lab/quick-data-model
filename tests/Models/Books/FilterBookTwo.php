<?php

declare(strict_types=1);

namespace QDM\Tests\Models\Books;

use QDM\Attr;
use QDM\DataModel;
use QDM\Traits;
use ArrayAccess;

class FilterBookTwo extends DataModel implements ArrayAccess
{
    use Traits\ArrayAccessTrait;

    #[Attr\DataPoint(required: true)]
    #[Attr\Filter(ref : [FilterBookOne::class, "name"])] // Takes filter from another DataModel class
    public ?string $name = null;

    #[Attr\DataPoint]
    // Takes from the same DataModel class but its referrring to a different DataModel
    #[Attr\Filter(ref : "::name")]
    public string $author = "";

    #[Attr\DataPoint]
    // Takes filter from another DataModel class which is also referring
    #[Attr\Filter(ref : [FilterBookOne::class, "co_author"])]
    public ?string $co_author = null;

    #[Attr\DataPoint]
    // Takes filter from another DataModel class which is also referring
    #[Attr\Filter(ref : [FilterBookOne::class, "publisher"])]
    public string $publisher = "";
}
