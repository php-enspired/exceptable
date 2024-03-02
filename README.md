![](https://img.shields.io/github/release/php-enspired/exceptable.svg)  ![](https://img.shields.io/badge/PHP-8.2-blue.svg?colorB=8892BF)  ![](https://img.shields.io/badge/PHP-8-blue.svg?colorB=8892BF)  ![](https://img.shields.io/badge/license-GPL_3.0_only-blue.svg)

how exceptable!
===============

_Exceptables_ make exceptions exceptional.  Exceptables provide some nice utility methods, but the main benefit is having a way to conveniently and quickly organize all the error cases in your application.

Exceptables are easy to create and pass details to, they provide access to error info for both humans and code, and make it easy to extend, add, and maintain error handling code as your application grows.

dependencies
------------

Requires php 8.2 or later.

ICU support requires the `intl` extension.

installation
------------

Recommended installation method is via [Composer](https://getcomposer.org/): simply `composer require php-enspired/exceptable`.

a quick taste
-------------
```php
<?php

use at\exceptable\ {
  Error,
  Handler,
  IsError
};

// a simple Error, just for you
enum FooError : int implements Error {
  use IsError;

  case UnknownFoo = 1;
  public const MESSAGES = [
    self::UnknownFoo->name => "i don't know who, you think is foo, but it's not {foo}"
  ];
}

(FooError::UnknownFoo)(["foo" => "foobedobedoo"]);
// on your screen:
// Fatal error: Uncaught at\exceptable\Spl\RuntimeException: i don't know who, you think is foo, but it's not foobedobedoo

$handler = new Handler();
$handler->onException(function($e) { error_log($e->getMessage()); return true; })
  ->register();

(FooError::UnknownFoo)(["foo" => "foobedobedoo"]);
// in your error log:
// i don't know who, you think is foo, but it's not foobedobedoo
```

errors as values
----------------
```php
<?php

use at\exceptable\ {
  Error,
  IsError,
  Result
};

enum FooError : int implements Error {
  use IsError;

  case TheyToldMeToDoIt = 1;
  public const MESSAGES = [self::TheyToldMeToDoIt->name => "ooh noooooooooooooooooo!"];
}

function foo(bool $fail) : Result {
  return $fail ?
    Result::error(FooError::TheyToldMeToDoIt) :
    Result::value("woooooooooooooooooo hoo!");
}

$result = foo($falseOrTrueIsUpToYou);
if ($result->isError()) {
  echo $result->error->message();
  // outputs "ooh noooooooooooooooooo!"
} else {
  echo $result->value;
  // outputs "woooooooooooooooooo hoo!"
}
```

see more in [the wiki](https://github.com/php-enspired/exceptable/wiki).

Version 5.0
-----------

**Version 5** requires PHP 8.2 or greater.
- ICU messaging system overhauled and published to its own package!
  Check out [php-enspired/peekaboo](https://github.com/php-enspired/peekaboo) - using _exceptable_ means you get it for free, so take advantage!
- Introduces _Error enums_, making errors into first-class citizens and opening up the ability to handle errors as values.
  Also introduces a `Return` class, which lets you handle success/error values by returning them up the chain.
- Reworks and improves functionality for Exceptables and the Handler.

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

- API:
  - [The Exceptable Interface](https://github.com/php-enspired/exceptable/wiki/API:-The-Exceptable-Interface)
  - [SPL Exception Classes](https://github.com/php-enspired/exceptable/wiki/API:-SPL-Exception-Classes)
  - [The Handler Class](https://github.com/php-enspired/exceptable/wiki/API:-The-Handler-Class)
- [Basic Exceptable Usage](https://github.com/php-enspired/exceptable/wiki/Usage:-Exceptables)
- [Basic Handler Usage](https://github.com/php-enspired/exceptable/wiki/Usage:-Handlers)
- [Localization and Message Formatting](https://github.com/php-enspired/exceptable/wiki/Usage:-ICU)
- [SPL Exception Classes](https://github.com/php-enspired/exceptable/wiki/Usage:-SPL-Exception-Classes)

contributing or getting help
----------------------------

I'm [on IRC at `libera#php-enspired`](https://web.libera.chat#php-enspired), or open an issue [on github](https://github.com/php-enspired/exceptable/issues).  Feedback is welcomed as well.
