<?php

namespace Vcn\Lib\Enum\Exception;

use Exception;
use InvalidArgumentException;
use Vcn\Lib\Enum;

class InvalidInstance extends Exception
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $invalidName;

    /**
     * @param string         $className       FQCN of the enum.
     * @param string         $invalidInstance The enum constant that could not be found.
     * @param int            $code
     * @param null|Exception $previous
     */
    public function __construct($className, $invalidInstance, $code = 0, Exception $previous = null)
    {
        if (!in_array(Enum::getEnumBaseClass(), class_parents($className))) {
            $enumBaseClass = Enum::getEnumBaseClass();

            throw new InvalidArgumentException(
                "First argument ({$className}) is expected to be an instance of {$enumBaseClass}."
            );
        }

        $this->className   = $className;
        $this->invalidName = $invalidInstance;

        /** @noinspection PhpUndefinedMethodInspection */
        $validInstances = implode(
            ", ",
            array_map(
                function (Enum $e) {
                    /** @noinspection PhpInternalEntityUsedInspection */
                    return $e->__toString();
                },
                $className::getAllInstances()
            )
        );

        parent::__construct(
            sprintf(
                "%s::%s() is not a valid instance for %s. " .
                "Its valid instances are: %s.",
                $className,
                $invalidInstance,
                $className,
                $validInstances
            ),
            $code,
            $previous
        );
    }

    /**
     * Alias of `getClassName`, use that instead.
     *
     * @return string
     *
     * @see getClassName()
     *
     * @deprecated
     */
    public function getEnumClass()
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Alias of `getInvalidName`, use that instead.
     *
     * @return string
     *
     * @see getInvalidName()
     *
     * @deprecated
     */
    public function getUndefinedName()
    {
        return $this->invalidName;
    }

    /**
     * @return string
     */
    public function getInvalidName()
    {
        return $this->invalidName;
    }

    /**
     * @return Enum[]
     */
    public function getValidInstances()
    {
        $className = $this->className;

        /** @noinspection PhpUndefinedMethodInspection */
        return $className::getAllInstances();
    }
}
