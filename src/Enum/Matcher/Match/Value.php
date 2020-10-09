<?php

namespace Vcn\Lib\Enum\Matcher\Match;

use Vcn\Lib\Enum\Matcher\Match;

/**
 * @template T
 * @implements Match<T>
 */
class Value implements Match
{
    /**
     * @phpstan-var T
     * @var mixed
     */
    private $value;

    /**
     * @phpstan-param T $value
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        return $this->value;
    }
}
