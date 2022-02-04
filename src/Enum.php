<?php

namespace Vcn\Lib;

use InvalidArgumentException;
use JsonSerializable;
use LogicException;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

abstract class Enum implements JsonSerializable
{
    /**
     * @var string
     */
    private $constName;

    /**
     * @var string[][]
     */
    private static $constantsArray = [];

    /**
     * @var static[][]
     */
    private static $instancesArray = [];

    /**
     * @var bool[]
     */
    private static $parentIsEnumArray = [];

    /**
     * @param string $name
     */
    private function __construct($name)
    {
        $this->constName = $name;
    }

    /**
     * Attempts to instantiate an Enum from a label name, or return null if the name is not valid.
     *
     * For example:
     *
     * ```
     * Fruit::tryByName('APPLE'); // Fruit::APPLE()
     * Fruit::tryByName('BANANA'); // Fruit::BANANA()
     * Fruit::tryByName('UNKNOWN'); // null
     * ```
     *
     * @param string $name
     *
     * @return null|static
     */
    final public static function tryByName($name)
    {
        $instances = &static::getInstances();

        if (!array_key_exists($name, $instances)) {
            $constants = static::getAllNames();

            if (!in_array($name, $constants)) {
                return null;
            }

            $instances[$name] = new static($name);
        }

        return $instances[$name];
    }

    /**
     * Attempts to instantiate an Enum from a label name.
     *
     * For example:
     *
     * ```
     * Fruit::byName('APPLE'); // Fruit::APPLE()
     * Fruit::byName('BANANA'); // Fruit::BANANA()
     * Fruit::byName('UNKNOWN'); // exception
     * ```
     *
     * @param string $name
     *
     * @return static
     *
     * @throws Enum\Exception\InvalidInstance
     */
    final public static function byName($name)
    {
        $instance = self::tryByName($name);

        if ($instance === null) {
            throw new Enum\Exception\InvalidInstance(get_called_class(), $name);
        }

        return $instance;
    }

    /**
     * Gets this instance's label name.
     *
     * <br/>
     *
     * For example:
     *
     * <br/>
     *
     * ```
     * Fruit::APPLE()->getName(); // 'APPLE'
     * Fruit::BANANA()->getName(); // 'BANANA'
     * ```
     *
     * @return string
     */
    final public function getName()
    {
        return $this->constName;
    }

    /**
     * Gets an array of all possible instances.
     *
     * <br/>
     *
     * For example:
     *
     * <br/>
     *
     * ```
     * Fruit::getAllInstances(); // [Fruit::APPLE(), Fruit::BANANA()]
     * ```
     *
     * @return static[]
     */
    public static function getAllInstances()
    {
        try {
            $instances = array();

            foreach (static::getAllNames() as $name) {
                $instances[] = static::byName($name);
            }

            return $instances;
        } catch (Enum\Exception\InvalidInstance $e) {
            throw new LogicException("All valid names should produce valid instances?");
        }
    }

    /**
     * @return static[]
     */
    private static function &getInstances()
    {
        $className = get_called_class();

        if (!array_key_exists($className, self::$instancesArray)) {
            self::$instancesArray[$className] = array();
        }

        return self::$instancesArray[$className];
    }

    /**
     * Gets an array of all possible instances' label names.
     *
     * <br/>
     *
     * For example:
     *
     * <br/>
     *
     * ```
     * Fruit::getAllNames(); // ['APPLE', 'BANANA']
     * ```
     *
     * @return string[]
     */
    public static function getAllNames()
    {
        $className = get_called_class();

        if (!array_key_exists($className, self::$constantsArray)) {
            try {
                $class = new ReflectionClass($className);
            } catch (ReflectionException $e) {
                throw new RuntimeException($e->getMessage(), (int)$e->getCode(), $e);
            }

            self::$constantsArray[$className] = array_keys($class->getConstants());
        }

        return self::$constantsArray[$className];
    }

    /**
     * Returns the FQCN of Enum.
     * `Fruit::getEnumBaseClass` is equivalent to `Enum::class`.
     *
     * @phpstan-return class-string<self>
     *
     * @return string
     */
    final public static function getEnumBaseClass()
    {
        return __CLASS__;
    }

    /**
     * Returns the specific FQCN of the specific Enum.
     * `Fruit::getClass()` is equivalent to `Fruit::class`.
     *
     * @phpstan-return class-string<static>
     *
     * @return string
     */
    final public static function getClass(): string
    {
        return get_called_class();
    }

