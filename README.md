![](https://img.shields.io/github/release/php-enspired/exceptable.svg)  ![](https://img.shields.io/badge/PHP-7.3-blue.svg?colorB=8892BF)  ![](https://img.shields.io/badge/license-GPL_3.0_only-blue.svg)

how exceptable!
===============

_Exceptables_ make exceptions exceptional.  Exceptables provide some nice utility methods, but the main benefit is having a way to conveniently and quickly organize all the error cases in your application.

Exceptables are easy to create and pass details to, they provide access to error info for both humans and code, and make it easy to extend, add, and maintain error handling code as your application grows.

dependencies
------------

Requires php 7.3 or later.

ICU support requires the `intl` extension.

installation
------------

Recommended installation method is via [Composer](https://getcomposer.org/): simply `composer require php-enspired/exceptable`.

a quick taste
-------------
```php
<?php

use AT\Exceptable\Handler;
use AT\Exceptable\Spl\RuntimeException;

// a simple Exceptable just for you
class FooException extends RuntimeException {

  const UNKNOWN_FOO = 1;

  const INFO = [
    self::UNKNOWN_FOO => [
      'message' => 'unknown foo',
      'format' => "i don't know who, you think is foo, but it's not {foo}"
    ]
  ];
}

throw new FooException(FooException::UNKNOWN_FOO);
// on your screen:
// Fatal error: Uncaught FooException: unknown foo in ...

$handler = new Handler();
$handler
  ->onException(function($e) { error_log($e->getMessage()); return true; })
  ->register();

$context = ['foo' => 'foobedobedoo'];
throw new FooException(FooException::UNKNOWN_FOO, $context);
// in your error log:
// i don't know who, you think is foo, but it's not foobedobedoo
```

see more in [the wiki](https://github.com/php-enspired/exceptable/wiki).

Version 2 will reach End-of-Life on November 30, 2020.
------------------------------------------------------

Version 3.0 is here!
--------------------

**Version 3.0** requires PHP 7.3 or greater and introduces some exciting changes from version 2:
- Support* for ICU locales, message formats, and resource bundles!\
  \* _requires the intl extension._
- Ready-to-extend (or just use) `Exceptable` classes based on the built-in SPL Exception classes!
- The generic `Exceptable` Exception base class has been removed.
- Introduces a "debug mode" for Handlers!
- Handlers are now Logger (e.g., Monolog)-aware!

[Read more about the 3.0 release](https://github.com/php-enspired/exceptable/wiki/new-in-3.0).

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

I'm on [Freenode at `#php-enspired`](http://web.libera.chat#php-enspired), or open an issue [on github](https://github.com/php-enspired/exceptable/issues).  Feedback is welcomed as well.
