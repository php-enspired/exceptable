`at\exceptable\Exception`
=========================

Base implementation of the Exceptable interface.  Your Exceptables can extend from this class and need define only their exceptable codes and info (see [usage examples]()).

constants
---------

### Handler::INFO

Every child class must define codes for its error cases.  `INFO` is a code → details map of information about each error:

```
array INFO {
  array <code> {
    string "message"     the exception message
    int    "severity"    the exception severity
    string "tr_message"  a translatable exception message with {}-delimited placeholders
    mixed  ...           implementation-specific additional info
  }
  ...
}
```

---

inherited methods
-----------------

See [The Exceptable Interface](https://github.com/php-enspired/exceptable/wiki/API:-The-Exceptable-Interface) for details.
- `array getInfo( int $code )`
- `bool hasInfo( int $code )`
- `void __construct( int $code [, array $context = [] [, Throwable $previous]] )`
- `array getContext( void )`
- `Throwable getRoot( void )`
- `int getSeverity( void )`

---

protected methods
-----------------

The Exception constructor uses these methods to prepare the Exceptable message. If your Exceptable needs to change/modify this behavior,[*](#) start by looking at overriding these methods.

> [*](#) _it probably doesn't._

### Exception::_makeMessage()
```
protected ?string _makeMessage( int $code )
```  

Validates and/or provides a default exception message.  Any context passed to the constructor will have already been added (use `getContext()` to access it) when this method is invoked.

parameters:  
- int **`$code`**  
  The exception code returned from `_makeCode()`.

**returns** an exception message appropriate for the exception code.

---