    /**
     * Maps an Enum instance to a value.
     *
     * <br/>
     *
     * If the subject of this map is equal to the given instance, `get()` will return this value.
     *
     * <br/>
     *
     * By chaining this method, a total case distinction can be expressed, for example:
     *
     * <br/>
     *
     * ```
     * $fruit
     *     ->when(Fruit::BANANA(), 'banana')
     *     ->when(Fruit::APPLE(), 'apple')
     *     ->get();
     * ```
     *
     * <br/>
     *
     * will yield:
     *
     * <br/>
     *
     * `'banana'` if `$fruit = Fruit::BANANA()` or
     *
     * <br/>
     *
     * `'apple'` if `$fruit = Fruit::APPLE()`
     *
     * @template T
     *
     * @phpstan-param T $b
     * @phpstan-return Enum\Matcher<static, T>
     *
     * @param static $a
     * @param mixed $b
     *
     * @return Enum\Matcher
     */
    final public function when(Enum $a, $b): Enum\Matcher
    {
        $map = new Enum\Matcher($this);

        return $map->when($a, $b);
    }

    /**
     * Like `Enum::when()`, but let a callback provide the value.
     *
     * <br/>
     *
     * Use this to lazily provide mapped values, or produce side effects when matched:
     *
     * ```
     * $fruit
     *     ->whenDo(
     *         Fruit::BANANA(),
     *         function () {
     *             return new LargeHadronCollider();
     *         }
     *     )
     *     ->whenDo(
     *         Fruit::APPLE(),
     *         function () use ($logger) {
     *             $logger->critical("I don't like apples!");
     *
     *             return null;
     *         }
     *     )
     *     ->get();
     * ```
     *
     * <br/>
     *
     * will yield:
     *
     * <br/>
     *
     * `LargeHadronCollider` if `$fruit = Fruit::BANANA()` or
     *
     * <br/>
     *
     * `null` if `$fruit = Fruit::APPLE()`
     *
     * <br/>
     *
     * It is <strong>discouraged</strong> to throw checked exceptions since PHPStorm can't infer the corresponding
     * throws clauses on relevant methods.
     *
     * @template T
     * @phpstan-param callable(): T $b
     * @phpstan-return Enum\Matcher<static, T>
     *
     * @param static   $a
     * @param callable $b
     *
     * @return Enum\Matcher
     */
    final public function whenDo(Enum $a, callable $b): Enum\Matcher
    {
        $map = new Enum\Matcher($this);

        return $map->whenDo($a, $b);
    }

    /**
     * Only two Enums of the same type can be compared.
     * They are equal if their label is the same.
     *
     * <br/>
     *
     * For example:
     *
     * <br/>
     *
     * ```
     * Fruit::APPLE()->equals(Fruit::BANANA()); // false
     * Fruit::APPLE()->equals(Fruit::APPLE()); // true
     * Fruit::APPLE()->equals(VEGETABLE::SPINACH()); // exception
     * ```
     *
     * @param static $b
     *
     * @return bool
     * @throws InvalidArgumentException If $b is not of the same type as this Enum.
     */
    final public function equals(Enum $b): bool
    {
        $aClass = get_class($this);
        $bClass = get_class($b);

        if ($aClass !== $bClass) {
            throw new InvalidArgumentException("Unexpected type {$bClass} is not of type {$aClass}.");
        }

        return $b->getName() === $this->getName();
    }

    /**
     * Test if the provided collection contains all possible enum values.
     *
     * @param static[] $instances
     *
     * @return bool
     */
    final public static function isExhaustive(array $instances): bool
    {
        $allInstances = self::getAllInstances();

        $missing = array_diff($allInstances, $instances);

        return count($missing) === 0;
    }

    /**
     * Test if the provided collection contains either none or all possible enum values.
     *
     * @param static[] $instances
     *
     * @return bool
     */
    final public static function isExhaustiveOrEmpty(array $instances): bool
    {
        return count($instances) === 0 || self::isExhaustive($instances);
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return static
     *
     * @throws Enum\Exception\InvalidInstance
     *
     * @internal
     */
    final public static function __callStatic($name, array $arguments)
    {
        try {
            $className = get_called_class();

            if (!isset(self::$parentIsEnumArray[$className])) {
                $class           = new ReflectionClass($className);
                $parentClassName = $class->getParentClass()->getName();
                $enumClassName   = __CLASS__;

                if ($parentClassName !== $enumClassName) {
                    self::$parentIsEnumArray[$class->getName()] = false;
                } else {
                    self::$parentIsEnumArray[$class->getName()] = true;
                }
            }

            if (!self::$parentIsEnumArray[$className]) {
                $class           = isset($class) ? $class : new ReflectionClass($className);
                $parentClassName = $class->getParentClass()->getName();
                $enumClassName   = __CLASS__;

                trigger_error(
                    "{$class->getName()} inherits from {$parentClassName} " .
                    "which eventually inherits from {$enumClassName}. " .
                    "All children of {$enumClassName} should be final, or at least not be inherited from again.",
                    E_USER_WARNING
                );
            }

            return static::byName($name);
        } catch (ReflectionException $e) {
            throw new RuntimeException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * For debugging purposes.
     *
     * @return string
     * @internal
     */
    public function __toString(): string
    {
        return static::getClass() . '::' . $this->constName . '()';
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): mixed
    {
        return $this->getName();
    }
}
