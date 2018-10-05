<?php

namespace Vcn\Lib;

use PHPUnit_Framework_Error_Warning;
use Vcn\Lib\EnumTest\Color;
use Vcn\Lib\EnumTest\Fruit;
use Vcn\Lib\EnumTest\Silly;
use Vcn\Lib\EnumTest\Vegetables;

class EnumTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @throws Enum\Exception\InvalidInstance
     */
    public function testSingletonInstances()
    {
        $this->assertSame(Fruit::BANANA(), Fruit::BANANA());
        $this->assertSame(Vegetables::byName('SPINACH'), Vegetables::byName('SPINACH'));
        $this->assertSame(Color::BLUE(), Color::byName('BLUE'));
    }

    /**
     * @test
     */
    public function testByName()
    {
        $this->assertSame("CAULIFLOWER", Vegetables::CAULIFLOWER()->getName());
        $this->assertSame("SPINACH", Vegetables::SPINACH()->getName());
        $this->assertSame("APPLE", Fruit::APPLE()->getName());
        $this->assertSame("BANANA", Fruit::BANANA()->getName());
    }

    /**
     * @test
     */
    public function testgetAllNames()
    {
        $constList = Vegetables::getAllNames();

        $this->assertSame(2, count($constList));

        $this->assertContains('CAULIFLOWER', $constList);
        $this->assertContains('SPINACH', $constList);
    }

    /**
     * @test
     * @expectedException \Vcn\Lib\Enum\Exception\InvalidInstance
     */
    public function testNonExistingName()
    {
        $nonExistingName = implode('', Vegetables::getAllNames());

        Vegetables::byName($nonExistingName);
    }

    /**
     * @test
     */
    public function testGetAllInstances()
    {
        $this->assertEquals(
            Vegetables::getAllNames(),
            array_map(
                function (Enum $e) {
                    return $e->getName();
                },
                Vegetables::getAllInstances()
            )
        );
    }

    /**
     * @test
     */
    public function testJsonSerialize()
    {
        $this->assertEquals(
            Vegetables::getAllNames(),
            array_map(
                function (Enum $e) {
                    return $e->jsonSerialize();
                },
                Vegetables::getAllInstances()
            )
        );
    }

    /**
     * @test
     */
    public function testEquals()
    {
        $this->assertTrue(Vegetables::SPINACH()->equals(Vegetables::SPINACH()));
        $this->assertTrue(Vegetables::CAULIFLOWER()->equals(Vegetables::CAULIFLOWER()));
        $this->assertFalse(Vegetables::SPINACH()->equals(Vegetables::CAULIFLOWER()));
        $this->assertFalse(Vegetables::CAULIFLOWER()->equals(Vegetables::SPINACH()));
    }

    /**
     * @test
     */
    public function testWhen()
    {
        $actual =
            Vegetables
                ::CAULIFLOWER()
                ->when(Vegetables::CAULIFLOWER(), 0)
                ->when(Vegetables::SPINACH(), 1)
                ->get();

        $this->assertSame(0, $actual);
    }

    /**
     * @test
     */
    public function testWhenDo()
    {
        $actual =
            Vegetables
                ::CAULIFLOWER()
                ->whenDo(
                    Vegetables::CAULIFLOWER(),
                    function () {
                        return 0;
                    }
                )
                ->whenDo(
                    Vegetables::SPINACH(),
                    function () {
                        return 1;
                    }
                )
                ->get();

        $this->assertSame(0, $actual);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function testEqualsNotWorkingOnTwoDifferentEnums()
    {
        Vegetables::SPINACH()->equals(Fruit::BANANA());
    }

    /**
     * @test
     * @expectedException \Vcn\Lib\Enum\Exception\InvalidInstance
     */
    public function testInvalidEnum()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        Vegetables::INVALID();
    }

    /**
     * @test
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testInheritThenInheritAgainProducesWarning()
    {
        Silly::GOOSE();
    }

    /**
     * @test
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testInheritThenInheritAgainProducesWarning2()
    {
        Silly::CAULIFLOWER();
    }
}
