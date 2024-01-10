<?php

declare(strict_types=1);

namespace QDM\Tests\Visibility;

use PHPUnit\Framework\TestCase;
use QDM\Tests\Models;

class ExportImportVisTest extends TestCase
{
    public function setUp() : void
    {
        return;
    }

    public function tearDown() : void
    {
        return;
    }

    public function testBasicVisibility() : void
    {
        $car = new Models\SimpleCar();
        $errors = [];
        $status = $car->from([
            "brand"     => "Toyota",    // Will be updated & exported
            "model"     => "Corolla",   // Will be updated & exported 
            "year"      => 2010,        // Will be updated but not exported (export: false)
            "color"     => "red",       // Will not be updated but exported as default "white" (import: false)
            "code"      => "123456",    // Will be updated but not exported its protected (not export override)
            "owner"     => "John Doe"   // Will be updated & exported although protected (export: true)
        ]);
        $data = $car->toArray();
        
        $this->assertTrue($status);
        $this->assertCount(0, $errors);
        $this->assertEquals("Toyota", $data["brand"]);
        $this->assertEquals("Corolla", $data["model"]);
        $this->assertEquals(2010, $car->year);
        $this->assertFalse(array_key_exists("year", $data));
        $this->assertEquals("white", $data["color"]);
        $this->assertFalse(array_key_exists("code", $data));
        $this->assertEquals("123456", $car["code"]); 
        $this->assertEquals("John Doe", $data["owner"]);
    }

    public function testVisibilityWithDirectMethods() : void 
    {
        $car = new Models\SimpleCar();
        $car->from([
            "brand"     => "Toyota",    // Will be updated & exported
            "model"     => "Corolla",   // Will be updated & exported 
            "year"      => 2010,        // Will be updated but not exported (export: false)
            "color"     => "red",       // Will not be updated but exported as default "white" (import: false)
            "code"      => "123456",    // Will be updated but not exported its protected (not export override)
            "owner"     => "John Doe"   // Will be updated & exported although protected (export: true)
        ]);
        
        // Test has method with visibility checks:
        $this->assertTrue($car->has("year")); // By default has will not check for visibility
        $this->assertFalse($car->has("year", export: true)); // Now it will check for visibility
        $this->assertTrue($car->has("color", export: true));
        $this->assertFalse($car->has("color", import: true));
        
        // Test get method with visibility checks:
        $this->assertEquals(2010, $car->get("year")); // By default get will not check for export visibility
        $this->assertNull($car->get("year", export: true)); // Now it will check for export visibility
        $this->assertEquals("white", $car->get("color"));
        $this->assertEquals("white", $car->get("color", export: true));

        // Test set method with visibility checks:
        $done = $car->set("color", "black"); // By default set will not check for import visibility
        $this->assertTrue($done);
        $this->assertEquals("black", $car->color);
        $this->assertEquals("black", $car->get("color"));
        $this->assertEquals("black", $car->get("color", export: true));

        $done = $car->set("color", "red", import: true); // Now it will check for import visibility
        $this->assertFalse($done);
        $this->assertEquals("black", $car->color);
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