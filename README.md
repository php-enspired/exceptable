how exceptable!
===============

Make your exceptions exceptionally exceptional.  _Exceptable_ exceptions have some nice utility methods, but the main benefit is having a way to convently and quickly organize all the error cases in your application.  _Exceptables_ are easy to create and pass details to.  They provide access to error info for both humans and code.  _Exceptables_ make it easy to extend, add, and maintain error handling code as your application grows.

dependencies
------------

Requires php 7.0 or later, and the `php-enspired/util` package.

basic usage
-----------

When used with a class that inherits from a `Throwable`, the `exceptable` trait provides a complete, basic implementation for the `Exceptable` interface.  Simply define your error codes and information, and you have a working implementation.

### your first exceptable

Here's a brief example Exceptable:
```php
<?php

use at\exceptable\api\Exceptable,
    at\exceptable\exceptable as exceptableTrait;

class FooException extends RuntimeException implements Exceptable {
  use exceptableTrait;

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

Why a trait and not an abstract class? using a trait allows you to choose your own base `Exception`/`Throwable` class.

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

use at\exceptable\api\Exceptable,
    at\exceptable\exceptable as exceptableTrait;

class FooException extends RuntimeException implements Exceptable {
  use exceptableTrait;

  const UNKNOWN_FOO = 1;

  const INFO = [
    self::UNKNOWN_FOO => [
      'message' => 'unknown foo',
      'tr_message' => "i don't know who, you think is foo, but it's not {foo}",
      'tr' => ['foo' => null]
    ]
  ];
}
```

The `tr_message` is a _translatable message_.  It takes named {placeholders} from contextual information your code will provide at runtime.  The `tr` array is a `placeholder` => `default value` map.

```php
<?php

throw new FooException(FooException::UNKNOWN_FOO, ['foo' => 'foobedobedoo']);
// Fatal error: Uncaught FooException: i don't know who, you think is foo, but it's not foobedobedoo in ...
```

Pretty sweet, huh?  What if you forget/don't have context?  The default value we defined for `foo` is `null`, which tells the Exceptable it's required in order to use the translated message: it will use the generic "unknown foo" message instead.

### handling exceptables

Uncaught exceptions are great and all, but what if we want to catch them?  How do we know what to do with them?  Because your error conditions have codes, your code can read Exceptables almost as well as you can.  You can also provide a _severity_ rating (one of `E_ERROR`|`E_WARNING`|`E_NOTICE`|`E_DEPRECATED`), either at runtime or as a part of the default exception info, which your code can use as a hint as to how serious the problem is.

```php
<?php

use at\exceptable\api\Exceptable,
    at\exceptable\exceptable as exceptableTrait;

class FooException extends RuntimeException implements Exceptable {
  use exceptableTrait;

  const UNKNOWN_FOO = 1;

  const INFO = [
    self::UNKNOWN_FOO => [
      'message' => 'unknown foo',
      'severity' => E_WARNING,
      'tr_message' => "i don't know who, you think is foo, but it's not {foo}",
      'tr' => ['foo' => null]
    ]
  ];
}
```

```php
<?php

try {
  // we don't provide a severity, so it defaults to E_WARNING as we defined above
  throw new FooException(FooException::UNKNOWN_FOO, ['foo' => 'foobedobedoo']);
} catch (FooException $e) {
  handleFoo($e);
  // everyone is happy
}

try {
  throw new FooException(
    FooException::UNKNOWN_FOO,
    ['severity' => E_ERROR, 'foo' => 'cthulhu']
  );
} catch (FooException $e) {
  handleFoo($e);
  // RUN AWAY, RUN AWAY
}

function handleFoo(FooException $e) {
  switch ($e->getSeverity()) {
    case E_WARNING :
      error_log($e->getMessage());
      introduceFoo($e->getContext()['foo']);
      // everyone is happy
      break;
    case E_ERROR :
      error_log($e->getDebugMessage());
      foo_RUN_AWAY_RUN_AWAY();
      die(1);
  }
}
```

### useful utilities

In the above examples, you might have noticed some of those useful utilities.

The **`getSeverity()`** method might be familiar to you, if you've ever seen `ErrorException`s (hey, now you have a concrete idea of what you can pass as that argument).

Since we can pass a `$context` array to the Exceptable on construct, it makes sense that we'd have a **`getContext()`** method to get it back.

The **`getDebugMessage()`** method has a similar purpose: it returns the Exceptable's normal `__toString` message, and then adds the `$context` info at the end, in pretty json.

If you've peeked at the code, you may have also noticed that `Exceptable` extends `JsonSerializable`, which means you can do `json_encode($exceptable)` and get a reasonably useful result.

There's one more: **`getRoot()`**.  If you have an exception chain, it's common that the _initial_ exception is of more interest than other, intermediate exceptions; so we have a way to get it directly.

### extending exceptables

If you find yourself needing more or situation-specific functionality, you can override the methods inherited from the `exceptable` trait.  Read the source first  : )

quick tips
----------

A few things that might be useful as you start out with Exceptables:

- Your Exceptable classes must extend from a built-in `Throwable` class (e.g., `RuntimeException` or `ArithmeticError`).  I mean, they don't **have too**, but they aren't very useful if they don't.

- If your Exceptable extends from ErrorException (which already has a (final) method getSeverity()), exceptable::getSeverity() will need to be aliased when the trait is used.

- Your Exceptables cannot extend from PDOException, because it breaks the Throwable interface (`PdoException::getCode()` returns a string and not an int).
