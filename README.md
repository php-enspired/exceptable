![](https://img.shields.io/github/release/php-enspired/exceptable.svg)  ![](https://img.shields.io/badge/PHP-8.2-blue.svg?colorB=8892BF)  ![](https://img.shields.io/badge/license-GPL_3.0_only-blue.svg)

how exceptable!
===============

A lot of php code treats exceptions in an ad-hoc way: throwing a plain `Exception` instance with a message written inline. Then, when (...if?) you look at your error log, you have to start working backwards to figure out the state of the application at that time.

_Exceptables_ make exceptions exceptional. Exceptables:
- ✓ are easy to create and pass details to
- ✓ are readable by both humans and your application code
- ✓ can provide a wealth of runtime information about the state of things that led to the problem
- ✓ make it easy to add, adapt, and maintain error handling code as your application grows

More importantly, your Exceptables can include any additional information you want - values of arguments or local variables, details about state, whole objects, anything! This _context_ is good for adding details to the error message, but can also be sent to log aggregation tools, and even be used at runtime to inspect the error and recover or fail gracefully.

dependencies
------------

Requires php 8.2 or later.

ICU support requires the `intl` extension.

installation
------------

Recommended installation method is via [Composer](https://getcomposer.org/):

simply `composer require "php-enspired/exceptable:^5"`

a quick taste
-------------
```php
<?php

use at\exceptable\ {
  Error,
  IsError
};

// a simple Error, just for you
enum ProcessError : int implements Error {
  use IsError;

  case NotReady = 1;
  public const MESSAGES = [
    self::NotReady->name => "{type} is not ready (status is '{status}')"
  ];
}

class Example {
  public function __construct( public string $status ) {}
}

$example = new Example("preparing");
if ($example->status !== "ready") {
  throw (ProcessError::NotReady)([
    "type" => $example::class,
    "status" => $example->status
  ]);
}
```
outputs:
> Fatal error: Uncaught at\exceptable\Spl\RuntimeException: ProcessError.NotReady: Example is not ready (status is 'preparing')

errors as values
----------------

Having errors available to your application as normal values also means you can decide to _not_ throw exceptions.

The _Result pattern_, for example, is a functional programming approach to error handling that treats error conditions as normal, expected return values. This encourages handling error cases more carefully and closer to their source, and is also a benefit to static analysis. See [Larry Garfield's excellent article ("_A Naked Result_")](https://peakd.com/hive-168588/@crell/much-ado-about-null#anakedresult) for more.

```php
<?php

use at\exceptable\ {
  Error,
  IsError
};

enum FooError : int implements Error {
  use IsError;

  case TheyToldMeToDoIt = 1;
  public const MESSAGES = [
    self::TheyToldMeToDoIt->name => "ooh noooooooooooooooooo!"
  ];
}

function foo(bool $fail) : string|FooError {
  return $fail ?
    FooError::TheyToldMeToDoIt :
    "woooooooooooooooooo hoo!";
}

$bool = maybeTrueMaybeFalse();
$result = foo($bool);
if ($result instanceof FooError) {
  echo $result->message();
  // outputs "ooh noooooooooooooooooo!"

  $bool = ! $bool;
  $result = foo($bool);
}

echo $result;
// outputs "woooooooooooooooooo hoo!"
```
...and if you want to make _everybody_ mad, you can still throw them.
```php
throw $result(["yes" => "i know i'm horrible"]);
```

see more in [the wiki](https://github.com/php-enspired/exceptable/wiki).

Version 5.0
-----------

**Version 5** requires PHP 8.2 or greater.
- ICU messaging system overhauled and published to its own package!
  Check out [php-enspired/peekaboo](https://packagist.org/packages/php-enspired/peekaboo) - using _exceptable_ means you get it for free, so take advantage!
- Introduces the _Error_ interface for enums, making errors into first-class citizens and opening up the ability to handle errors as values.
  Adds an `SplError` enum for php's built-in exception types.
- Reworks and improves functionality for Exceptables and the Handler.
  Error / Exception / Shutdown Handlers now have explicit interfaces, as do debug log entries.

[Read the release notes.](https://github.com/php-enspired/exceptable/wiki/new-in-5.0)

Version 4.0
-----------

**Version 4.0** requires PHP 7.4 or greater.
- PHP 7.4 added typehints to some Throwable properties, which required changes to the `IsExceptable` trait.
  This means _Exceptable_ can no longer support PHP 7.3 or earlier - though that's fine, right?
  You've already upgraded your application to 8+ anyway, right?
- right?

docs
----

### Usage
- [Error enums](https://github.com/php-enspired/exceptable/wiki/Usage:-Error-enums)
- [Exceptables](https://github.com/php-enspired/exceptable/wiki/Usage:-Exceptables)
- [Spl Errors](https://github.com/php-enspired/exceptable/wiki/Usage:-SPL-Errors)
- [Handlers](https://github.com/php-enspired/exceptable/wiki/Usage:-Handlers)
- [Making and Testing Exceptables for Your Own Project (coming soon!)](#)
### Api
- [The `Error` Interface](https://github.com/php-enspired/exceptable/wiki/API:-The-Error-Interface)
- [The `Exceptable` Interface](https://github.com/php-enspired/exceptable/wiki/API:-The-Exceptable-Interface)
- [The `Handler` Class](https://github.com/php-enspired/exceptable/wiki/API:-The-Handler-Class)

contributing or getting help
----------------------------

I'm [on IRC at `libera#php-enspired`](https://web.libera.chat#php-enspired), or open an issue [on github](https://github.com/php-enspired/exceptable/issues).  Feedback is welcomed as well.
