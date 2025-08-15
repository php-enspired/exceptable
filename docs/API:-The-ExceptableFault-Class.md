`at\exceptable\ExceptableFault`
===============================

Internal Faults.

cases
-----

---
### `UnknownFault`

A Fault occured but its type is not known.

#### message formatting context
- `__rootMessage__` The error message that indicated the fault.

---
### `UnacceptableFault`

An object implementing the Fault interface was expected, but not provided.

#### message formatting context
- `type` The data type of the value passed as a Fault.

---
### `UncaughtException`

An exception was thrown but not explicitly caught.

#### message formatting context
- `__rootType__` the fully qualified classname of the uncaught exception
- `__rootMessage__` the error message from the uncaught exception
- `__exception__` the uncaught exception

---
### `UnacceptableLogMessage`

An invalid/unusable log entry message was provided.

#### message formatting context
- ``

---
### `UnknownError`

An error occured but its details are not available.


exceptables
-----------

---
- `LogicException`: thrown for `UnknwonFault`, `UnacceptableFault`
- `RuntimeException`: thrown for `UncaughtException`
- `InvalidArgumentException`: thrown for `UnacceptableLogMessage`, `UnknownError`
