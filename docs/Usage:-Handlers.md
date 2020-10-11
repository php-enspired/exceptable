Exceptables are all about making error cases a more organized and integrated part of your application.  So, it makes sense that you'd need a way to handle non-Exceptable exceptions, regular PHP errors, and even the shutdown process (e.g., in the case of a fatal PHP error).

_Handlers_ are error handling objects that you can use to implement error handling on a fine-grained basis: specify handler(s) for errors or uncaught exceptions based on severity.
```php
<?php

use at\exceptable\Handler;

$handler = new Handler();
$handler->onError(function($s, $m, $f, $l, $c) { error_log($m); return true; })
  ->register();

trigger_error('foo!', E_USER_NOTICE);
// "foo!" will be logged
```

Note we included `return true` in the handler function: this tells the Handler that everything has been handled, and so no more handlers will be called.  Otherwise, the next handler (if any) would be called, and would finally fall back to PHP's internal error handler:
```php
<?php

use at\exceptable\Handler;

$handler = new Handler();
$handler->onError(function($s, $m, $f, $l, $c) { error_log("one: {$m}"); })
  ->onError(function($s, $m, $f, $l, $c) { error_log("two: {$m}"); })
  ->register();

trigger_error('foo!', E_USER_NOTICE);
// "one: foo!" and "two: foo!" will be logged
// Notice: foo! in ...
```

As mentioned above, handlers can be assigned based on error (or Exception) severity.  You can assign handlers in this way individually:
```php
<?php

use at\exceptable\Handler;

$handler = new Handler();
$handler->onError(
    function($s, $m, $f, $l, $c) { error_log("notice: {$m}"); },
    E_USER_NOTICE
  )
  ->onError(
    function($s, $m, $f, $l, $c) { error_log("warning: {$m}"); },
    E_USER_WARNING
  )
  ->onError(
    function($s, $m, $f, $l, $c) {
      error_log("warning or notice: {$m}");
      return true;
    },
    E_USER_WARNING|E_USER_NOTICE
  )
  ->register();

trigger_error('foo!', E_USER_NOTICE);
// "notice: foo!" and "warning or notice: foo!" will be logged

trigger_error('bar!', E_USER_WARNING);
// "warning: bar!" and "warning or notice: bar!" will be logged
```

Handlers can handle exceptions in the same way:
```php
<?php

use at\exceptable\Handler;

$handler = new Handler();
$handler->onException(function($e) { error_log($e->getMessage()); return true; })
  ->register();

throw new \Exception('foo!');
// "foo!" will be logged
```

Handlers can throw Errors (as ErrorExceptions):
```php
<?php

use at\exceptable\Handler;

$handler = new Handler();
// pass a bitmask of error types that should be thrown;
// defaults to E_ERROR|E_WARNING
$handler->throw(E_USER_NOTICE)
  ->register();

trigger_error('foo!', E_USER_NOTICE);
// Fatal error: Uncaught ErrorException: foo! in ...
```

Handlers can be turned off, or be used only _during_ a particular function call:
```php
<?php

use at\exceptable\Handler;

$handler = new Handler();
$handler->onError(function($s, $m, $f, $l, $c) { error_log($m); return true; })
  ->register();

trigger_error('foo!', E_USER_NOTICE);
// "foo!" will be logged

$handler->unregister();
trigger_error('foo!', E_USER_NOTICE);
// Notice: foo! in ...

$handler->during(function() { trigger_error('foo!', E_USER_NOTICE); });
// "foo!" will be logged

trigger_error('foo!', E_USER_NOTICE);
// Notice: foo! in ...
```
