<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Vcn\Lib;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Vcn\Lib\Enum\Exception;
use Vcn\Lib\EnumTest\Color;
use Vcn\Lib\EnumTest\Fruit;
use Vcn\Lib\EnumTest\Silly;
use Vcn\Lib\EnumTest\Vegetables;

class EnumTest extends TestCase
{
    /**
     * @test
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
    public function testTryByName()
    {
        $this->assertSame("CAULIFLOWER", Vegetables::tryByName("CAULIFLOWER")->getName());
        $this->assertSame("SPINACH", Vegetables::tryByName("SPINACH")->getName());
        $this->assertSame("APPLE", Fruit::tryByName("APPLE")->getName());
        $this->assertSame("BANANA", Fruit::tryByName("BANANA")->getName());
        $this->assertSame(null, Fruit::tryByName("CAULIFLOWER"));
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
     */
    public function testNonExistingName()
    {
        $nonExistingName = implode('', Vegetables::getAllNames());

        $this->expectException(Exception\InvalidInstance::class);

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
     */
    public function testEqualsNotWorkingOnTwoDifferentEnums()
    {
        $this->expectException(InvalidArgumentException::class);

        Vegetables::SPINACH()->equals(Fruit::BANANA());
    }

    /**
     * @test
     */
    public function testInvalidEnum()
    {
        $this->expectException(Exception\InvalidInstance::class);

        /** @noinspection PhpUndefinedMethodInspection */
        Vegetables::INVALID();
    }

    /**
     * @test
     */
    public function testInheritThenInheritAgainProducesWarning()
    {
        $this->expectWarning();

        Silly::GOOSE();
    }

    /**
     * @test
     */
    public function testInheritThenInheritAgainProducesWarning2()
    {
        $this->expectWarning();

        Silly::CAULIFLOWER();
    }
}
