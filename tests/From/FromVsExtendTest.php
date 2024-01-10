<?php

declare(strict_types=1);

namespace QDM\Tests\From;

use PHPUnit\Framework\TestCase;
use QDM\Tests\Models;

final class FromVsExtendTest extends TestCase
{
    public function setUp() : void
    {
        return;
    }

    public function tearDown() : void
    {
        return;
    }

    public function testFromTwiceOverride() : void
    {
        $human  = new Models\SimpleHuman();
        $status = $human->from([
            "name"      => "Marry Jane",
            "age"       => 20,
            "nickname"  => "JD",
        ]);

        $this->assertTrue($status);
        $this->assertEquals("Marry Jane", $human->name);
        $this->assertEquals(20, $human->age);
        $this->assertEquals("JD", $human->nickname);

        // This should revert all to default values and set only name
        $status = $human->from([
            "name"      => "Mark Twain",
        ]);

        $this->assertTrue($status);
        $this->assertEquals("Mark Twain", $human->name);
        $this->assertEquals(-1, $human->age);
        $this->assertNull($human->nickname);
    }

    public function testExtendTwiceOverride() : void
    {
        $human  = new Models\SimpleHuman();
        $status = $human->from([
            "name"      => "Marry Jane",
            "age"       => 20,
            "nickname"  => "JD",
        ]);

        $this->assertTrue($status);
        $this->assertEquals("Marry Jane", $human->name);
        $this->assertEquals(20, $human->age);
        $this->assertEquals("JD", $human->nickname);

        // This should revert all to default values and set only name
        $status = $human->extend([
            "name"      => "Mark Twain",
        ]);

        $this->assertTrue($status);
        $this->assertEquals("Mark Twain", $human->name);
        $this->assertEquals(20, $human->age);
        $this->assertEquals("JD", $human->nickname);
    }

    public function testRevertAfterFromExtend() : void
    {
        $human  = new Models\SimpleHuman();
        $status = $human->from([
            "name"      => "Moshe Dayan",
        ]);
        $this->assertTrue($status);
        $status = $human->extend([
            "age"       => 45,
            "nickname"  => "MD",
        ]);
        $this->assertTrue($status);
        $this->assertEquals("Moshe Dayan", $human->name);
        $this->assertEquals(45, $human->age);
        $this->assertEquals("MD", $human->nickname);

        // This should revert all to default values and set only name
        $human->revert();
        $this->assertEquals("John Doe", $human->name);
        $this->assertEquals(-1, $human->age);
        $this->assertNull($human->nickname);
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
