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

// results in:
//  Fatal error: Uncaught at\exceptable\Spl\RuntimeException: ProcessError.NotReady: Example is not ready (status is 'preparing')
```

Having faults available to your application also means you _don't have to_ throw exceptions: your methods can return first-class error values. This approach encourages handling error cases more carefully and closer to their source and is also a benefit to static analysis. See [Larry Garfield's excellent article "_A Naked Result_"](https://peakd.com/hive-168588/@crell/much-ado-about-null#anakedresult) for more about this idea and its benefits.

### read more in [the wiki](https://github.com/php-enspired/exceptable/wiki).

Version 6.0
-----------

**6.0** requires PHP 8.3 or greater.

It also changes the api fairly substantially in two ways:
- The focus is changing from excpetions to more of a **Fault** (formerly, "Error")-first approach.
- Many of the old error "handling" utilities are removed in favor of more practical tools that help you use faults and exceptables in your own code, while also smoothing out usage of other code that doesn't.

**Additionally, _exceptable_ is now released under the Mozilla Public License, version 2.**

Previously, the software was released under the GPLv3. That license's strong copyleft protections were a major factor in the decision to use it, but it has become apparent that their interpretations of what constitutes "combined works" was much broader than I'd understood it to be. Specifically, merely declaring this package as a dependency (e.g., via composer), without actually modifying and/or including/distributing the code with your software, was never intended to trigger these protections.

If you are using a previous version of this software licensed under the GPLv3, and would prefer to use it under the MPL instead, please [contact me](relicense@enspi.red). I will grant the relicensing and waive enforcement action against you arising from any noncompliance with the GPLv3, if it would be permissible under the MPL.

[Read the release notes.](https://github.com/php-enspired/exceptable/wiki/new-in-6.0)

docs
----

### Usage
- [Faults and Exceptables](https://github.com/php-enspired/exceptable/wiki/Usage:-Faults-and-Exceptables)
- [Spl Faults and Exceptables](https://github.com/php-enspired/exceptable/wiki/Usage:-Spl-Faults-and-Exceptables)
- [Handlers](https://github.com/php-enspired/exceptable/wiki/Usage:-Handlers)

### Api
- [The `Fault` Interface](https://github.com/php-enspired/exceptable/wiki/API:-The-Fault-Interface)
- [The `Exceptable` Interface](https://github.com/php-enspired/exceptable/wiki/API:-The-Exceptable-Interface)
- [The `SplFault` Class](https://github.com/php-enspired/exceptable/wiki/API:-The-SplFault-Class)
- [The `ExceptableFault` Class](https://github.com/php-enspired/exceptable/wiki/API:-The-ExceptableFault-Class)
- [The `Handler` Class](https://github.com/php-enspired/exceptable/wiki/API:-The-Handler-Class)

tests
-----

You can run unit tests with `composer test:unit` and static analysis with `composer test:analyze`.

Note, the first time you run a `test:` command, dev dependencies will be installed automatically. This requires an internet connection and may take some time.

contributing or getting help
----------------------------

I'm [on IRC at `libera#php-enspired`](https://web.libera.chat#php-enspired), or open an issue [on github](https://github.com/php-enspired/exceptable/issues).  Feedback is welcomed as well.
