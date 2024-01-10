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
        $human = new Models\SimpleHuman();
        $this->assertEquals("John Doe", $human->name);
        $this->assertEquals(-1, $human->age);
        $this->assertEquals(null, $human->nickname);

        $arr = $human->toArray();
        $this->assertEquals("John Doe", $arr["name"]);
        $this->assertEquals(-1, $arr["age"]);
        $this->assertEquals(null, $arr["nickname"]);

        $json = json_decode($human->toJson(), true);
        $this->assertEquals("John Doe", $json["name"]);
        $this->assertEquals(-1, $json["age"]);
        $this->assertEquals(null, $json["nickname"]);
    }

    public function testFormArray() : void
    {

        $human = new Models\SimpleHuman();
        $init = $human->from([
            "name"      => "Marry Jane",
            "age"       => 20,
            "nickname"  => "JD",
        ]);

        $this->assertEquals(true, $init);
        $this->assertEquals("Marry Jane", $human->name);
        $this->assertEquals(20, $human->age);
        $this->assertEquals("JD", $human->nickname);

        $human = new Models\SimpleHuman();
        $init = $human->from([
            "name" => "Marry Jane",
            "age" => 20,
        ]);

        $this->assertEquals(true, $init);
        $this->assertEquals("Marry Jane", $human->name);
        $this->assertEquals(20, $human->age);
        $this->assertEquals(null, $human->nickname);
    }

    public function testRequiredProperty() : void
    {
        $human = new Models\SimpleHuman();
        $errors = [];
        // name is required and have a default value we are
        // trying to set it to null so it should fail
        // Giving us an error and setting the default value
        $status = $human->from(["name" => null], $errors);
        $this->assertEquals(false, $status);
        $this->assertEquals(1, count($errors));
        $this->assertEquals("John Doe", $human->name);
    }

    public function testSameTypeEnforcing() : void
    {
        $human = new Models\SimpleHuman();

        $errors = [];
        $status = $human->from(["age" => "Very Old"], $errors);

        $this->assertEquals(false, $status);
        $this->assertEquals(1, count($errors));
        $this->assertEquals(-1, $human->age);
    }

    public function testMultipleTypes() : void
    {
        $human = new Models\MediumHuman();
        $errors = [];
        $status = $human->from(["height" => 7], $errors);
        $this->assertEquals(true, $status);
        $this->assertEquals(0, count($errors));
        $this->assertEquals(7, $human->height);

        $errors = [];
        $status = $human->from(["height" => 7.0], $errors);
        $this->assertEquals(true, $status);
        $this->assertEquals(0, count($errors));
        $this->assertEquals(7.0, $human->height);

        $errors = [];
        $status = $human->from(["height" => "1.75m"], $errors);
        $this->assertEquals(true, $status);
        $this->assertEquals(0, count($errors));
        $this->assertEquals("1.75m", $human->height);

        $errors = [];
        $status = $human->from(["height" => [2, "m"]], $errors);
        $this->assertEquals(false, $status);
        $this->assertEquals(1, count($errors));
        $this->assertEquals(6.0, $human->height);
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
