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
        $car = new Models\MediumCar();
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
        $old_model = new Models\SimpleCar();
        $old_model->from([
            "brand"     => "Toyota",
            "model"     => "Corolla S",   
            "year"      => 2008,
            "owner"     => "John Doe"
        ]);
        $car = new Models\MediumCar();

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

    public static function setUpBeforeClass() : void
    {
        return;
    }

    public static function tearDownAfterClass() : void
    {
        return;
    }
}