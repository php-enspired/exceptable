
[Faults and Exceptable classes](https://github.com/php-enspired/exceptable/wiki/API:-The-Spl-Fault-Class) based on each of PHP's built-in SPL exceptions are provided. These provide convenient base classes to build your own Exceptables from that can also be handled in an exceptable-agnostic way.

For more information about SPL Exceptions, see [the php manual](https://php.net/spl.exceptions) or [this pretty decent (if dated) WebDevEtc article](https://webdevetc.com/blog/why-you-should-use-spl-exceptions-in-php-for-better-exception-handling).

The Spl _Exceptables_ extend from the built-in Spl Exceptions, and so can be caught using their corresponding Spl Exception class. Each Spl _Exceptable_ also has a default error code for generic use, and so in a pinch can be used "out-of-the-box."

```php
<?php
use at\exceptable\Spl\SplFault;

throw (SplFault::RuntimeError)();
// throws:
//  at\exceptable\Spl\RuntimeException<SplFault::RuntimeError>
```
_...meanwhile_
```php
<?php
// note, this is php's built-in SPL type, not the exceptable type
use RuntimeException;

try {
  require "that-file.php";
} catch (RuntimeException $e) {
  echo $e->getMessage();
  // prints:
  //  at\exceptable\Spl\RuntimeException: at\exceptable\Spl\SplFault.RuntimeError
}
```

The better approach is to use Spl exceptables as base classes for your application's own Exception types, with your own Fault cases.

```php
<?php
use at\exceptable\ {
  Spl\InvalidArgumentException,
  Spl\LogicException
};

enum AdventureFault implements Fault {
  use IsFault;

  case Bogus;
  case MostBogus;

  // you can change what exceptable type your faults are thrown as
  protected function exceptableType() : string {
    return match ($this) {
      self::Bogus => InvalidArgumentException::class,
      self::MostBogus => LogicException::class
    };
  }
}

throw (AdventureFault::Bogus)();
// throws:
//  at\exceptable\Spl\InvalidArgumentException<AdventureFault::Bogus>

throw (AdventureFault::MostBogus)();
// throws:
//  at\exceptable\Spl\LogicException<AdventureFault::MostBogus>
```
