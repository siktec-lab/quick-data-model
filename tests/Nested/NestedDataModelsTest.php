<?php

declare(strict_types=1);

namespace QDM\Tests\Nested;

use PHPUnit\Framework\TestCase;
use QDM\Tests\Models;

class NestedDataModelsTest extends TestCase
{
    public function setUp() : void
    {
        return;
    }

    public function tearDown() : void
    {
        return;
    }

    public function testBasicNestedElementFromArray() : void
    {
        $car = new Models\Cars\MediumCar();
        $errors = [];
        $status = $car->from([
            "brand"     => "Toyota",
            "model"     => "Corolla XRS",
            "year"      => 2010,
            "old_model" => [
                "brand"     => "Toyota",
                "model"     => "Corolla S",
                "year"      => 2008
            ]
        ], $errors);
        $this->assertTrue($status);
        $this->assertCount(0, $errors);
        $this->assertEquals("Corolla XRS", $car->model);
        $this->assertEquals("Corolla S", $car->old_model->model);
    }

    public function testBasicNestedElementFromObject() : void
    {
        $old_model = new Models\Cars\SimpleCar();
        $old_model->from([
            "brand"     => "Toyota",
            "model"     => "Corolla S",
            "year"      => 2008,
            "owner"     => "John Doe"
        ]);
        $car = new Models\Cars\MediumCar();

        $errors = [];
        $status = $car->from([
            "brand"     => "Toyota",
            "model"     => "Corolla XRS",
            "year"      => 2010,
            "old_model" => $old_model
        ], $errors);

        $this->assertTrue($status);
        $this->assertCount(0, $errors);
        $this->assertEquals("Corolla XRS", $car->model);
        $this->assertEquals("Corolla S", $car->old_model->model);
        $this->assertEquals([
            "brand"     => "Toyota",
            "model"     => "Corolla XRS",
            "year"      => 2010,
            "old_model" => [
                "brand"     => "Toyota",
                "model"     => "Corolla S",
                "color"     => "white",
                "owner"     => "John Doe"
            ]
        ], $car->toArray());
    }

    public function testNullableNestedDataModel() : void
    {

        // Test nullable:
        $two = new Models\General\TwoModel(); // Has a nullable OneModel
        $status = $two->from([
            "value" => "two"
        ]);
        $this->assertTrue($status);
        $data = $two->toArray();
        $this->assertNull($two->one);
        $this->assertArrayHasKey("one", $data); // Should be in the array
        $this->assertNull($data["one"]);

        // Same test but with a one intialized:
        $two = new Models\General\TwoModel();
        $status = $two->from([
            "value" => "V2",
            "one" => [ "value" => "V1" ]
        ]);
        $this->assertTrue($status);
        $this->assertEquals([
            "name" => "two",
            "value" => "V2",
            "one" => [
                "name" => "one",
                "value" => "V1"
            ],
            "extra" => [] // Model has extra data catcher
        ], $two->toArray());
    }

    public function testMultipleNestedModels() : void
    {
        $three = new Models\General\ThreeModel();
        $status = $three->from([
            "value" => "V3",
            "one" => [
                "value" => "V31"
            ],
            "two" => [
                "value" => "V32",
                "one" => [
                    "value" => "V321"
                ]
            ]
        ]);
        $this->assertTrue($status);
        $this->assertEquals([
            "name" => "three",
            "value" => "V3",
            "one" => [
                "name" => "one",
                "value" => "V31"
            ],
            "two" => [
                "name" => "two",
                "value" => "V32",
                "one" => [
                    "name" => "one",
                    "value" => "V321"
                ],
                "extra" => [] // Model has extra data catcher public so its exported
            ]
        ], $three->toArray());
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
