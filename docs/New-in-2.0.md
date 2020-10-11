Version 2 brings not only improvements, but also breaking changes!

Requires PHP 7.1
----------------

PHP 7.0 is now end-of-life. You shouldn't be using it!

Note that this is a _minimum_ requirement. PHP 7.1 is now in security-only support and will be EOL on December 1, 2019. Exceptables will support 7.1 until that time as well, in order to give users time to migrate existing code to PHP 7.2.

If you're still running PHP 7.1, it's time to upgrade!

The _Exceptable_ Interface
--------------------------

### New Constructor Signature
####   public __construct(int $code [, array $context = [] [, Throwable $previous]]) : void

Version 1 had four optional (and omittable) constructor arguments. This effectively gave it 10 different valid signatures… which is very bad for clarity and maintainability. Further, one of the primary goals of this package is to keep errors from being defined as "one-off" messages throughout a codebase, yet we still accept a `$message` argument.

Version 2 simplifies the constructor with a single signature, and removes the `$message` argument entirely. 

### Removed Methods

Version 2 removes the `isError`, `isWarning`, and `isNotice` methods from the interface. These were convenience methods, and rarely used. Check the return value of `getSeverity` directly instead.

Also removed are the `addContext` and `setSeverity` methods. Context and severity are both used mainly in composing the Exceptable message during construct, which means that allowing them to be changed later is inconsistent and misleading — or at the very least, a waste of effort.

The _IsExceptable_ Trait
------------------------

The base Exceptable implementation is now made in a trait.  The abstract `Exception` class was not removed, so users can still extend from this base class if they wish. But the trait allows Exceptables to be composed as mixins to other exception classes.

PHP and the SPL provide a number of useful exception classes, but in version 1 these could not be used with Exceptables because implementations had to extend from the base class. Now, they can extend from _any_ Throwable, and still use the Exceptable features:

```php
<?php

namespace Foo;

use InvalidArgumentException;
use at\Exceptable\ {
  Exceptable,
  IsExceptable
};

class BadFooArg extends InvalidAgrumentException implements Exceptable {
  use IsExceptable;

  // . . .
}
```
```php
<?php

use Foo\BadFooArg;
use at\Exceptable\Exceptable;

try {
  throw new BadFooArg(. . .);
} catch (BadFooArg $e) {
  // catches it!
}

try {
  throw new BadFooArg(. . .);
} catch (Exceptable $e) {
  // catches it!
}

try {
  throw new BadFooArg(. . .);
} catch (InvalidArgumentException $e) {
  // catches it!
}
```

Selective Exceptable Handling
-----------------------------

The Handler class has a method `during`, which registers the Handler, invokes a callback function, and then un-registers the handler. This allows any exceptions thrown from the callback would be handled by the registered handlers. Critically, this does not mean such exceptions would be _caught_: in fact, it means only uncaught exceptions would be passed to the registered handlers. Execution of the php script would still end once the handlers had completed.

While this is intentional, it was also widely misunderstood. Users expected `during()` to catch any exceptions, handle them, and (if successfully handled) allow the script to continue. To address this, a new method `try()` has been added to the `Handler` class:

####   public try(callable $callback [, ...$arguments]) : mixed
Tries invoking a callback and handles any uncaught exceptions using the registered exception handlers.

This is effectively the same as invoking the callback inside a try..catch block using the registered exception handlers, but allows the handlers to be provided programatically and passed around instead of being hard-coded at the point of invocation.

- callable `$callback`: the callback to invoke.
- mixed `....$arguments`: arguments to pass to the callback function.

Returns the value returned from the callback function.

Development Process Improvements
--------------------------------

In addition to continuing to fill in more unit tests, Exceptable now uses [phan](https://github.com/phan/phan) for static code analysis. The development process also now makes use of github tools (like Projects) to help keep things organized.