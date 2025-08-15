
### your first fault

Generally, it's best to define your faults as an enum.

```php
<?php
use at\exceptable\ {
  Fault,
  IsFault
};

// make an enum for your fault(s)
enum ProcessFault implements Fault {
  use IsFault;

  // define each error case
  case NotReady;
  case Unprocessable;
  // . . .
}
```
…and that's it.

Seriously? Yes, seriously, that's it. If you want to skip to the end now you can. Go ahead and start writing your application code, returning and/or throwing these faults.

### error messages

You can build messages for your faults using the intl extension and ICU resource bundles. If you don't, then the error message will just be the name of the fault.

```php
<?php
use at\peekaboo\MessageRegistry;

$context = [
  "type" => "Example",
  "status" => "preparing"
];

echo ProcessFault::NotReady->message($context);
// prints:
//  ProcessFault.NotReady

$messages = new ResourceBundle("en_US", $yourResourceDirectory);
MessageRegistry::register($messages, ProcessFault::class);

echo ProcessFault::NotReady->message($context);
// depending on your bundle, prints something like
//  ProcessFault.NotReady: Example is not ready (status is 'preparing')
```

Using resource bundles gives you a lot of flexibility, but most often, faults are functional: form matters less. You usually won't need translations, dynamic messages, or advanced formatting abilities. As an alternative, you can used a backed enum to define the message:

```php
use at\exceptable\EnumeratesFaults;

enum ProcessFault : string implements Fault {
  use EnumeratesFaults;

  case NotReady = "{type} is not ready (status is '{status}')";
  // . . .
}

echo ProcessFault::NotReady->message($context);
// prints:
//  ProcessFault.NotReady: Example is not ready (status is 'preparing')
```
_Note, using `EnumeratesFaults` means the fault will_ never _try to look up messages on your registered message bundles. The `makeMessage()` method is still available, and_ will _look up messages if they are registered, but is not used internally._

### customized behavior

Sensible default behavior is provided by `IsFault` and `EnumeratesFaults`, but there are some aspects that can be customized.

> Note: During development, it is _highly_ recommended that you enable assertion checking. The exceptable library uses assertions to sanity-check your modifications - for example, that `exceptableType()` returns a suitable classname. In production, assertions can be safely disabled (and generally, should).

#### changing what `Exceptable` type is thrown

By default, faults are thrown as `at\exceptable\Spl\RuntimeException`. You can choose another Exceptable type by overriding `IsFault::exceptableType()`:

```php
<?php

namespace Example;
use at\exceptable\ {
  Fault,
  IsFault,
  Spl\InvalidArgumentException,
  Spl\LogicException,
  Spl\RuntimeException
};

enum MyFault implements Fault {
  use IsFault;

  public function exceptableType() : string {
    return match ($this) {
      self::X => InvalidArgumentException::class,
      self::Y => LogicException::class,
      self::Z => MyOwnExceptable::class,
      default => RuntimeException::class
    };
  }
}
```

Things to keep in mind:
- The string you return **must** be the fully qualified name (i.e., the classname _including_ the namespace) of an `Exceptable`. You cannot use an exception type that does not implement `Exceptable`, as the interface is what guarantees we know how to construct it.
- You can return one exceptable type for all of your faults, or different types for each, but you **must** provide a type for every fault case - e.g., specifying a `default` case when using a `match` expression is recommended.

#### changing how messages are looked up

By default, faults use their `$name` as the message key, and their classname as the group. This means your message bundle might look something like:
```
root {
  X: { "My Fault, X didn't mark the spot" }
  // . . .other messages. . .
}
```
and you would register it similarly to:
```php
use Example\MyFault;

$yourBundle = new ResourceBundle($locale, $directory);
MessageRegistry::register($yourBundle, MyFault::class);
```

You can override `IsFault::messageKey()` to use a different lookup strategy. For example, if your bundles were structured like:
```
root {
  my: {
    bundle: {
      structure: {
        X: { "My Fault, X didn't mark the spot" }
        // . . .other messages. . .
      }
    }
  }
}
```
Then you might do something like:
```php
<?php
use Override;
use at\exceptable\ {
  Fault,
  IsFault
}

enum MyFault implements Fault {
  use IsFault {
    messageKey as baseMessageKey;
  }

  #[Override]
  protected function messageKey() : string {
    return "my.bundle.structure.{$this->baseMessageKey()}";
  }
}
```

You can override `IsFault::messageGroup()` to change what group your faults look up messages under. For example, if you wanted to put all the fault messages for your application into their own group, you might do something like:
```php
<?php

const FAULT_MESSAGE_GROUP = "my-faults";

enum MyFault implements Fault {
  use IsFault;

  #[Override]
  protected function messageGroup() : string {
    return FAULT_MESSAGE_GROUP;
  }
}

$yourBundle = new ResourceBundle($locale, $directory);
MessageRegistry::register($yourBundle, FAULT_MESSAGE_GROUP);
```

### the end

Faults can be returned from methods as error values, or thrown as exceptions:

```php

class Example {
  public function __construct( public ExampleStatus $status ) {}
}

enum ExampleStatus {
  case Preparing;
  case Ready;
}

class Outcome {. . .}

class Processor {

  public function __construct(public Example $example) {}

  public function process() : Outcome|ProcessFault {
    if ($this->example->status !== ExampleStatus::Ready) {
      return ProcessFault::NotReady;
    }
    return $this->outcome();
  }

  private function outcome() : Outcome {. . .}
}

$processor = new Processor(new Example(ExampleStatus::Preparing));
$outcome = $processor->process();
// returns ProcessFault on failure
if ($outcome instanceof ProcessFault) {
  throw $outcome([
    "type" => Example::class,
    "status" => $processor->example->status
  ]);
  // throws:
  //  at\exceptable\Spl\RuntimeException<ProcessFault::NotReady>
}

// returns Outcome on success
celebrate($outcome);
```

...but when?
------------

Should you use Faults as return values? or should you throw them as Exceptables?

This is a war of opinions, but it shouldn't be. Use return values when appropriate; throw when appropriate. Other arguments aside, the most significant _practical_ difference between the two is that return values must be handled immediately, when the function in question returns, while a thrown exception can be handled anywhere "upstream" as desired - generally, _not_ by the code that directly invoked the erroring function.

So, to answer: return a Fault where a solution should be easy, straightforward, and/or immediate, and reasonably obvious - not requiring much investigation (such as digging through application state) to determine the root cause.* When it's reasonable to assume that a problem would normally _not_ be expected, to be better handled further from the call site, or to be less-easily-recoverable, throw.

_* note this puts a responsibility on you to make your faults_ very specific _rather than generalized and reusable throughout the application. don't shy away from faults that are used only in one place!_

The characteristics of each approach support this idea: Faults are simple value objects that represent as specific an error condition as you like, but carry no state; while Exceptables have a stack trace and as much extra information as you could possibly find helpful.
