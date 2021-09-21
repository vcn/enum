<?php

/** @noinspection PhpStatementHasEmptyBodyInspection */

namespace Vcn\Lib\PHPStan;

use stdClass;
use Vcn\Lib\EnumTest\Color;

class InferenceTest
{
    public function test(): void
    {
        $red = Color::RED();
        $val =
            $red
                ->when(Color::RED(), 'red')
                ->when(Color::RED(), 1)
                ->when(Color::RED(), null)
                ->whenDo(Color::RED(), fn () => true)
                ->orElse(new stdClass());

        if (is_string($val)) {
        } elseif (is_int($val)) {
        } elseif (is_null($val)) {
        } elseif (is_bool($val)) {
        } elseif ($val instanceof stdClass) {
        }

        $val =
            $red
                ->when(Color::RED(), 'red')
                ->when(Color::RED(), 1)
                ->when(Color::RED(), null)
                ->whenDo(Color::RED(), fn () => true)
                ->orElseDo(fn () => new stdClass());

        if (is_string($val)) {
        } elseif (is_int($val)) {
        } elseif (is_null($val)) {
        } elseif (is_bool($val)) {
        } elseif ($val instanceof stdClass) {
        }

        $val =
            $red
                ->when(Color::RED(), 'red')
                ->when(Color::RED(), 1)
                ->whenDo(Color::RED(), fn () => true)
                ->orElseDoNothing();

        if (is_string($val)) {
        } elseif (is_int($val)) {
        } elseif (is_null($val)) {
        } elseif (is_bool($val)) {
        }
    }
}
