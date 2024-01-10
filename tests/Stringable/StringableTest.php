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
        $human = new Models\SimpleHuman();
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

    public static function setUpBeforeClass() : void
    {
        return;
    }

    public static function tearDownAfterClass() : void
    {
        return;
    }
}
