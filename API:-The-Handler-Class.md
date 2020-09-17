`at\exceptable\Handler`
=======================

Manages a registry of callback functions ("handlers") for errors, uncaught exceptions, and shutdown.

instance methods
----------------

### Handler::during()
```
public mixed during( callable $callback [, mixed ...$arguments] )
``` 
Registers this handler to invoke a callback, and then restores the previous handler(s).

parameters:  

- callable **`$callback`**  
  The callback to invoke.
- mixed **`...$arguments`**  
  Argument(s) to pass to the callback.

**returns** the value returned from the callback.

---

### Handler::onError()
```
public Handler onError( callable $handler [, int $types] )
```

Adds an error handler.  

parameters: 
- callable **`$handler`**  
  The handler to add.  Must be suitable for use with [`set_error_handler`](http://php.net/set_error_handler).  If the handler returns `true`, then handling will stop (subsequently registered handlers will not be invoked).
- int **`$types`**  
  The error types the handler should be invoked for (a bitmask of `E_*` constants).  Defaults to "any error type."

**returns** the Handler instance.

---

### Handler::onException()
```
public Handler onException( callable $handler [, int $types] )
```  

Adds a handler for uncaught exceptions.  

parameters: 
- callable **`$handler`**  
  The handler to add.  Must be suitable for use with [`set_exception_handler`](http://php.net/set_exception_handler).  If the handler returns `true`, then handling will stop (subsequently registered handlers will not be invoked).
- int **`$severity`**  
  The exception severities the handler should be invoked for (a bitmask of `Exceptable` severity constants).  Defaults to "any severity."

**returns** the Handler instance.

---

### Handler::onShutdown()
```
public Handler onShutdown( callable $handler [, ...$arguments] )
```  

Adds a shutdown handler.  

_Note_, shutdown handlers should not be registered to handle fatal errors.  If the shutdown is due to a fatal error, the appropriate registered error handlers will be invoked.

parameters: 
- callable **`$handler`**  
  The handler to add.  Must be suitable for use with [`register_shutdown_function`](http://php.net/register_shutdown_function).
- mixed **`$arguments`**  
  Argument(s) to pass to the handler on shutdown.

**returns** the Handler instance.

---

### Handler::register()
```
public Handler register( void )
```  

Starts the Handler (makes its registered handlers "active").

**returns** the Handler instance.

---

### Handler::throw()
```
public Handler throw( [int $types] )
```  

Sets error types which should be intercepted and thrown as ErrorExceptions.

parameters:  
- int **`$types`**  
  The error types to be thrown.  Defaults to `E_ERROR|E_WARNING`; use `0` to stop throwing.

**returns** the Handler instance.

---

### Handler::unregister()
```
public Handler unregister( void )
```  

Stops the Handler (makes its registered handlers "inactive").  

**returns** the Handler instance.

---

### Handler::try()
```
public mixed try( callable $callback [, mixed ...$arguments] )
``` 
Tries invoking a callback, using the registered exception handler(s) to handle any uncaught exceptions.

parameters:  

- callable **`$callback`**  
  The callback to invoke.
- mixed **`...$arguments`**  
  Argument(s) to pass to the callback.

**returns** the value returned from the callback.

---