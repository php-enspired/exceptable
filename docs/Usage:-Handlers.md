
_Exceptable_ provides composable error handling tools to make the experience smooth and enjoyable.

### collecting faults

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

### ignoring faults

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

### forcing faults

You can force specific Faults or thrown exceptions to be (re)thrown, even if they would otherwise be collected or ignored:

```php
new Handler()
  ->ignore(TypeError::class)
  ->collect(MyFault::IForgotWhatIWasDoing, TypeError:class)
  ->throw(TypeError::class)
  ->tryIgnoring(new Processor()->process(...), new StdClass());
  // throws TypeError
```

### success and failure callbacks

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
