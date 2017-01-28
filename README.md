![](https://img.shields.io/github/release/php-enspired/exceptable.svg)  ![](https://img.shields.io/badge/PHP-7.0-blue.svg?colorB=8892BF)  ![](https://img.shields.io/badge/license-GPL_3.0_only-blue.svg)

how exceptable!
===============

_Exceptables_ make exceptions exceptional.  Exceptables have some nice utility methods, but the main benefit is having a way to conveniently and quickly organize all the error cases in your application.  Exceptables are easy to create and pass details to.  They provide access to error info for both humans and code.  Exceptables make it easy to extend, add, and maintain error handling code as your application grows.

dependencies
------------

Requires php 7.0 or later.

installation
------------

Recommended installation method is via [Composer](https://getcomposer.org/): simply `composer require php-enspired/exceptable`.

basic usage
-----------

The `Exception` class provides a complete base implementation for the `Exceptable` interface.  Simply extend it, define your error codes and information, and you have a working implementation.

### your first exceptable

Here's a brief example Exceptable:
```php
<?php

use at\exceptable\Exception;

class FooException extends Exception {

  // define your error code.
  const UNKNOWN_FOO = 1;

  // define information about your errors (indexed by error code).
  // at a minimum, include a message.
  const INFO = [
    self::UNKNOWN_FOO => ['message' => 'unknown foo']
  ];

  // that's it
}
```

Exceptables have very flexible constructors.  The arguments are the same as those on the `Throwable` interface — `$message`, `$code`, and `$previous` — with an additional argument `$context` which accepts an array of values you provide (typically, details for the exception message).  The _flexiblity_ is that these arguments are **all optional**.  Often, the only argument you'll need to provide is the error code:

```php
<?php

throw new FooException(FooException::UNKNOWN_FOO);
// Fatal error: Uncaught FooException: unknown foo in ...
```

### adding context

Note, our Exceptable set the proper exception message for us.  But, this message is generic and fairly useless.  Let's add some `$context`.

```php
<?php

use at\exceptable\Exception;

class FooException extends Exception {

  const UNKNOWN_FOO = 1;

  const INFO = [
    self::UNKNOWN_FOO => [
      'message' => 'unknown foo',
      'tr_message' => "i don't know who, you think is foo, but it's not {foo}"
    ]
  ];
}
```

The `tr_message` is a _translatable message_.  It takes named {placeholders} from contextual information your code will provide at runtime.  If a value for a named placeholder is not provided, then the Exceptable will fall back on using the default message.

```php
<?php

throw new FooException(FooException::UNKNOWN_FOO, ['foo' => 'foobedobedoo']);
// Fatal error: Uncaught FooException: i don't know who, you think is foo, but it's not foobedobedoo in ...
```

`$context` can also be provided later, after instantiation, via the `addContext()` method.  Values provided this way will be merged with existing values.

### handling exceptables

Uncaught exceptions are great and all, but what if we want to catch them?  How do we know what to do with them?  Because your error conditions have codes, your code can read Exceptables almost as well as you can.  You can also provide a _severity_ rating (one of `Exceptable::ERROR`|`Exceptable::WARNING`|`Exceptable::NOTICE`), either at runtime or as a part of the default exception info, which your code can use as a hint as to how serious the problem is.

```php
<?php

use at\exceptable\Exception;

class FooException extends Exception {

  const UNKNOWN_FOO = 1;

  const INFO = [
    self::UNKNOWN_FOO => [
      'message' => 'unknown foo',
      'severity' => Exceptable::WARNING,
      'tr_message' => "i don't know who, you think is foo, but it's not {foo}"
    ]
  ];
}
```

```php
<?php

try {
  // here we don't provide a severity, so it defaults to WARNING as we defined above
  throw new FooException(FooException::UNKNOWN_FOO, ['foo' => 'foobedobedoo']);
} catch (FooException $e) {
  handleFoo($e);
  // everyone is happy
}

try {
  throw (new FooException(FooException::UNKNOWN_FOO, ['foo' => 'cthulhu']))
    ->setSeverity(Exceptable::ERROR);
} catch (FooException $e) {
  handleFoo($e);
  // RUN AWAY, RUN AWAY
}

function handleFoo(FooException $e) {
  if ($e->isWarning()) {
    error_log($e->getMessage());
    introduceFoo($e->getContext()['foo']);
    return;  // everyone is happy
  }
  if ($e->isError()) {
    error_log($e->__toString());
    foo_RUN_AWAY_RUN_AWAY();
    die(1);
  }
}
```

### useful utilities

In the above examples, you might have noticed some of those useful utilities.

The **`getSeverity()`** method might be familiar to you, if you've ever seen `ErrorException`s (hey, now you have a concrete idea of what you can pass as that argument).  As shown, we also have convenience methods for checking the exceptable severity: `isError()`, `isWarning()`, and `isNotice()`.

Since we can pass a `$context` array to the Exceptable, it makes sense that we'd have a **`getContext()`** method to get it back.

**`__toString`** generates a normal Exception `__toString` message, and adds the `$context` info at the end, in pretty json.

When you have a chain of previous exception(s), it's common that the _initial_ exception is of more interest than other, intermediate exceptions; so we have **`getRoot()`** to get it directly.

### extending exceptables

If you find yourself needing more or situation-specific functionality, you can override the methods your exceptable inherits from `Exception`.  Read the source first  : )

There is also a test suite for the base `Exception` class, which might also be useful as a starting point for testing your own Exceptables.  Run it with `composer test:unit`.

### exceptable handlers

_*NEW since 1.1_

Exceptables are all about making error cases a more organized and integrated part of your application.  So, it makes sense that you'd need a way to handle non-Exceptable exceptions, regular PHP errors, and even the shutdown process (e.g., in the case of a fatal PHP error).

_Handlers_ are error handling objects that you can use to implement error handling on a fine-grained basis: specify handler(s) for errors or uncaught exceptions based on severity.
```php
<?php

use at\exceptable\Handler;

$handler = new Handler();
$handler->onError(function($s, $m, $f, $l, $c) { error_log($m); return true; })
  ->register();

trigger_error('foo!', E_USER_NOTICE);
// "foo!" will be logged
```

Note we included `return true` in the handler function: this tells the Handler that everything has been handled, and so no more handlers will be called.  Otherwise, the next handler (if any) would be called, and would finally fall back to PHP's internal error handler:
```php
<?php

use at\exceptable\Handler;

$handler = new Handler();
$handler->onError(function($s, $m, $f, $l, $c) { error_log("one: {$m}"); })
  ->onError(function($s, $m, $f, $l, $c) { error_log("two: {$m}"); })
  ->register();

trigger_error('foo!', E_USER_NOTICE);
// "one: foo!" and "two: foo!" will be logged
// Notice: foo! in ...
```

As mentioned above, handlers can be assigned based on error (or Exception) severity.  You can assign handlers in this way individually:
```php
<?php

use at\exceptable\Handler;

$handler = new Handler();
$handler->onError(function($s, $m, $f, $l, $c) { error_log("notice: {$m}"); }, E_USER_NOTICE)
  ->onError(function($s, $m, $f, $l, $c) { error_log("warning: {$m}"); }, E_USER_WARNING)
  ->onError(
    function($s, $m, $f, $l, $c) { error_log("warning or notice: {$m}"); return true; },
    E_USER_WARNING|E_USER_NOTICE
  )
  ->register();

trigger_error('foo!', E_USER_NOTICE);
// "notice: foo!" and "warning or notice: foo!" will be logged

trigger_error('bar!', E_USER_WARNING);
// "warning: bar!" and "warning or notice: bar!" will be logged
```

Handlers can handle exceptions in the same way:
```php
<?php

use at\exceptable\Handler;

$handler = new Handler();
$handler->onException(function($e) { error_log($e->getMessage()); return true; })
  ->register();

throw new \Exception('foo!');
// "foo!" will be logged
```

Handlers can throw Errors (as ErrorExceptions):
```php
<?php

use at\exceptable\Handler;

$handler = new Handler();
// pass a bitmask of error types that should be thrown; defaults to E_ERROR|E_WARNING
$handler->throw(E_USER_NOTICE)
  ->register();

trigger_error('foo!', E_USER_NOTICE);
// Fatal error: Uncaught ErrorException: foo! in ...
```

Handlers can be turned off, or be used only _during_ a particular function call:
```php
<?php

use at\exceptable\Handler;

$handler = new Handler();
$handler->onError(function($s, $m, $f, $l, $c) { error_log($m); return true; })
  ->register();

trigger_error('foo!', E_USER_NOTICE);
// "foo!" will be logged

$handler->unregister();
trigger_error('foo!', E_USER_NOTICE);
// Notice: foo! in ...

$handler->during(function() { trigger_error('foo!', E_USER_NOTICE); });
// "foo!" will be logged

trigger_error('foo!', E_USER_NOTICE);
// Notice: foo! in ...
```

Documentation for Handlers is still a work in progess.
Feedback is welcomed — what's unclear? what could be explained more/better?

### contributing or getting help

I'm on Freenode at `#php-enspired`, or open an issue [on github](https://github.com/php-enspired/exceptable).  Feedback is welcomed as well.
