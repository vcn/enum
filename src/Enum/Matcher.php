<?php

namespace Vcn\Lib\Enum;

use InvalidArgumentException;
use Vcn\Lib\Enum;

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
     */
    private Enum $subject;

    /**
     * @phpstan-var array<callable(): TResult>
     *
     * @var callable[] Map String Match
     */
    private array $callables = [];

    /**
     * @phpstan-var callable(): TResult
     */
    private $surrogate;

    /**
     * Use `Enum::when()` instead.
     *
     * @phpstan-param TEnum $subject
     *
     * @see Enum::when()
     *
     * @internal
     */
    public function __construct(Enum $subject)
    {
        $this->subject   = $subject;
        $this->surrogate = function () use ($subject) {
            throw new Matcher\Exception\MatchExhausted($subject);
        };
    }

    /**
     * @template UResult
     *
     * @phpstan-param TEnum $enum
     * @phpstan-param callable(): TResult|UResult $callable
     *
     * @phpstan-return Matcher<TEnum, TResult|UResult>
     *
     * @return Matcher
     */
    private function whenMatch(Enum $enum, callable $callable): Matcher
    {
        if (!$enum instanceof $this->subject) {
            $classSubject  = get_class($this->subject);
            $classArgument = get_class($enum);

            throw new InvalidArgumentException(
                "This map is already mapping an instance of {$classSubject}, " .
                "yet this invocation is trying to map an instance of {$classArgument}."
            );
        }

        if (array_key_exists($enum->getName(), $this->callables)) {
            throw new InvalidArgumentException(
                "This map has already mapped instance {$enum} to a value."
            );
        }

        $this->callables[$enum->getName()] = $callable;

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
     * @template UResult
     *
     * @phpstan-param TEnum $enum
     * @phpstan-param TResult|UResult $value
     *
     * @phpstan-return Matcher<TEnum, TResult|UResult>
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
        return $this->whenMatch($enum, fn () => $value);
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
     * @template UResult
     *
     * @phpstan-param TEnum $enum
     * @phpstan-param callable(): (TResult|UResult) $callable
     *
     * @phpstan-return Matcher<TEnum, TResult|UResult>
     *
     * @return Matcher
     */
    public function whenDo(Enum $enum, callable $callable): Matcher
    {
        return $this->whenMatch($enum, $callable);
    }

    /**
     * @phpstan-param callable(): TResult $callable
     * @phpstan-return TResult
     *
     * @return mixed
     */
    private function orElseMatch(callable $callable)
    {
        return ($this->callables[$this->subject->getName()] ?? $callable)();
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
     * @template UResult
     *
     * @phpstan-param TResult|UResult $value
     * @phpstan-return TResult|UResult
     *
     * @param mixed $value The surrogate value to return if the instance has not been mapped to anything
     *
     * @return mixed
     */
    public function orElse($value)
    {
        return $this->orElseMatch(fn () => $value);
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
     * @template UResult
     *
     * @phpstan-param callable(): TResult|UResult $callable
     * @phpstan-return TResult|UResult
     *
     * @param callable $callable
     *
     * @return mixed
     */
    public function orElseDo(callable $callable)
    {
        return $this->orElseMatch($callable);
    }

    /**
     * `Matcher::orElseDo` passing `noop`.
     *
     * @phpstan-return null|TResult
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
        return $this->orElseDo($this->surrogate);
    }
}
