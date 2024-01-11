<?php

declare(strict_types=1);

namespace QDM\Tests\Collection;

use PHPUnit\Framework\TestCase;
use QDM\Tests\Models;

class CollectionBasicTest extends TestCase
{
    public function setUp() : void
    {
        return;
    }

    public function tearDown() : void
    {
        return;
    }

    public function testEmptyCollection() : void
    {
        $expected = [
            "name" => "My Car Lot",
            "owner" => [
                "name" => "Marcos Quezada",
                "age" => 30,
                "nickname" => "Marcos"
            ],
            "cars" => []
        ];

        // Using a constructor:
        $lot = new Models\CarLot(
            cars: [],
            name: "My Car Lot",
            owner: new Models\SimpleHuman(
                name: "Marcos Quezada",
                age: 30,
                nickname: "Marcos"
            )
        );

        $count = $lot->cars->count();
        $this->assertEquals(0, $count);
        $this->assertEquals($expected, $lot->toArray());

        // Using the from() method:
        $lot = new Models\CarLot();
        $lot->from([
            "name" => "My Car Lot",
            "owner" => [
                "name" => "Marcos Quezada",
                "age" => 30,
                "nickname" => "Marcos"
            ],
            "cars" => []
        ]);
        $count = $lot->cars->count();
        $this->assertEquals(0, $count);
        $this->assertEquals($expected, $lot->toArray());
    }

    public function testCarLotCollectionFrom() : void
    {

        $pool = new Models\CarPool();
        $errors = [];
        $status = $pool->from([
            [
                "brand" => "Toyota",
                "model" => "Corolla S",
                "year" => 2008
            ],
            [
                "brand" => "Toyota",
                "model" => "Corolla XRS",
                "year" => 2010
            ],
            [
                "brand" => "Toyota",
                "model" => "Corolla LE",
                "year" => 2012
            ]
        ], $errors);
        $this->assertTrue($status);
        $this->assertCount(0, $errors);
        $this->assertEquals(3, $pool->count());
    }

    public function testCarLotCollectionExtend() : void
    {

        $pool1 = new Models\CarPool();
        $pool2 = new Models\CarPool();
        $errors = [];

        //Initiate pool1
        $status = $pool1->from([
            "one" => [
                "brand" => "Toyota",
                "model" => "Corolla S",
                "year" => 2008
            ],
            "two" => [
                "brand" => "Toyota",
                "model" => "Corolla XRS",
                "year" => 2010
            ]
        ], $errors);
        $this->assertTrue($status);
        $this->assertCount(0, $errors);
        $this->assertEquals(2, $pool1->count());

        //Initiate pool2
        $status = $pool2->from([
            "one" => [
                "brand" => "Renault",
                "model" => "Clio",
                "year" => 2008
            ]
        ], $errors);
        $this->assertTrue($status);
        $this->assertCount(0, $errors);
        $this->assertEquals(1, $pool2->count());

        //Extend pool2 with pool1
        $status = $pool2->extend($pool1, $errors);
        $this->assertTrue($status);
        $this->assertCount(0, $errors);
        $this->assertEquals(2, $pool2->count());

        //Extend pool2 with new car array
        $status = $pool2->extend([
            "three" => [
                "brand" => "Toyota",
                "model" => "Corolla LE",
                "year" => 2012
            ]
        ], $errors);
        $this->assertTrue($status);
        $this->assertCount(0, $errors);
        $this->assertEquals(3, $pool2->count());

        // Finaly lets test we have the right cars
        $this->assertEquals("Corolla S", $pool2["one"]["model"]); // ArrayAccess syntax
        $this->assertEquals("Corolla S", $pool2->get("one")?->model); // Object syntax
        $this->assertEquals("Corolla XRS", $pool2["two"]["model"]); // ArrayAccess syntax
        $this->assertEquals("Corolla XRS", $pool2->get("two")?->model); // Object syntax
        $this->assertEquals("Corolla LE", $pool2["three"]["model"]); // ArrayAccess syntax
        $this->assertEquals("Corolla LE", $pool2->get("three")?->model); // Object syntax
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
