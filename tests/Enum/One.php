<?php

namespace Vcn\Lib\Enum;

use Vcn\Lib\Enum\Matcher\Match;

class One implements Match
{
    /**
     * @inheritdoc
     */
    public function get()
    {
        return 1;
    }
}
