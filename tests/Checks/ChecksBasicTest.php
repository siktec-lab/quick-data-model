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

    public static function setUpBeforeClass() : void
    {
        return;
    }

    public static function tearDownAfterClass() : void
    {
        return;
    }
}
