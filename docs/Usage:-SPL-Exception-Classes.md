## Spl _Exceptables_ and Faults

[Faults and Exceptable classes](https://github.com/php-enspired/exceptable/wiki/API:-The-Spl-Fault-Class) that extend each of PHP's built-in SPL exceptions are provided. These provide convenient base classes to build your own Exceptables from that can also be handled in an exceptable-agnostic way.

For more information about SPL Exceptions, see [the php manual](https://php.net/spl.exceptions) or [this pretty decent (if dated) WebDevEtc article](https://webdevetc.com/blog/why-you-should-use-spl-exceptions-in-php-for-better-exception-handling).

## Examples

The Spl _Exceptables_ extend from the built-in Spl Exceptions, and so can be caught using their corresponding Spl Exception class. Each Spl _Exceptable_ also has a default error code for generic use, and so in a pinch can be used "out-of-the-box."
```php
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

The Spl _Exceptables_ are better used as base classes for your application's own Exception classes, with your own Fault cases.

```php
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
