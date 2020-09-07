<?php

namespace Vcn\Lib\Enum;

use InvalidArgumentException;
use Vcn\Lib\Enum;
use Vcn\Lib\Enum\Matcher\Match;

/**
 * @template TEnum of Enum
 * @template TResult
 *
 * @internal
 */
final class Matcher
{
    /**
     * @phpstan-var TEnum
     *
     * @var Enum
     */
    private $subject;

    /**
     * @phpstan-var array<Match<TResult>>
     *
     * @var Match[] Map String Match
     */
    private $matches = array();

    /**
     * @phpstan-var Match<TResult>
     *
     * @var Match
     */
    private $surrogate;

    /**
     * Use `Enum::when()` instead.
     *
     * @phpstan-param TEnum $subject
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
     * @phpstan-param TEnum $enum
     * @phpstan-param Match<TResult> $match
     *
     * @phpstan-return Matcher<TEnum, TResult>
     *
     * @param Enum  $enum
     * @param Match $match
     *
     * @return Matcher
     */
    private function whenMatch(Enum $enum, Match $match): Matcher
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
     * @phpstan-param TEnum $enum
     * @phpstan-param TResult $value
     *
     * @phpstan-return Matcher<TEnum, TResult>
     *
     * @param Enum  $enum  The Enum instance, must be an instance of the subject of this map.
     * @param mixed $value The value to map the given instance to.
     *
     * @return Matcher
     * @throws InvalidArgumentException If the given Enum is not an instance of the subject of this map, or if the given
     *                                  instance has already been mapped.
     */
    public function when(Enum $enum, $value): Matcher
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
     * @phpstan-param TEnum $enum
     * @phpstan-param callable(): TResult $callable
     *
     * @phpstan-return Matcher<TEnum, TResult>
     *
     * @param Enum     $enum
     * @param callable $callable
     *
     * @return Matcher
     */
    public function whenDo(Enum $enum, callable $callable): Matcher
    {
        return $this->whenMatch($enum, new Match\Callback($callable));
    }

    /**
     * @phpstan-param Match<TResult> $match
     * @phpstan-return TResult
     *
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
     * @phpstan-param TResult $value
     * @phpstan-return TResult
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
     * @phpstan-param callable(): TResult
     * @phpstan-return TResult
     *
     * @param callable $callable
     *
     * @return mixed
     */
    public function orElseDo(callable $callable)
    {
        return $this->orElseMatch(new Match\Callback($callable));
    }

    /**
     * `Matcher::orElseDo` passing `noop`.
     *
     * @phpstan-return TResult
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
     * @phpstan-return TResult
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
