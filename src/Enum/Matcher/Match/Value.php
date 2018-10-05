<?php

namespace Vcn\Lib\Enum\Matcher\Match;

use Vcn\Lib\Enum\Matcher\Match;

class Value implements Match
{
    /**
     * @var mixed
     */
    private $value;

    /**
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
