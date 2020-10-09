<?php

namespace Vcn\Lib\Enum\Matcher;

/**
 * @template TResult
 */
interface Match
{
    /**
     * Resolve this match and return the result
     *
     * @phpstan-return TResult
     * @return mixed
     */
    public function get();
}
