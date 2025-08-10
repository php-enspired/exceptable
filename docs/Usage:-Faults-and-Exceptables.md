
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

You can build messages for your faults using the intl extension and ICU resource bundles. If you don't, then the error message will just be the name of the fault.

```php
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

Using resource bundles gives you a lot of capabilities, but most often, faults are functional: form matters less. You usually won't need translations, dynamic messages, or advanced formatting abilities. As an alternative, you can used a backed enum to define the message:

```php
use at\exceptable\EnumeratesFaults;

enum ProcessFault : string implements Fault {

  case NotReady = "{type} is not ready (status is '{status}')";
  // . . .
}

echo ProcessFault::NotReady->message($context);
// prints:
//  ProcessFault.NotReady: Example is not ready (status is 'preparing')
```

### the end

Faults can then be returned from methods as error values, or thrown as exceptions:

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
if ($outcome instanceof ProcessFault) {
  throw $outcome([
    "type" => Example::class,
    "status" => $processor->example->status
  ]);
}
// throws:
//  at\exceptable\Spl\RuntimeException<ProcessFault::NotReady>
```

...but when?
------------

Should you use Faults as return values? or should you throw them as Exceptables?

This is a war of opinions, but it shouldn't be. Use return values when appropriate; throw when appropriate. Other arguments aside, the most significant _practical_ difference between the two is that return values must be handled immediately, when the function in question returns, while a thrown exception can be handled anywhere "upstream" as desired - generally, _not_ by the code that directly invoked the erroring function.

So, to answer: return a Fault where a solution should be easy, straightforward, and/or immediate, and reasonably obvious - not requiring much investigation (such as digging through application state) to determine the root cause.* When it's reasonable to assume that a problem would normally _not_ be expected, to be better handled further from the call site, or to be less-easily-recoverable, throw.

_* note this puts a responsibility on you to make your faults_ very specific _rather than generalized and reusable throughout the application. don't shy away from faults that are used only in one place!_

The characteristics of each approach support this idea: Faults are simple value objects that represent as specific an error condition as you like, but carry no state; while Exceptables have a stack trace and as much extra information as you could possibly find helpful.
