`at\exceptable\Exceptable`
==========================

Interface for exceptional exceptions.

properties
----------

---
### readonly array `$context`

User-provided contextual information for this exception.

This may be replacement values for error message formatting, information you want to be logged, or additional data/structures to be available to your code (e.g., for error handling). Any data types are permitted, and most will be meaningfully converted to string for message formatting. Values are not _required_ to be serializable, but this is probably a good idea.

Key names starting with a double underscore (`__`) are reserved for internal use; your keys with this prefix may be overwritten/lost. The following values will be added to your Exceptable context automatically:
- Throwable   `__exception__`: the current exception.
- ? Throwable `__previous__`:  the previous exception, if any.
- Throwable   `__root__`:      the original (most-previous) exception in this exception chain. This may be the same as `__exception__` or `__previous__`.

For each of these, the following are also available for message formatting:
- string  `__{exception}Message__`: the exception/previous/root error message.
- int     `__{exception}Code__`:    the exception/previous/root error code.
- string  `__{exception}File__`:    the file in which the exception/previous/root occured.
- int     `__{exception}Line__`:    the line on which the exception/previous/root occured.
- ? Fault `__{exception}Fault__`:   the fault that the exception/previous/root was built from, if any.

---
### readonly Fault `$fault`

The Fault instance that this Exceptable was build from.

---
### readonly ? Throwable `$previous`

The previous exception, if any.

---
### readonly Throwable `$root`

The original (most-previous) exception in this exception's chain. This may be the same as the top-level or previous exception.

methods
-------

---
### `__construct(Fault $fault [, array $context] [, Throwable $previous])`

Constructs a new Exceptable instance from a Fault.

#### parameters
- at\exceptable\Fault `$fault`    The Fault to build from.
- array               `$context`  User-provided contextual information.
- Throwable           `$previous` The previous exception, if any.

---
### bool `has(Fault $fault)`

Checks whether this Exceptable contains the given Fault, anywhere in its exception chain.

#### parameters
- at\exceptable\Fault `$fault`  The Fault to check for.

#### returns
- boolean

---
### bool `is(Fault $fault)`

Checks whether this Exceptable was built from the given Fault.

#### parameters
- at\exceptable\Fault `$fault`  The Fault to check for.

#### returns
- boolean

inherited methods
-----------------

---
Inherited from [Throwable](https://php.net/throwable):
- string      `getMessage()`
- int         `getCode()`
- string      `getFile()`
- int         `getLine()`
- array       `getTrace()`
- string      `getTraceAsString()`
- ? Throwable `getPrevious()`
- string      `__toString()`
