<?php

declare(strict_types=1);

namespace QDM\Tests\Exceptions;

use PHPUnit\Framework\TestCase;
use QDM\Tests\Models;
use QDM\DataModelException;

final class FiltersExceptionsTest extends TestCase
{
    public function setUp() : void
    {
        return;
    }

    public function tearDown() : void
    {
        return;
    }

    public function testCircurFilterRef() : void
    {
        $this->expectExceptionCode(DataModelException::CODE_CIRCULAR_REFERENCE);
        $model = new Models\Invalid\Ref\CircularFilterTwo();
        $model->initialize(); // We force the initialization to trigger the exception
    }

    public function testComplexCircurFilterRef() : void
    {
        $this->expectExceptionCode(DataModelException::CODE_CIRCULAR_REFERENCE);
        $model = new Models\Invalid\Ref\CircularComplexInvalid();
        $model->initialize(); // We force the initialization to trigger the exception
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
