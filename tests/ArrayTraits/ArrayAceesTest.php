<?php

declare(strict_types=1);

namespace QDM\Tests\ArrayTraits;

use PHPUnit\Framework\TestCase;
use QDM\Tests\Models;

final class ArrayAceesTest extends TestCase
{
    public function setUp() : void
    {
        return;
    }

    public function tearDown() : void
    {
        return;
    }

    public function testArrayGetExists() : void
    {
        $human = new Models\MediumHuman();
        $human->from([
            "name"      => "Marry Jane",
            "age"       => 20,
            "nickname"  => "JD",
            "height"    => 5.5,
        ]);

        $this->assertEquals("Marry Jane", $human["name"]);
        $this->assertEquals(20, $human["age"]);
        $this->assertEquals("JD", $human["nickname"]);
        $this->assertEquals(5.5, $human["height"]);
        $this->assertEquals(null, $human["not_exists"]);
    }

    public function testArrayIsset() : void
    {
        $human = new Models\MediumHuman();
        $human->from([
            "name"      => "Marry Jane",
            "age"       => 20,
            "height"    => 5.5,
        ]);

        $this->assertEquals(true, isset($human["name"]));
        $this->assertEquals(true, isset($human["age"]));
        $this->assertEquals(false, isset($human["nickname"]));
        $this->assertEquals(true, isset($human["height"]));
        $this->assertEquals(false, isset($human["not_exists"]));
    }

    public function testArrayEmpty() : void
    {
        $human = new Models\MediumHuman();
        $human->from([
            "name"      => "Marry Jane",
            "age"       => 20,
            "nickname"  => null,
            "height"    => 5.5,
        ]);

        $this->assertEquals(false, empty($human["name"]));
        $this->assertEquals(false, empty($human["age"]));
        $this->assertEquals(true, empty($human["nickname"]));
        $this->assertEquals(false, empty($human["height"]));
        $this->assertEquals(true, empty($human["not_exists"]));
    }

    public function testArraySet() : void
    {
        $human = new Models\MediumHuman();
        $human->from([
            "name"      => "Marry Jane",
            "age"       => 20,
            "nickname"  => null,
            "height"    => 5.5,
        ]);

        $this->assertEquals("Marry Jane", $human["name"]);
        $this->assertEquals(20, $human["age"]);
        $this->assertEquals(null, $human["nickname"]);
        $this->assertEquals(5.5, $human["height"]);

        $human["name"] = "Mark Twain";
        $human["age"] = 21;
        $human["nickname"] = "MT";
        $human["height"] = 6.0;
        $human["will_be_ignores"] = "ignored";

        $this->assertEquals("Mark Twain", $human["name"]);
        $this->assertEquals(21, $human["age"]);
        $this->assertEquals("MT", $human["nickname"]);
        $this->assertEquals(6.0, $human["height"]);
        $this->assertEquals(null, $human["will_be_ignores"]);

        $human["name"] = null; // Should be ignored because it is not valid name is required
        $this->assertEquals("Mark Twain", $human["name"]);
    }

    public function testArrayUnset() : void
    {
        // Unset is actually setting the value to default
        $human = new Models\MediumHuman();
        $human->from([
            "name"      => "Marry Jane",
            "age"       => 20,
            "nickname"  => "JD",
            "height"    => 5.5,
        ]);

        $this->assertEquals("Marry Jane", $human["name"]);
        $this->assertEquals(20, $human["age"]);
        $this->assertEquals("JD", $human["nickname"]);
        $this->assertEquals(5.5, $human["height"]);

        unset($human["name"]);
        unset($human["age"]);
        unset($human["nickname"]);
        unset($human["height"]);
        unset($human["will_be_ignores"]);

        $this->assertEquals("John Doe", $human["name"]);
        $this->assertEquals(-1, $human["age"]);
        $this->assertEquals(null, $human["nickname"]);
        $this->assertEquals(6.0, $human["height"]);
        $this->assertEquals(null, $human["will_be_ignores"]);
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
