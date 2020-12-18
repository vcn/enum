<?php

namespace Vcn\Lib\Enum;

use Vcn\Lib\Enum\Matcher\Value;

class One implements Value
{
    /**
     * @inheritdoc
     */
    public function get()
    {
        return 1;
    }
}
