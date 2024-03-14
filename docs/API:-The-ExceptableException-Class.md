`at\exceptable\ExceptableError`
===================================

Represents Exceptable error cases.

constants
---------

### ExceptableError::NO_SUCH_CODE
An invalid code was provided to an Exceptable class.

### ExceptableError::UNCAUGHT_EXCEPTION
An exception was thrown which no registered exception handlers successfully handled.

### ExceptableError::INVALID_HANDLER
A handler threw an exception, had a bad signature, or had an invalid return value.
