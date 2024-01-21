<?php

declare(strict_types=1);

namespace QDM\Tests\Models\Books;

use QDM\Attr;
use QDM\Attr\Checks\With;
use QDM\DataModel;

class BookShelf extends DataModel
{
    #[Attr\DataPoint]
    public BookCollection $books;

    public function __construct(
        // The row must be greater than 0
        #[Attr\DataPoint]
        #[Attr\Check(With::GREATER_THAN, args : [ 0 ])]
        public int $row = 0,
        //The floor must be one of the following: A, B, C
        #[Attr\DataPoint]
        #[Attr\Filter("trim"),
    Attr\Filter("strtoupper")]
        #[Attr\Check(With::IN, args : [ ["A", "B", "C"] ])]
        public string $floor = "A",
        // Data for the Books:
        array $books = [],
    ) {
        $this->books = new BookCollection($books);
    }
}
