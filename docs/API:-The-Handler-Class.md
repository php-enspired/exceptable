`at\exceptable\Handler\Handler`
===============================

For building composable and reusable error handling strategies.

properties
----------

---
### readonly DebugLog `$debugLog`

A log of debugging messages provided to this Handler.

methods
-------

---
### `__construct([HandlerOptions $options])`

Constructor.

#### parameters
- HandlerOptions `$options`: Configuration options for this Handler.
  - bool `$debug`:
  Enable debug mode? Defaults to `false`.
  - ? Psr\Log\LoggerInterface `$logger`:
  Optional logger instance to send log messages to.
  - bool `$scream`:
  Ignore the error control operator? Defaults to `false`.
  - int `$throwErrorExceptions`:
  Php error types which should be thrown as ErrorExceptions. This is a disjunction of `E_*` constants; you can use `-1` to throw all error types or `0` to throw none. Defaults to `0`.
  - int `$returnErrors`:
  Php error types which should be returned as Faults. This is a disjunction of `E_*` constants; you can use `-1` to throw all error types or `0` to throw none. Defaults to `0`.

---
### Closure `bind(callable $if)`

Binds a callback to this Handler's registered error strategies.

Bound callables are intended for use with `Handler->tryPipe()` (or, in php 8.5+, with the `|>` operator). The callback is invoked only if the input value is not a Fault; otherise, the Fault is returned.

#### parameters
- callable `$if`:  The callback to invoke _if_ the input value is not a Fault. The callback must accept a single argument, and is expected to return a Fault on failure or any other value on success.

#### returns
- Closure:  The callback, as a Closure.

---
### Handler `collect(Fault $as, Fault|string ...$errorCases)`

Specifies error cases to be collected and returned as the given Fault.

#### parameters
- Fault `$as`: The Fault to collect error cases as.
- Fault|string `...$errorCases`: The Faults and/or fully qualified Throwable classnames to collect.

#### returns
- Handler: a new handler instance.

---
### Handler `default(mixed $to, Fault|string|null ...$errorCases)`

Specifies a default value to be returned in place of the given error cases.

#### parameters
- mixed `$to`: The default value to return.
- Fault|string|null `...$errorCases`: The Faults and/or fully qualified Throwable classnames to default from. Omit (or pass `null`) to specify a default for a nullable or void return.

#### returns
- Handler: a new handler instance.

---
### Handler `ignore(Fault|string ...$errorCases)`

Specifies error cases this Handler should ignore and returned as null.

#### parameters
- Fault|string `...$errorCases`: The Faults and/or fully qualified Throwable classnames to ignore.

#### returns
- Handler: a new handler instance.

---
### void `logIf(Stringable|HasMessages|array|string|null $message [, array $context])`

Logs a fault or exception to this handler, or any message in debug mode.

If a PSR logger instance was configured, the message will also be logged there.

#### parameters
- Stringable|HasMessages|array|string|null $message
- array`$context`: User-provided contextual information.

#### returns
- Handler: a new handler instance.

---
### Handler `onFailure(callable $failure)`

Specifies a callback for processing return values on failure.

#### parameters
- callable `$failure`: The callback to invoke on failure. The callback must accept a single argument, the Fault. Its return value is returned by the Handler.

#### returns
- Handler: a new handler instance.

---
### Handler `onSuccess(callable $success)`

Specifies a callback for processing return values on success.

#### parameters
- callable `$failure`: The callback to invoke on failure. The callback must accept a single argument. Its return value is returned by the Handler.

#### returns
- Handler: a new handler instance.

---
### Handler `options(Options $options)`

Specifies new Handler Options.

#### parameters
- at\exceptable\Handler\Options `$options`: New options. These will be merged with existing options.

#### returns
- Handler: a new handler instance.

---
### Handler `retry(int $attempts, Fault|string ...$errorCases)`

Specifies the number of times processing should be retried on the given error cases.

#### parameters
- int `$attempts`: Number of retries to attempt.
- Fault|string `...$errorCases`: The Faults and/or fully qualified Throwable classnames to retry.

#### returns
- Handler: a new handler instance.

---
### Handler `throw(Fault|string ...$errorCases)`

Specifies error cases this Handler should (re)throw without modification.

#### parameters
- Fault|string `...$errorCases`: The Faults and/or fully qualified Throwable classnames to throw.

#### returns
- Handler: a new handler instance.

---
### mixed|Fault `try(callable $callback, mixed ...$args)`

Invokes the given callable using any registered error strategies.

#### parameters
- callable `$callback`: The callback to invoke.
- mixed `...$args`: Argument(s) to pass to the callback.

#### throws
- Throwable: as configured.

#### returns
- mixed: The callback's return value, on success.
- Fault: On failure.

---
### mixed|null `tryIgnoring(callable $callback, mixed ...$args)`

Invokes the given callable using any registered error strategies, ignoring any failures.

#### parameters
- callable `$callback`: The callback to invoke.
- mixed `...$args`: Argument(s) to pass to the callback.

#### throws
- Throwable: as configured.

#### returns
- mixed: The callback's return value, on success.
- null: On failure.

---
### mixed|Fault `tryPipe(mixed $initial, callable ...$functions)`

Invokes the given callables in turn, passing the return value of each as the argument for the next.

If a Fault is encountered, the process is aborted.

If desired, individual callable steps can be bound to specific Handlers via `Handler->bind()`. The process as a whole is wrapped with this Handler's registered error strategies.

#### parameters
- mixed `$initial`: The initial value to pass to the first function in the chain.
- callable `...$callback`: The callbacks to invoke, in order. Each must accept a single mixed argument, and is expected to return mixed on success and a Fault on failure.

#### throws
- Throwable: as configured.

#### returns
- mixed: The callback's return value, on success.
- Fault: On failure.
