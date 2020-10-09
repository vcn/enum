<?php

namespace Vcn\Lib\Enum\Matcher\Match;

use Vcn\Lib\Enum\Matcher\Match;

/**
 * @template T
 * @implements Match<T>
 */
class Callback implements Match
{
    /**
     * @phpstan-var callable(): T
     *
     * @var callable
     */
    private $thunk;

    /**
     * @phpstan-param callable(): T $callback
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->thunk = $callback;
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        $thunk = $this->thunk;

        return $thunk();
    }
}
