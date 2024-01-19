<?php

declare(strict_types=1);

namespace QDM\Tests\Filters;

use PHPUnit\Framework\TestCase;
use QDM\Tests\Models;

class FiltersBasicTest extends TestCase
{
    public function setUp() : void
    {
        return;
    }

    public function tearDown() : void
    {
        return;
    }

    public function testBasicPhpBuiltinFunctions() : void
    {

        // A medium car:
        $car = new Models\MediumCar();
        $errors = [];
        $status = $car->from([
            "brand" => "   toyota   ", // A trim + ucfirst an custom filter is applied
            "model" => "   Corolla   ", // A trim filter is applied
            "year"  => "   2010   ",  // A filter intval is applied
        ], $errors);

        $this->assertTrue($status);
        $this->assertCount(0, $errors);

        $data = $car->toArray();
        $this->assertEquals([
            "brand" => "Toyota",
            "model" => "Corolla",
            "year" => 2010,
            "old_model" => null,
        ], $data);
    }

    public function testFilterRefFunctions() : void
    {

        // A medium car:
        $book = new Models\Books\FilterBookOne();
        $errors = [];
        $status = $book->from([
            "name" => "     harry potter and the philosopher's stone   ",
            "author" => "   j.k. rowling   ",
        ], $errors);

        $this->assertTrue($status);
        $this->assertEquals("Harry Potter And The Philosopher's Stone", $book->name);
        $this->assertEquals("J.K. Rowling", $book->author);
    }

    public function testFilterRefOtherModelsFunctions() : void
    {

        // Book with filters from other models:
        $book = new Models\Books\FilterBookTwo();
        $errors = [];
        $status = $book->from([
            "name" => "     harry potter and the philosopher's stone   ",
            "author" => "   j.k. rowling   ",
        ], $errors);

        $this->assertTrue($status);
        $this->assertEquals("Harry Potter And The Philosopher's Stone", $book->name);
        $this->assertEquals("J.K. Rowling", $book->author);

        // Complex book with unreasonable filters references:
        $book = new Models\Books\FilterComplexRef();
        $errors = [];
        $status = $book->from([
            "one" => "  one ", // trim
            "two" => "  two ", // trim(one),
                               // trim(three->one), strtolower(three),
                               // trim(four->three->one), strtolower(four->three), ucwords(four)
            "three" => "  THREE ", // trim(one), strtolower
            "four" => "  FOUR ", // trim(three->one), strtolower(three), ucwords
        ], $errors);

        $this->assertTrue($status);
        $this->assertEquals([
            "one"   => "one",
            "two"   => "Two",
            "three" => "three",
            "four"  => "Four",
        ], $book->toArray());
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
