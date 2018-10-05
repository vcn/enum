<?php

namespace Vcn\Lib\Enum;

use Exception;
use PHPUnit_Framework_TestCase;
use Vcn\Lib\EnumTest\Fruit;
use Vcn\Lib\EnumTest\Vegetables;

class MatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testGet()
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $matcher = new Matcher(Vegetables::CAULIFLOWER());

        $matcher
            ->when(Vegetables::CAULIFLOWER(), 0)
            ->when(Vegetables::SPINACH(), 1);

        $this->assertSame(0, $matcher->get());

        /** @noinspection PhpInternalEntityUsedInspection */
        $matcher = new Matcher(Vegetables::SPINACH());

        $matcher
            ->when(Vegetables::CAULIFLOWER(), 0)
            ->when(Vegetables::SPINACH(), 1);

        $this->assertSame(1, $matcher->get());

        /** @noinspection PhpInternalEntityUsedInspection */
        $matcher = new Matcher(Vegetables::SPINACH());

        $matcher
            ->when(Vegetables::CAULIFLOWER(), 1)
            ->when(Vegetables::SPINACH(), null);

        $this->assertNull($matcher->get());
    }

    /**
     * @test
     * @expectedException \Vcn\Lib\Enum\Matcher\Exception\MatchExhausted
     */
    public function testGetOnIncompleteMap()
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $matcher = new Matcher(Vegetables::SPINACH());

        $matcher
            ->when(Vegetables::CAULIFLOWER(), 0);

        $matcher->get();
    }

    /**
     * @test
     */
    public function testOrElse()
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $matcher = new Matcher(Vegetables::CAULIFLOWER());

        $matcher
            ->when(Vegetables::CAULIFLOWER(), 0);

        $this->assertSame(0, $matcher->orElse(1));

        /** @noinspection PhpInternalEntityUsedInspection */
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
        /** @noinspection PhpInternalEntityUsedInspection */
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

        /** @noinspection PhpInternalEntityUsedInspection */
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
     * @expectedException \InvalidArgumentException
     */
    public function testOrElseDoWithValue()
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $matcher = new Matcher(Vegetables::CAULIFLOWER());

        /** @noinspection PhpParamsInspection */
        $matcher
            ->orElseDo(1);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function testInconsistentMap()
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $matcher = new Matcher(Vegetables::CAULIFLOWER());

        $matcher
            ->when(Vegetables::CAULIFLOWER(), 0)
            ->when(Fruit::BANANA(), 1);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function testDoubleMap()
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $matcher = new Matcher(Vegetables::CAULIFLOWER());

        $matcher
            ->when(Vegetables::CAULIFLOWER(), 0)
            ->when(Vegetables::CAULIFLOWER(), 0);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function testDoubleMapWithNull()
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $matcher = new Matcher(Vegetables::CAULIFLOWER());

        $matcher
            ->when(Vegetables::CAULIFLOWER(), null)
            ->when(Vegetables::CAULIFLOWER(), null);
    }

    /**
     * @test
     */
    public function testWhenDo()
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $matcher = new Matcher(Vegetables::CAULIFLOWER());

        $matcher
            ->whenDo(
                Vegetables::CAULIFLOWER(),
                function () {
                    return 0;
                }
            )
            ->whenDo(
                Vegetables::SPINACH(),
                function () {
                    throw new Exception('This should not be evaluated.');
                }
            );

        $this->assertSame(0, $matcher->get());
    }

    /**
     * @test
     */
    public function testOrElseDoNothing()
    {

        /** @noinspection PhpInternalEntityUsedInspection */
        $matcher = new Matcher(Vegetables::CAULIFLOWER());

        $matcher
            ->whenDo(
                Vegetables::SPINACH(),
                function () {
                    throw new Exception('This should not be evaluated.');
                }
            )
            ->orElseDoNothing();
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function testWhenDoOnValue()
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $matcher = new Matcher(Vegetables::CAULIFLOWER());

        /** @noinspection PhpParamsInspection */
        $matcher
            ->whenDo(Vegetables::CAULIFLOWER(), 1);
    }
}
