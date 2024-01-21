<?php

declare(strict_types=1);

namespace QDM\Tests\Checks;

use PHPUnit\Framework\TestCase;
use QDM\Tests\Models;

class ChecksBasicTest extends TestCase
{
    public function setUp() : void
    {
        return;
    }

    public function tearDown() : void
    {
        return;
    }

    public function testCheckWithFunctions() : void
    {

        // A Book:
        $book = new Models\Books\FilterBookThree();
        $errors = [];
        $status = $book->from([
            "name" => "     harry potter and the philosopher's stone   ", // is ucfirst?
            "author" => "   j.k. rowling   ", // is min length 3?
            "co_author" => "   me and you   ", // is ucfirst? is min length 3?
            "publisher" => "   BLOOMSBURY   ", // is ucfirst?
        ], $errors);

        $this->assertTrue($status);
        $this->assertEmpty($errors);


        // Same book, but with errors:
        $book = new Models\Books\FilterBookThree();
        $errors = [];
        $status = $book->from([
            "name" => "     harry potter and the philosopher's stone   ", // is ucfirst?
            "author" => "   j.k. rowling   ", // is min length 3?
            "co_author" => "   me and you   ", // is ucfirst? is min length 3?
            "publisher" => "   bloomsbury   ", // is ucfirst? // <-------- error
        ], $errors);

        $this->assertFalse($status);
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey("publisher", $errors);
    }

    public function testChecksWhenArrayAccess() : void
    {

        // A Book:
        $book = new Models\Books\FilterBookThree();
        $book["name"] = "     harry potter and the philosopher's stone   "; // is ucfirst?
        $book["author"] = "   jk   "; // is min length 3?

        // Checks are not applied when using ArrayAccess
        // We can call validate() to apply checks after setting values (which are filtered always)

        $this->assertEquals("Jk", $book["author"]);
        $this->assertEquals("Jk", $book->author);
        $this->assertEquals("Jk", $book->toArray()["author"]);
        $this->assertEquals("Jk", $book->get("author"));


        // Lets validate the book:
        $errors = [];
        $valid = $book->validate($errors);

        $this->assertFalse($valid);
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey("author", $errors); // <-- min length 3 error
        $this->assertArrayHasKey("co_author", $errors); // <-- min length 3 error
        $this->assertArrayHasKey("publisher", $errors); // <-- min length 5 error
    }

    public function testChecksInCollections() : void
    {
        // Car collection:
        // All those are passed to the constructor:
        // There for instead of filtering and checking the values
        // The model is initialized with the values.
        // Only the nested models are filtered and checked.
        // Because they are constructed by the model itself.
        $shelf = new Models\Books\BookShelf(
            floor: "a",
            row:    1,
            books: [
                [
                    "name" => "   harry potter and the philosopher's stone   ",
                    "author" => "   j.k. rowling   ",
                    "co_author" => "   me and you1   ",
                    "publisher" => "   BLOOMSBURY   ",
                ],
                [
                    "name" => "   lord of the rings   ",
                    "author" => "   j.r.r. tolkien   ",
                    "co_author" => "   me and you2   ",
                    "publisher" => "   MARINER BOOKS   ",
                ]
            ]
        );

        $this->assertEquals("a", $shelf->floor); // No filters applied should not be valid later on.
        $this->assertEquals(1, $shelf->row);
        $this->assertEquals(2, $shelf->books->count()); // 2 books
        $this->assertEquals("J.K. Rowling", $shelf->books[0]->author); // Filtered + Checked + transformed.

        // Lets mutate forcefully inner collection to be invalid:
        $shelf->books[0]["author"] = "jk"; // is min length 3?

        // Lets validate the shelf:
        $errors = [];
        $valid = $shelf->validate($errors);

        $this->assertFalse($valid);
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey("floor", $errors); // <-- in array of ["A", "B", "C"]
        $this->assertNotEmpty($errors["books"][0]["author"] ?? null); // <-- min length 3 error

        //Fix the floor:
        $shelf->set("   c ", "floor"); // Since Import is false, the value is only filtered not checked.
        $shelf->books[0]["author"] = "   j.k. rowling   ";

        // Lets validate the shelf:
        $errors = [];
        $valid = $shelf->validate($errors);
        $this->assertTrue($valid);
        $this->assertEmpty($errors);
    }
    public static function setUpBeforeClass() : void
    {
        return;
    }

    public static function tearDownAfterClass() : void
    {
        return;
    }
}
