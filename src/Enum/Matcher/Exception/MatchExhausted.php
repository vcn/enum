<?php

namespace Vcn\Lib\Enum\Matcher\Exception;

use Exception;
use RuntimeException;
use Vcn\Lib\Enum;

class MatchExhausted extends RuntimeException
{
    /**
     * @param Enum           $subject
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct(Enum $subject, $code = 0, Exception $previous = null)
    {
        parent::__construct("No value was mapped to instance {$subject}.", $code, $previous);
    }
}
