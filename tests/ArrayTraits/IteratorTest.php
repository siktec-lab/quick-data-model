<?php

declare(strict_types=1);

namespace QDM\Tests\ArrayTraits;

use PHPUnit\Framework\TestCase;
use QDM\Tests\Models;

final class IteratorTest extends TestCase
{
    public function setUp() : void
    {
        return;
    }

    public function tearDown() : void
    {
        return;
    }

    public function testSimpleIterator() : void
    {
        $human = new Models\MediumHuman();
        $human->from([
            "name"      => "Marry Jane",
            "age"       => 20,
            "nickname"  => "JD",
            "height"    => 5.5
        ]);
        $dps = $human->toArray();
        foreach ($human as $datapoint => $value) {
            $this->assertEquals($dps[$datapoint], $value);
        }
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
