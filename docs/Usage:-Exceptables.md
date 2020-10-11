The `Exception` class provides a complete base implementation for the `Exceptable` interface.  Simply extend it, define your error codes and information, and you have a working implementation.

### your first exceptable

Here's a brief example Exceptable:
```php
<?php

use at\exceptable\Exception as Exceptable;

class FooException extends Exceptable {

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

Exceptables have very straightforward constructors.  The first, and often only, argument you'll need to provide is the error code:

```php
<?php

throw new FooException(FooException::UNKNOWN_FOO);
// Fatal error: Uncaught FooException: unknown foo in ...
```

### adding context

Note, our Exceptable set the proper exception message for us.  But, this message is generic and fairly useless.  Let's add some `$context`.

```php
<?php

use at\exceptable\Exception as Exceptable;

class FooException extends Exceptable {

  const UNKNOWN_FOO = 1;

  const INFO = [
    self::UNKNOWN_FOO => [
      'message' => 'unknown foo',
      'tr_message' => "i don't know who, you think is foo, but it's not {foo}"
    ]
  ];
}
```

The `tr_message` is a _translatable message_.  It takes named `{placeholders}` from contextual information your code will provide at runtime.  If a value for a named placeholder is not provided, then the Exceptable will fall back on using the default message.

```php
<?php

throw new FooException(FooException::UNKNOWN_FOO, ['foo' => 'foobedobedoo']);
// Fatal error: Uncaught FooException: i don't know who, you think is foo, but it's not foobedobedoo in ...
```

### handling exceptables

Uncaught exceptions are great and all, but what if we want to catch them?  How do we know what to do with them?  Because your error conditions have codes, your program can read Exceptables almost as well as you can.  You can also provide a _severity_ rating (one of `Exceptable::ERROR`|`Exceptable::WARNING`|`Exceptable::NOTICE`), either at runtime or as a part of the default exception info, which your code can use as a hint as to how serious the problem is.

```php
<?php

use at\exceptable\Exception as Exceptable;

class FooException extends Exceptable {

  const UNKNOWN_FOO = 1;
  const SCARY_FOO = 2;

  const INFO = [
    self::UNKNOWN_FOO => [
      'message' => 'unknown foo',
      'severity' => Exceptable::WARNING,
      'tr_message' => "i don't know who, you think is foo, but it's not {foo}"
    ],
    self::SCARY_FOO => [
      'message' => 'scary foo',
      'severity' => Exceptable::ERROR,
      'tr_message' => "Ph'nglui mglw'nafh {Cthulhu} R'lyeh wgah'nagl fhtagn"
    ]
  ];
}
```

```php
<?php

try {
  throw new FooException(FooException::UNKNOWN_FOO, ['foo' => 'foobedobedoo']);
} catch (FooException $e) {
  handleFoo($e);
  // everyone is happy
}

try {
  throw (new FooException(FooException::SCARY_FOO, ['Cthulhu' => 'foo']));
} catch (FooException $e) {
  handleFoo($e);
  // RUN AWAY, RUN AWAY
}

function handleFoo(FooException $e) {
  switch ($e->getSeverity()) {
    case Exceptable::WARNING:
      error_log($e->getMessage());
      introduceFoo($e->getContext()['foo']);
      return;  // everyone is happy
    case Exceptable::ERROR:
    default:
      error_log($e->__toString());
      foo_RUN_AWAY_RUN_AWAY();
      die(1);
  }
}
```

### useful utilities

In the above examples, you might have noticed some of those useful utilities.

The **`getSeverity()`** method might be familiar to you, if you've ever seen `ErrorException`s (hey, now you have a concrete idea of what you can pass as that argument).

Since we can pass a `$context` array to the Exceptable, it makes sense that we'd have a **`getContext()`** method to get it back.

**`__toString`** generates a normal Exception `__toString` message, and adds the `$context` info at the end, in pretty json.

When you have a chain of previous exception(s), it's common that the _initial_ exception is of more interest than other, intermediate exceptions; so we have **`getRoot()`** to get it directly.

### extending exceptables

If you find yourself needing more or situation-specific functionality, you can override the methods your exceptable inherits from `Exception`.  Read the source first  : )

There is also a test suite for the base `Exception` class, which might also be useful as a starting point for testing your own Exceptables.  Run it with `composer test:unit`.
