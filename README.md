# enum

Enumeration data types with pattern match operations.

## Quickstart

`composer require vcn/enum`

```php
<?php

use Vcn\Lib\Enum;

/**
 * @method static Fruit APPLE()
 * @method static Fruit BANANA()
 */
class Fruit extends Enum
{
    protected const APPLE  = 0;
    protected const BANANA = 0;
}

Fruit::APPLE()->equals(Fruit::BANANA()); // false
Fruit::APPLE()->equals(Fruit::APPLE()); // true

Fruit::APPLE()
    ->when(Fruit::APPLE(), 'apple')
    ->when(Fruit::BANANA(), 'banana')
    ->get(); // 'apple'
```

## Motivation

An enum (or enumeration), is a data construct describing a value that can be exactly one of a distinct, predefined set of values.

We could for instance model fruit - where we only consider apples and bananas as fruit - as follows (using ADTs):

```haskell
data Fruit = Apple | Banana
```

A fruit is either an apple or banana - not both, nor neither.

Here `Fruit` is the enumerable type and `Apple` and `Banana` are its labels (its possible values, or instances).

This implementation tries to come close to the expressive power above.

## Usage

```haskell
data Fruit = Apple | Banana
```

To express the above using this implementation, firstly extend `Enum` to define `Fruit`:

```php
<?php
 
final class Fruit extends Enum {}
```

(Making the class final is recommended, as further inheritance is not supported and will produce warnings.)         

Then define the labels as constant members of `Fruit`:

```php
<?php

final class Fruit extends Enum {
    protected const APPLE  = 0;
    protected const BANANA = 0;
}
```

(The constant values (0s) are meaningless, but required by PHP.)

This will expose the labels as magic static methods `Fruit::APPLE()` and `Fruit::BANANA()`.
They serve as the constructors of the corresponding labels.
It is recommended to annotate them as class members in the docblock:

```php
<?php

/**
 * @method static Fruit APPLE()
 * @method static Fruit BANANA()
 */
final class Fruit extends Enum {
    protected const APPLE  = 0;
    protected const BANANA = 0;
}
```

Now you can instantiate either label:

```php
<?php

$banana = Fruit::APPLE();
$apple  = Fruit::BANANA();
```

You can test for equality:

```php
<?php

Fruit::APPLE()->equals(Fruit::BANANA()); // false
Fruit::APPLE()->equals(Fruit::APPLE()); // true
```

You can pattern match on labels:

```php
<?php

Fruit::APPLE()
    ->when(Fruit::APPLE(), 'apple')
    ->when(Fruit::BANANA(), 'banana')
    ->get(); // 'apple'
```

You can stringify and unstringify label names:

```php
<?php

Fruit::APPLE()->getName(); // 'APPLE'

Fruit::byName('APPLE'); // Fruit::APPLE()
```

You can check a collection for exhaustiveness:

```php
<?php

Fruit::isExhaustive([Fruit::APPLE()]); // false

Fruit::isExhaustive([
    Fruit::APPLE(),
    Fruit::BANANA()
]); // true
```
