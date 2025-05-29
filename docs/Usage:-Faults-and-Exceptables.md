
### your first fault

There are two approaches to declaring your fault types: with a backed enum, or without. Using a backed enum allows you to declare simple error messages on the enum itself. Otherwise, it's expected that you provide error messages to the application via an ICU `ResourceBundle` (or, via an exceptable `MessageBundle`).

Using bundles




a quick taste
-------------
```php
<?php
use at\exceptable\ {
  Fault,
  EnumeratesFaults
};

// A simple Fault, just for you
enum ProcessFault : string implements Fault {
  use EnumeratesFaults;

  case NotReady = "{type} is not ready (status is '{status}')";
}

class Example {
  public function __construct( public ExampleStatus $status ) {}
}
enum ExampleStatus {
  case Preparing;
  case Ready;
}

$example = new Example(ExampleStatus::Preparing);
if ($example->status !== ExampleStatus::Ready) {
  throw (ProcessFault::NotReady)([
    "type" => $example::class,
    "status" => $example->status
  ]);
}
```
outputs:
> Fatal error: Uncaught at\exceptable\Spl\RuntimeException: ProcessError.NotReady: Example is not ready (status is 'preparing')

errors as values
----------------

Having errors available to your application as normal values also means you _don't have to_ throw exceptions.

The idea of treating error conditions as normal, expected return values is gaining popularity. This approach encourages handling error cases more carefully and closer to their source and is also a benefit to static analysis. See [Larry Garfield's excellent article "_A Naked Result_"](https://peakd.com/hive-168588/@crell/much-ado-about-null#anakedresult) for more.

```php
<?php

class Processor {

  public function __construct(public Example $example) {}

  public function process() : Outcome|ProcessFault {
    if ($this->example->status !== ExampleStatus::Ready) {
      return ProcessFault::NotReady;
    }
    return $this->outcome();
  }

  private function outcome() : Outcome {
    . . .
  }
}

class Outcome {

  public function publish() {. . .}
  . . .
}
$outcome = (new Processor($example))->process();
if ($outcome instanceof ProcessFault) {
  echo $outcome->message([
    "type" => $processor->example::class,
    "status" => $processor->example->status
  ]);
} else {
  $outcome->publish();
}
```
...and, of course, if you want to make _everybody_ mad you can still throw them.
```php
throw $outcome([
  "type" => $processor->example::class,
  "status" => $processor->example->status,
  "yes" => "i know i'm horrible"
]);
```


















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
