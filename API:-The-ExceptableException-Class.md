`at\exceptable\ExceptableException`
===================================

Represents Exceptable error cases.

constants
---------

### ExceptableException::NO_SUCH_CODE
An invalid code was provided to an Exceptable class.

### ExceptableException::UNCAUGHT_EXCEPTION
An exception was thrown which no registered exception handlers successfully handled.

### ExceptableException::INVALID_HANDLER
A handler threw an exception, had a bad signature, or had an invalid return value.