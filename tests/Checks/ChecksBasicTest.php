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
            "name" => "     harry potter and the philosopher's stone   ",
            "author" => "   j.k. rowling   ", // Min length 3
            "co_author" => "   me   ", // Min length 3
            "publisher" => "   BLOOMSBURY   ", // Starts with a capital letter
        ], $errors);

        print_r($book->describe());
        echo $book->toJson(true);
        $this->assertTrue($status);
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
