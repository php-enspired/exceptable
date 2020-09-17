`at\exceptable\Exceptable`
==========================

An augmented interface for php exceptions.  `Exceptable` extends the `Throwable` interface with a severity rating, various utility methods, and a specialized (but backwards compatible) constructor.

static methods
--------------

### Exceptable::getInfo()
```
public static array getInfo( int $code )
```

Gets information about an error case identified by the given code.

parameters:
- int **`$code`**
  The exceptable code to look up

**throws** `at\exceptable\ExceptableException` if the code is not known to the implementation.

**returns** an array with info about the code, including (at a minimum) its `"code"` and `"message"`.

---

### Exceptable::hasInfo()
```
public static bool hasInfo( int $code )
```

Checks whether there is an error case associated with the given code.

parameters:
- int **`$code`**
  The exceptable code to look up

**returns** true if the code identifies a known error case; false otherwise.

---

### Exceptable::is()
```
public static bool is( Throwable $e, int $code )
```

Checks whether the given exception matches the given Exceptable code.

parameters:
- Throwable **`$e`**
  The exception to check
- int **`$code`**
  The exceptable code to compare to

**returns** true if the given throwable is an instance of the Exceptable class and has the same code; false otherwise.

---

### Exceptable::create()
```
public static Exceptable create( int $code [, array $context = [] [, Throwable $previous]] )
```

Factory method: constructs a new Exceptable from the given arguments.

parameters:
- int **`$code`**
  The Exceptable code.
- array **`$context`**
  Contextual information about the Exceptable.  Typically, used to provide details for the Exceptable message, but may include any information that should be available later (e.g., to loggers).
- Throwable **`$previous`**
  The previous Exception, if any.

**throws** `at\exceptable\ExceptableException` if the given code is invalid.

**returns** a new Exceptable instance on success.

---

### Exceptable::throw()
```
public static void throw( int $code [, array $context = [] [, Throwable $previous]] )
```

Utility method: constructs and then throws a new Exceptable from the given arguments.

parameters:
- int **`$code`**
  The Exceptable code.
- array **`$context`**
  Contextual information about the Exceptable.  Typically, used to provide details for the Exceptable message, but may include any information that should be available later (e.g., to loggers).
- Throwable **`$previous`**
  The previous Exception, if any.

**throws** `at\exceptable\Exceptable` on success.
**throws** `at\exceptable\ExceptableException` if the given code is invalid.

---

instance methods
----------------

### Exceptable::__construct()
```
public __construct( int $code [, array $context = [] [, Throwable $previous]] )
```

The exceptable constructor.

Constructor arguments are `$code`, `$previous`, and an additional argument `$context` which accepts an array of values you provide (typically, details for the exception message). Note there is no `$message` argument; the exceptable message is generated based on the provided code.

parameters:
- int **`$code`**
  The Exceptable code.
- array **`$context`**
  Contextual information about the Exceptable.  Typically, used to provide details for the Exceptable message, but may include any information that should be available later (e.g., to loggers).
- Throwable **`$previous`**
  The previous Exception, if any.

**throws** `at\exceptable\ExceptableException` if the given code is invalid.

---

### Exceptable::getContext()
```
public array getContext( void )
```
Gets contextual information about this Exceptable.  Info will vary depending on what information is provided at runtime.  At a minimum, will include "`__severity__`" (the Exceptable's severity) and "`__rootMessage__`" (which will be empty if no previous exception exists).

**returns** an array of contextual information about the Exceptable.

---

### Exceptable::getRoot()
```
public Throwable getRoot( void )
```
Traverses the chain of previous exception(s) and gets the root exception.

**returns** the root exception (which will be the Exceptable instance if no previous exception exists).

---
