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

    public function testSimpleDataModelIterator() : void
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

    public function testSimpleCollectionIterator() : void
    {
        $pool = new Models\CarPool([
            [
                "brand"     => "Toyota",
                "model"     => "Corolla XRS",
                "year"      => 2020,
            ],
            [
                "brand"     => "Toyota",
                "model"     => "Corolla Hybrid",
                "year"      => 2021,
            ],
            [
                "brand"     => "Toyota",
                "model"     => "Corolla XSE",
                "year"      => 2019,
            ]
        ]);
        $cars = $pool->toArray();
        foreach ($pool as $index => $car) {
            $this->assertEquals($cars[$index], $car->toArray());
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
