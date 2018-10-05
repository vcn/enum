<?php

namespace Vcn\Lib\Enum;

use InvalidArgumentException;
use Vcn\Lib\Enum;
use Vcn\Lib\Enum\Matcher\Match;

/**
 * @internal
 */
final class Matcher
{
    /**
     * @var Enum
     */
    private $subject;

    /**
     * @var Match[] Map String Match
     */
    private $matches = array();

    /**
     * @var Match
     */
    private $surrogate;

    /**
     * Use `Enum::when()` instead.
     *
     * @param Enum $subject
     *
     * @see Enum::when()
     *
     * @internal
     */
    public function __construct(Enum $subject)
    {
        $this->subject   = $subject;
        $this->surrogate = new Match\Callback(
            function () use ($subject) {
                throw new Matcher\Exception\MatchExhausted($subject);
            }
        );
    }

    /**
     * @param Enum  $enum
     * @param Match $match
     *
     * @return Matcher
     */
    private function whenMatch(Enum $enum, Match $match)
    {
        if (!$enum instanceof $this->subject) {
            $classSubject  = get_class($this->subject);
            $classArgument = get_class($enum);

            throw new InvalidArgumentException(
                "This map is already mapping an instance of {$classSubject}, " .
                "yet this invocation is trying to map an instance of {$classArgument}."
            );
        }

        if (array_key_exists($enum->getName(), $this->matches)) {
            throw new InvalidArgumentException(
                "This map has already mapped instance {$enum} to a value."
            );
        }

        /** @noinspection PhpInternalEntityUsedInspection */
        $this->matches[$enum->getName()] = $match;

        return $this;
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
     * @param Enum  $enum  The Enum instance, must be an instance of the subject of this map.
     * @param mixed $value The value to map the given instance to.
     *
     * @return Matcher
     * @throws InvalidArgumentException If the given Enum is not an instance of the subject of this map, or if the given
     *                                  instance has already been mapped.
     */
    public function when(Enum $enum, $value)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        return $this->whenMatch($enum, new Match\Value($value));
    }

    /**
     * Like `Matcher::when()`, but let a callback provide the value.
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
     * @param Enum     $enum
     * @param callable $callable
     *
     * @return Matcher
     * @throws InvalidArgumentException If the given argument is not callable.
     */
    public function whenDo(Enum $enum, $callable)
    {
        if (!is_callable($callable)) {
            $type = is_object($callable) ? get_class($callable) : gettype($callable);

            throw new InvalidArgumentException(
                "Expected input argument to be callable, {$type} given. " .
                "If you want to match a plain value, use Matcher::when() instead."
            );
        }

        return $this->whenMatch($enum, new Match\Callback($callable));
    }

    /**
     * @param Match $match
     *
     * @return mixed
     */
    private function orElseMatch(Match $match)
    {
        return array_key_exists($this->subject->getName(), $this->matches)
            ? $this->matches[$this->subject->getName()]->get()
            : $match->get();
    }

    /**
     * Provides the value the instance of the mapped subject is mapped to, or a given surrogate value if the instance
     * has not been mapped to anything.
     *
     * <br/>
     *
     * For example:
     *
     * <br/>
     *
     * ```
     * $fruit
     *     ->when(Fruit::BANANA(), 'banana')
     *     ->when(Fruit::APPLE(), 'apple')
     *     ->orElse('Some other fruit I did not know about?');
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
     * `'apple'` if `$fruit = Fruit::APPLE()` or
     *
     * <br/>
     *
     * `'Some other fruit I did not know about?' if $fruit = Fruit::EGGPLANT();`
     *
     * @param mixed $value The surrogate value to return if the instance has not been mapped to anything
     *
     * @return mixed
     */
    public function orElse($value)
    {
        return $this->orElseMatch(new Match\Value($value));
    }

    /**
     * Like `Matcher::orElse()`, but let a callback provide the surrogate.
     *
     * <br/>
     *
     * Use this to lazily provide a surrogate, or produce side effects when no match is found:
     *
     * <br/>
     *
     * ```
     * $fruit
     *     ->when(Fruit::BANANA(), 'banana')
     *     ->when(Fruit::APPLE(), 'apple')
     *     ->orElseDo(
     *         function () {
     *             printf('Expected either a banana or apple.');
     *         }
     *     );
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
     * `'apple'` if `$fruit = Fruit::APPLE()` or
     *
     * <br/>
     *
     * an invocation of the callback if `$fruit = Fruit::EGGPLANT()`
     *
     * <br/>
     *
     * It is <strong>discouraged</strong> to throw checked exceptions since PHPStorm can't infer the corresponding
     * throws clause on this method.
     *
     * @param callable $callable
     *
     * @return mixed
     * @throws InvalidArgumentException If the given argument is not callable.
     */
    public function orElseDo($callable)
    {
        if (!is_callable($callable)) {
            $type = is_object($callable) ? get_class($callable) : gettype($callable);

            throw new InvalidArgumentException(
                "Expected input argument to be callable, {$type} given. " .
                "If you want to provide a plain value as a surrogate, use Matcher::orElse() instead."
            );
        }

        return $this->orElseMatch(new Match\Callback($callable));
    }

    /**
     * `Matcher::orElseDo` passing `noop`.
     *
     * @return mixed
     */
    public function orElseDoNothing()
    {
        return $this->orElseDo(
            function () {
                ;
            }
        );
    }

    /**
     * Provides the value the instance of the mapped subject is mapped to, or throws a runtime exception if the instance
     * has not been mapped to anything.
     *
     * <br/>
     *
     * For example:
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
     * `'apple'` if `$fruit = Fruit::APPLE()` or
     *
     * <br/>
     *
     * a thrown exception if `$fruit = Fruit::EGGPLANT()`
     *
     * @return mixed
     * @throws Enum\Matcher\Exception\MatchExhausted
     */
    public function get()
    {
        return array_key_exists($this->subject->getName(), $this->matches)
            ? $this->matches[$this->subject->getName()]->get()
            : $this->surrogate->get();
    }
}
