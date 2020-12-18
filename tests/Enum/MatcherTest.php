<?php

namespace Vcn\Lib\Enum;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TypeError;
use Vcn\Lib\Enum;
use Vcn\Lib\EnumTest\Fruit;
use Vcn\Lib\EnumTest\Vegetables;

class MatcherTest extends TestCase
{
    /**
     * @test
     */
    public function testGet()
    {
        $matcher = new Matcher(Vegetables::CAULIFLOWER());

        $matcher
            ->when(Vegetables::CAULIFLOWER(), 0)
            ->when(Vegetables::SPINACH(), 1);

        $this->assertSame(0, $matcher->get());

        $matcher = new Matcher(Vegetables::SPINACH());

        $matcher
            ->when(Vegetables::CAULIFLOWER(), 0)
            ->when(Vegetables::SPINACH(), 1);

        $this->assertSame(1, $matcher->get());

        $matcher = new Matcher(Vegetables::SPINACH());

        $matcher
            ->when(Vegetables::CAULIFLOWER(), 1)
            ->when(Vegetables::SPINACH(), null);

        $this->assertNull($matcher->get());
    }

    /**
     * @test
     */
    public function testGetOnIncompleteMap()
    {
        $matcher = new Matcher(Vegetables::SPINACH());

        $this->expectException(Enum\Matcher\Exception\MatchExhausted::class);

        $matcher
            ->when(Vegetables::CAULIFLOWER(), 0)
            ->get();
    }

    /**
     * @test
     */
    public function testOrElse()
    {
        $matcher = new Matcher(Vegetables::CAULIFLOWER());

        $matcher
            ->when(Vegetables::CAULIFLOWER(), 0);

        $this->assertSame(0, $matcher->orElse(1));

        $matcher = new Matcher(Vegetables::SPINACH());

        $matcher
            ->when(Vegetables::CAULIFLOWER(), 0);

        $this->assertSame(1, $matcher->orElse(1));
    }

    /**
     * @test
     */
    public function testOrElseDo()
    {
        $matcher = new Matcher(Vegetables::CAULIFLOWER());

        $actual =
            $matcher
                ->when(Vegetables::CAULIFLOWER(), 0)
                ->orElseDo(
                    function () {
                        return 1;
                    }
                );

        $this->assertSame(0, $actual);

        $matcher = new Matcher(Vegetables::SPINACH());

        $actual =
            $matcher
                ->when(Vegetables::CAULIFLOWER(), 0)
                ->orElseDo(
                    function () {
                        return 1;
                    }
                );

        $this->assertSame(1, $actual);
    }

    /**
     * @test
     */
    public function testOrElseDoWithValue()
    {
        $matcher = new Matcher(Vegetables::CAULIFLOWER());

        $this->expectException(TypeError::class);

        /** @noinspection PhpParamsInspection */
        $matcher
            ->orElseDo(1);
    }

    /**
     * @test
     */
    public function testInconsistentMap()
    {
        $matcher = new Matcher(Vegetables::CAULIFLOWER());

        $this->expectException(InvalidArgumentException::class);

        $matcher
            ->when(Vegetables::CAULIFLOWER(), 0)
            ->when(Fruit::BANANA(), 1);
    }

    /**
     * @test
     */
    public function testDoubleMap()
    {
        $matcher = new Matcher(Vegetables::CAULIFLOWER());

        $this->expectException(InvalidArgumentException::class);

        $matcher
            ->when(Vegetables::CAULIFLOWER(), 0)
            ->when(Vegetables::CAULIFLOWER(), 0);
    }

    /**
     * @test
     */
    public function testDoubleMapWithNull()
    {
        $matcher = new Matcher(Vegetables::CAULIFLOWER());

        $this->expectException(InvalidArgumentException::class);

        $matcher
            ->when(Vegetables::CAULIFLOWER(), null)
            ->when(Vegetables::CAULIFLOWER(), null);
    }

    /**
     * @test
     */
    public function testWhenDoOnValue()
    {
        $matcher = new Matcher(Vegetables::CAULIFLOWER());

        $this->expectException(TypeError::class);

        /** @noinspection PhpParamsInspection */
        $matcher
            ->whenDo(Vegetables::CAULIFLOWER(), 1);
    }
}
