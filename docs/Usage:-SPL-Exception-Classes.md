Exceptable classes that extend each of PHP's built-in SPL Exceptions are provided. These provide convenient base classes to build your own Exceptables from that can also be handled in an exceptable-agnostic way.

## Spl _Exceptables_

| Exceptable Class                           | Default Error Code    | Extends From             |
|-------------------------------------------:|:----------------------|--------------------------|
|           at\exceptable\Spl\LogicException | ::PROGRAM_LOGIC_ERROR | LogicException           |
| at\exceptable\Spl\BadFunctionCallException | ::BAD_FUNCTION_CALL   | BadFunctionCallException |
|   at\exceptable\Spl\BadMethodCallException | ::BAD_METHOD_CALL     | BadMethodCallException   |
|          at\exceptable\Spl\DomainException | ::DOMAIN_ERROR        | DomainException          |
| at\exceptable\Spl\InvalidArgumentException | ::INVALID_ARGUMENT    | InvalidArgumentException |
|          at\exceptable\Spl\LengthException | ::LENGTH_ERROR        | LengthException          |
|      at\exceptable\Spl\OutOfRangeException | ::OUT_OF_RANGE        | OutOfRangeException      |
|         at\exceptable\Spl\RuntimeException | ::RUNTIME_ERROR       | RuntimeException         |
|     at\exceptable\Spl\OutOfBoundsException | ::OUT_OF_BOUNDS       | OutOfBoundsException     |
|        at\exceptable\Spl\OverflowException | ::OVERFLOW            | OverflowException        |
|           at\exceptable\Spl\RangeException | ::OUT_OF_RANGE        | RangeException           |
|       at\exceptable\Spl\UnderflowException | ::UNDERFLOW           | UnderflowException       |
| at\exceptable\Spl\UnexpectedValueException | ::UNEXPECTED_VALUE    | UnexpectedValueException |

For more information about SPL Exceptions, see [the php manual](https://php.net/spl.exceptions) or [this pretty decent WebDevEtc article](https://webdevetc.com/blog/why-you-should-use-spl-exceptions-in-php-for-better-exception-handling).

## Examples

The Spl _Exceptables_ extend from the built-in Spl Exceptions, and so can be caught using their corresponding Spl Exception class. Each Spl _Exceptable_ also has a default error code for generic use, and so in a pinch can be used "out-of-the-box."
```
<?php
namespace Example1;

use RuntimeException;
use at\exceptable\Spl\RuntimeException as RuntimeExceptable;

try {
  throw new RuntimeExceptable();
} catch (RuntimeException $e) {
  echo $e->getMessage();  // Runtime error
}
```

The Spl _Exceptables_ are better used as base classes for your application's own Exception classes, with your own error codes.

When extending an Spl _Exceptable_ class, it's best to preserve the _Exceptable_'s base error information by adding its `INFO` to your class's `INFO` using the array union operator (`+`).
```
<?php
namespace Example2;

use InvalidArgumentException;
use at\exceptable\Spl\InvalidArgumentException as InvalidArgumentExceptable;

class MyInvalidArgument extends InvalidArgumentExceptable {

  public const MOST_BOGUS = 1;
  public const INFO = parent::INFO + [
    self::MOST_BOGUS => [
      "message" => "A Most Bogus argument",
      "format" => "Most Bogus - expected a multiple of {factor}; {number} provided"
    ]
  ];
}

function divideByTen(int $number) : int {
  if ($number % 10 !== 0) {
    throw new MyInvalidArgument(MyInvalidArgument::MOST_BOGUS, ["number" => $number, "factor" => 10]);
  }
}

try {
  divideByTen(15);
} catch (InvalidArgumentException $e) {
  echo $e->getMessage();  // Most Bogus - expected a multiple of 10; 15 provided
}
```
