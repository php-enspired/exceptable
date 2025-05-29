![](https://img.shields.io/github/release/php-enspired/exceptable.svg)  ![](https://img.shields.io/badge/PHP-8.3-blue.svg?colorB=8892BF)  ![](https://img.shields.io/badge/license-MPL.2.0-orange.svg)

how exceptable!
===============

A lot of php code treats exceptions in an ad-hoc way: throwing a plain `Exception` instance with a message written inline. Then, when (...if?) you look at your error log, you have to start working backwards to figure out the state of the application at that time. This can be frustrating and painful.

_Exceptables_ make exceptions exceptional. Exceptables:
- ✓ are easy to create and pass details to
- ✓ are readable by both humans and your application code
- ✓ can provide a wealth of runtime information about the state of things that led to the problem
- ✓ make it easy to add, adapt, and maintain error handling code as your application grows

More importantly, when throwing an Exceptable, you can include any additional information that might be helpful - values of arguments or local variables, details about state, whole objects, anything! This _context_ is good for adding details to the error message, but can also be sent to log aggregation tools, and even be used at runtime to inspect the error and recover or fail gracefully.

dependencies
------------

Requires php 8.3 or later.

ICU support requires the `intl` extension.

installation
------------

Recommended installation method is via [Composer](https://getcomposer.org/):

simply `composer require "php-enspired/exceptable:^6"`

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

...but when?
------------

Should you use Faults as return values? or should you throw them as Exceptables?

This is a war of opinions, but it shouldn't be. Use return values when appropriate; throw when appropriate. Other arguments aside, the most significant _practical_ difference between the two is that return values must be handled immediately, when the function in question returns, while a thrown exception can be handled anywhere "upstream" as desired - generally, _not_ by the code that directly invoked the erroring function.

So, to answer: return a Fault where a solution should be easy, straightforward, and/or immediate, and reasonably obvious - not requiring much investigation (such as digging through application state) to determine the root cause.* When it's reasonable to assume that a problem would normally _not_ be expected, to be better handled further from the call site, or to be less-easily-recoverable, throw.

_* note this puts a responsibility on you to make your faults_ very specific _rather than generalized and reusable throughout the application. don't shy away from faults that are used only in one place!_

The characteristics of each approach support this idea: Faults are simple value objects that represent as specific an error condition as you like, but carry no state; while Exceptables have a stack trace and as much extra information as you could possibly find helpful.

handling errors
---------------

_Exceptable_ also provides composable error handling tools to make the experience smooth and enjoyable.

You can _collect_ various Faults or thrown exceptions and group them together as a specific Fault:

```php
<?php
use TypeError;
use at\exceptable\ {
  Fault,
  Handler\Handler,
  IsFault
};

enum MyFault implements Fault {
  use IsFault;

  case IForgotToCheckFirst;
  case IForgotWhatIWasDoing;
}

new Handler()
  ->collect(MyFault::IForgotToCheckFirst, ProcessFault::NotReady)
  ->try(new Processor()->process(...), new Example(ExampleStatus::Preparing));
  // returns MyFault::IForgotToCheckFirst

new Handler()
  ->collect(MyFault::IForgotWhatIWasDoing, TypeError:class)
  ->try(new Processor()->process(...), new StdClass());
  // returns MyFault::IForgotWhatIWasDoing
```

To abandon a code path, you can _ignore_ a specific failure case, or, ignore _any_ unexpected failure case:

```php
new Handler()
  ->ignore(TypeError::class)
  ->try(new Processor()->process(...), new StdClass());
  // returns null

new Handler()
  ->tryIgnoring(new Processor()->process(...), new StdClass());
  // returns null
```

You can force specific Faults or thrown exceptions to be (re)thrown, even if they would otherwise be collected or ignored:

```php
new Handler()
  ->ignore(TypeError::class)
  ->collect(MyFault::IForgotWhatIWasDoing, TypeError:class)
  ->throw(TypeError::class)
  ->tryIgnoring(new Processor()->process(...), new StdClass());
  // throws TypeError
```

A Handler can even encapsulate your _success_ or _failure_ handling.
This can be useful to make the entire process portable (i.e., you can pass the handler around as needed before `try()`ing it):

```php
new Handler()
  ->onFailure(fn (Fault $f) => log_error($f->message()))
  ->onSuccess(fn (Outcome $o) => $o->publish())
  ->try(new Processor()->process(...), $exampleFromInput);
  // publishes the outcome if the example from input was ready
  // logs the error otherwise
```

see more in [the wiki](https://github.com/php-enspired/exceptable/wiki).

Version 6.0
-----------

**6.0** requires PHP 8.3 or greater.

It also changes the api fairly substantially in two ways:
- The focus is changing from excpetions to more of a **Fault** (formerly, "Error")-first approach.
- Many of the old error "handling" utilities are removed in favor of more practical tools that help you use faults and exceptables in your own code, while also smoothing out usage of other code that doesn't.

**Additionally, _exceptable_ is now released under the Mozilla Public License, version 2.**

Previously, the software was released under the GPLv3. That license's strong copyleft protections were a major factor in the decision to use it, but it has become apparent that their interpretations of what constitutes "combined works" was much broader than I'd understood it to be. Specifically, merely declaring this package as a dependency (e.g., via composer), without actually modifying and/or including/distributing the code with your software, was never intended to trigger these protections.

If you are using a version of this software licensed under the GPLv3, and would prefer to use it under the MPL instead, please [contact me](relicense@enspi.red). I will grant the relicensing and waive any enforcement action against you arising from any noncompliance with the GPLv3 that would be permissible under the MPL.

[Read the release notes.](https://github.com/php-enspired/exceptable/wiki/new-in-6.0)

Version 5.0
-----------

**Version 5** requires PHP 8.2 or greater.
- ICU messaging system overhauled and published to its own package!
  Check out [php-enspired/peekaboo](https://packagist.org/packages/php-enspired/peekaboo) - using _exceptable_ means you get it for free, so take advantage!
- Introduces the _Error_ interface for enums, making errors into first-class citizens and opening up the ability to handle errors as values.
  Adds an `SplFault` enum for php's built-in exception types.
- Reworks and improves functionality for Exceptables and the Handler.
  Error / Exception / Shutdown Handlers now have explicit interfaces, as do debug log entries.

[Read the release notes.](https://github.com/php-enspired/exceptable/wiki/new-in-5.0)

docs
----

### Usage
- [Faults](https://github.com/php-enspired/exceptable/wiki/Usage:-Faults)
- [Exceptables](https://github.com/php-enspired/exceptable/wiki/Usage:-Exceptables)
- [Spl Faults](https://github.com/php-enspired/exceptable/wiki/Usage:-SPL-Faults)
- [Handlers](https://github.com/php-enspired/exceptable/wiki/Usage:-Handlers)
- [Making and Testing Exceptables for Your Own Project (coming soon!)](#)
### Api
- [The `Fault` Interface](https://github.com/php-enspired/exceptable/wiki/API:-The-Fault-Interface)
- [The `Exceptable` Interface](https://github.com/php-enspired/exceptable/wiki/API:-The-Exceptable-Interface)
- [The `Handler` Class](https://github.com/php-enspired/exceptable/wiki/API:-The-Handler-Class)

contributing or getting help
----------------------------

I'm [on IRC at `libera#php-enspired`](https://web.libera.chat#php-enspired), or open an issue [on github](https://github.com/php-enspired/exceptable/issues).  Feedback is welcomed as well.
