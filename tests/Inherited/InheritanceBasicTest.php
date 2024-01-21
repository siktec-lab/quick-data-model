<?php

declare(strict_types=1);

namespace QDM\Tests\Inherited;

use PHPUnit\Framework\TestCase;
use QDM\Tests\Models;

class InheritanceBasicTest extends TestCase
{
    public function setUp() : void
    {
        return;
    }

    public function tearDown() : void
    {
        return;
    }

    public function testModelFromModel() : void
    {

        // A medium car:
        $book3 = new Models\Books\FilterBookThree();
        $book4 = new Models\Books\FilterBookFour(); // Four is a child of Three

        $errors3 = [];
        $status3 = $book3->from([
            "name" => "   harry potter and the philosopher's stone   ",            
            "author" => "   j.k. rowling   ",
            "co_author" => "   me and you   ",
            "publisher" => "   BLOOMSBURY   ",
        ]);

        $errors4 = [];
        $status4 = $book4->from($book3); // From a model to another model

        $this->assertTrue($status3);
        $this->assertTrue($status4);
        $this->assertEmpty($errors3);
        $this->assertEmpty($errors4);
        $this->assertEquals($book3->toArray(), $book4->toArray());

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
