<?php

declare(strict_types=1);

namespace QDM\Tests\From;

use PHPUnit\Framework\TestCase;
use QDM\Tests\Models;

final class TypesAndDefaultsTest extends TestCase
{
    public function setUp() : void
    {
        return;
    }

    public function tearDown() : void
    {
        return;
    }

    public function testDefaultValue() : void
    {
        $human = new Models\Humans\SimpleHuman();
        $this->assertEquals("John Doe", $human->name);
        $this->assertEquals(-1, $human->age);
        $this->assertNull($human->nickname);

        $arr = $human->toArray();
        $this->assertEquals("John Doe", $arr["name"]);
        $this->assertEquals(-1, $arr["age"]);
        $this->assertNull($arr["nickname"]);

        $json = json_decode($human->toJson(), true);
        $this->assertEquals("John Doe", $json["name"]);
        $this->assertEquals(-1, $json["age"]);
        $this->assertNull($json["nickname"]);
    }

    public function testFormArray() : void
    {

        $human = new Models\Humans\SimpleHuman();
        $init = $human->from([
            "name"      => "Marry Jane",
            "age"       => 20,
            "nickname"  => "JD",
        ]);

        $this->assertTrue($init);
        $this->assertEquals("Marry Jane", $human->name);
        $this->assertEquals(20, $human->age);
        $this->assertEquals("JD", $human->nickname);

        $human = new Models\Humans\SimpleHuman();
        $init = $human->from([
            "name" => "Marry Jane",
            "age" => 20,
        ]);

        $this->assertTrue($init);
        $this->assertEquals("Marry Jane", $human->name);
        $this->assertEquals(20, $human->age);
        $this->assertNull($human->nickname);
    }

    public function testRequiredProperty() : void
    {
        $human = new Models\Humans\SimpleHuman();
        $errors = [];
        // name is required and have a default value we are
        // trying to set it to null so it should fail
        // Giving us an error and setting the default value
        $status = $human->from(["name" => null], $errors);
        $this->assertFalse($status);
        $this->assertCount(1, $errors);
        $this->assertEquals("John Doe", $human->name);
    }

    public function testSameTypeEnforcing() : void
    {
        $human = new Models\Humans\SimpleHuman();

        $errors = [];
        $status = $human->from(["age" => "Very Old"], $errors);

        $this->assertFalse($status);
        $this->assertCount(1, $errors);
        $this->assertEquals(-1, $human->age);
    }

    public function testMultipleTypes() : void
    {
        $human = new Models\Humans\MediumHuman();
        $errors = [];
        $status = $human->from(["height" => 7], $errors);
        $this->assertTrue($status);
        $this->assertCount(0, $errors);
        $this->assertEquals(7, $human->height);

        $errors = [];
        $status = $human->from(["height" => 7.0], $errors);
        $this->assertTrue($status);
        $this->assertCount(0, $errors);
        $this->assertEquals(7.0, $human->height);

        $errors = [];
        $status = $human->from(["height" => "1.75m"], $errors);
        $this->assertTrue($status);
        $this->assertCount(0, $errors);
        $this->assertEquals("1.75m", $human->height);

        $errors = [];
        $status = $human->from(["height" => [2, "m"]], $errors);
        $this->assertFalse($status);
        $this->assertCount(1, $errors);
        $this->assertEquals(6.0, $human->height);
    }

    public function testExtraCatcherWithFrom() : void
    {
        $human = new Models\General\TwoModel();
        $errors = [];
        $status = $human->from([
            "name" => "two",
            "value" => "two",
            "catch1" => "test",
            "catch2" => true,
            "catch3" => 1,
            "catch4" => 1.0,
            "catch5" => [1, 2, 3],
        ], $errors);

        $this->assertTrue($status);
        $this->assertCount(0, $errors);

        $export = $human->toArray();
        $this->assertEquals([
            "name" => "two",
            "value" => "two",
            "one" => null,
            "extra" => [
                "catch1" => "test",
                "catch2" => true,
                "catch3" => 1,
                "catch4" => 1.0,
                "catch5" => [1, 2, 3],
            ],
        ], $export);
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
