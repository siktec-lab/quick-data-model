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
            "year" => "   2010   ",  // A filter intval is applied
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
    public static function setUpBeforeClass() : void
    {
        return;
    }

    public static function tearDownAfterClass() : void
    {
        return;
    }
}
