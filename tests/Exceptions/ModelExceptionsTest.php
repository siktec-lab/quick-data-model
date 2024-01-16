<?php

declare(strict_types=1);

namespace QDM\Tests\Exceptions;

use PHPUnit\Framework\TestCase;
use QDM\Tests\Models;
use QDM\DataModelException;

final class ModelExceptionsTest extends TestCase
{
    public function setUp() : void
    {
        return;
    }

    public function tearDown() : void
    {
        return;
    }

    public function testInvalidAccessModifier() : void
    {
        $this->expectExceptionCode(DataModelException::CODE_ACCESS_MODIFIER);
        $model = new Models\Invalid\AccessModifier();
        $model->initialize(); // We force the initialization to trigger the exception
    }

    public function testErrorAccessModifier() : void
    {
        $model = new Models\Invalid\AccessModifier();
        $errors = [];
        $status = $model->from([], $errors);

        $this->assertFalse($status);
        $this->assertCount(1, $errors);
    }

    public function testInvalidExtraDataPointType() : void
    {
        $this->expectExceptionCode(DataModelException::CODE_EXTRA_DATAPOINT_TYPE);
        $model = new Models\Invalid\ExtraType();
        $model->initialize(); // We force the initialization to trigger the exception
    }

    public function testErrorExtraDataPointType() : void
    {
        $model = new Models\Invalid\ExtraType();
        $errors = [];
        $status = $model->from([], $errors);

        $this->assertFalse($status);
        $this->assertCount(1, $errors);
    }

    public function testInvalidCollectionTypes() : void
    {
        $this->expectExceptionCode(DataModelException::CODE_COLLECTION_TYPES);
        $model = new Models\Invalid\CollectionTypes();
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
