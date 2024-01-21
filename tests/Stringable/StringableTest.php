<?php

declare(strict_types=1);

namespace QDM\Tests\Stringable;

use PHPUnit\Framework\TestCase;
use QDM\Tests\Models;

class StringableTest extends TestCase
{
    public function setUp() : void
    {
        return;
    }

    public function tearDown() : void
    {
        return;
    }

    public function testModelToString() : void
    {
        $human = new Models\Humans\SimpleHuman();
        $human->from([
            "name"      => "Marry Jane",
            "age"       => 20,
            "nickname"  => "JD",
        ]);
        $this->assertEquals(
            '{"name":"Marry Jane","age":20,"nickname":"JD"}',
            (string)$human
        );
    }

    public function testModelDebugView() : void
    {
        $car = new Models\Cars\MediumCar();
        $status = $car->from([
            "brand" => "   toyota   ", // A trim + ucfirst an custom filter is applied
            "model" => "   Corolla   ", // A trim filter is applied
            "year" => "   2010   ",  // A filter intval is applied
        ]);
        $struct = $car->describe();
        $this->assertTrue($status);
        $this->assertArrayHasKey("brand", $struct);
        $this->assertArrayHasKey("model", $struct);
        $this->assertArrayHasKey("year", $struct);
        $this->assertArrayHasKey("old_model", $struct);
        $this->assertNotEmpty($struct["old_model"]);

        // With collections:
        $carLot = new Models\Cars\CarLot();
        $struct = $carLot->describe();

        $this->assertArrayHasKey("cars", $struct);
        // Nested cars describe:
        $expected = [
            "name" => "QDM\Collection",
            "items" => "QDM\Tests\Models\Cars\SimpleCar"
        ];
        $this->assertEquals($expected, $struct["cars"]["nested"]);
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
