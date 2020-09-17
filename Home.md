![](https://img.shields.io/github/release/php-enspired/exceptable.svg)  ![](https://img.shields.io/badge/PHP-7.0-blue.svg?colorB=8892BF)  ![](https://img.shields.io/badge/license-GPL_3.0_only-blue.svg)

how exceptable!
===============

_Exceptables_ make exceptions exceptional.  Exceptables provide some nice utility methods, but the main benefit is having a way to conveniently and quickly organize all the error cases in your application.  Exceptables are easy to create and pass details to.  They provide access to error info for both humans and code.  Exceptables make it easy to extend, add, and maintain error handling code as your application grows.

dependencies
------------

Requires php 7.0 or later.

installation
------------

Recommended installation method is via [Composer](https://getcomposer.org/): simply `composer require php-enspired/exceptable`.

a quick taste
-------------
```php
<?php

use at\exceptable\Handler;
use at\exceptable\Exception as Exceptable;

// a simple Exceptable just for you
class FooException extends Exceptable {

  const UNKNOWN_FOO = 1;

  const INFO = [
    self::UNKNOWN_FOO => [
      'message' => 'unknown foo',
      'tr_message' => "i don't know who, you think is foo, but it's not {foo}"
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

docs
----

- API:
  - [The Exceptable Interface](https://github.com/php-enspired/exceptable/wiki/API:-The-Exceptable-Interface)
  - [The Exception Class](https://github.com/php-enspired/exceptable/wiki/API:-The-Exception-Class)
  - [The Handler Class](https://github.com/php-enspired/exceptable/wiki/API:-The-Handler-Class)
  - [The ExceptableException Class](https://github.com/php-enspired/exceptable/wiki/API:-The-ExceptableException-Class)
- [Basic Exceptable Usage](https://github.com/php-enspired/exceptable/wiki/Usage:-Exceptables)
- [Basic Handler Usage](https://github.com/php-enspired/exceptable/wiki/Usage:-Handlers)

contributing or getting help
----------------------------

I'm on [Freenode at `#php-enspired`](http://webchat.freenode.net?channels=%23php-enspired&uio=d4), or open an issue [on github](https://github.com/php-enspired/exceptable/issues).  Feedback is welcomed as well.
