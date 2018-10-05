<?php

namespace Vcn\Lib\Enum\Matcher\Match;

use InvalidArgumentException;
use Vcn\Lib\Enum\Matcher\Match;

class Callback implements Match
{
    /**
     * @var callable
     */
    private $thunk;

    /**
     * @param callable $callback
     *
     * @throws InvalidArgumentException If the given argument is not callable.
     */
    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            $type = is_object($callback) ? get_class($callback) : gettype($callback);

            throw new InvalidArgumentException(
                "Expected input argument to be callable, {$type} given. " .
                "If you want to provide a plain value as a surrogate, use Matcher::orElse() instead."
            );
        }

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
