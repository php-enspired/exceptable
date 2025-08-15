`at\exceptable\Fault`
=====================

Defines fault cases for specific error conditions.

properties
----------

---
### readonly string `$name`

The name of the Fault.

Normally, Faults are implemented as enums, and this property will be inherited from the enum case. If you decide to implement your Fault as a normal class, you will need to declare this property. It should be a single-word identifier (i.e., something that could be used as an enum case name), preferably using PascalCase.

methods
-------

---
### Exceptable `__invoke([array $context] [, ? Throwable $previous])`

This method is a proxy for `Exceptable::toExceptable()`.

---
### string `name()`

Gets a unique name for this Fault, in the form `{fully qualified classname}.{fault name}`.

#### returns
- string

---
### string `message([array $context])`

The error message for this Fault, using the given context if applicable.

#### parameters
- array `$context`  User-provided contextual information.

#### returns
- string

  The formatted error message (if found and can be formatted from available context), prefixed with the Fault name.

---
### Exceptable `toExceptable([array $context] [, ? Throwable $previous])`

Builds an Exceptable from this Fault, using the provided contextual information and previous exception.

#### parameters
- array     `$context`  User-provided contextual information.
- Throwable `$previous` The previous exception, if any.

#### returns
- Exceptable

inherited methods
-----------------

---
Inherited from [`JsonSerializable`](https://php.net/JsonSerializable):
- mixed `jsonSerialize()`
