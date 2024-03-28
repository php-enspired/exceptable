<?php
/**
 * @package    at.exceptable
 * @subpackage tests
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2023
 * @license    GPL-3.0 (only)
 *
 *  This program is free software: you can redistribute it and/or modify it
 *  under the terms of the GNU General Public License, version 3.
 *  The right to apply the terms of later versions of the GPL is RESERVED.
 *
 *  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *  See the GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along with this program.
 *  If not, see <http://www.gnu.org/licenses/gpl-3.0.txt>.
 */
declare(strict_types = 1);

namespace at\exceptable\Tests;

use Closure,
  ErrorException,
  Exception,
  ResourceBundle,
  Stringable,
  Throwable;

use at\exceptable\ {
  Exceptable,
  ExceptableError,
  Handler\ExceptionHandler,
  Handler\Handler,
  Handler\Options,
  IsExceptable,
  Tests\TestCase
};

use Psr\Log\ {
  LoggerInterface as Logger,
  LogLevel
};

/**
 * Basic tests for the Exceptable error/exception/shutdown Handler.
 *
 * We can test that error/exception/shutdown handlers as registered,
 * but cannot test them actually working as phpunit intercepts uncaught errors/exceptions
 * before a (properly functioning) Handler can and interprets them as test failures.
 *
 * Due to this limitation, actual invocation of error/exception/shutdown handlers,
 * as well as the handle() method, must be manually tested outside of phpunit.
 *
 * @covers at\exceptable\Handler
 */
class HandlerTest extends TestCase {

  /** @var array[] List of reported error handling function invocations. */
  protected static $registered = [];

  /**
   * Notify test class of invocation of a builtin error handling function.
   *
   * @param string $function The function that was invoked
   * @param mixed  ...$args  Arguments received
   */
  public static function notify(string $function, ...$args) : void {
    self::$registered[] = [$function, $args];
  }

  /** {@inheritDoc} */
  public static function setUpBeforeClass() : void {
    // Ensure stubs are loaded.
    require_once __DIR__ . "/stubs.php";
  }

  /** @param array[] List of errors/exceptions reported handled during tests. */
  protected $stack = [];

  /** {@inheritDoc} */
  protected function setUp() : void {
    // Reset error/exception stack between tests.
    $this->stack = [];
    self::$registered = [];
  }

  public function testDebugModeDisabledByDefault() : void {
    $handler = new Handler(new Options());
    $handler->onException($this->exceptionHandler(true));

    $handler->try($this->throwCallback(new Exception("should not be logged")));
    $off = $handler->debugLog();
    $this->assertIsArray($off, "debugLog() did not return an array");
    $this->assertEmpty($off, "debug mode logged items by default");
  }

  public function testDebugModeLogsWhenEnabled() : void {
    $handler = new Handler(new Options());
    $handler->onException($this->exceptionHandler(true));

    $handler->options->debug = true;
    $e = new Exception("should be logged");
    $handler->try($this->throwCallback($e));
    $this->assertExceptionLogged($handler, 0, $e, true);
  }

  public function testDebugModeStopsLoggingWhenDisabled() : void {
    $handler = new Handler(new Options());
    $handler->onException($this->exceptionHandler(true));

    $handler->options->debug = true;
    $handler->options->debug = false;
    $handler->try($this->throwCallback(new Exception("should not be logged")));
    $this->assertEmpty($handler->debugLog(), "debug mode logged items after disabled");
  }

  public function testHandleException() : void {
    $e = new Exception("should be handled in the end");
    (new Handler(new Options()))
      ->onException($this->exceptionHandler(false))
      ->onException($this->exceptionHandler(true), Mismatched::class)
      ->onException($this->exceptionHandler(true))
      ->handleException($e);

    $this->assertExceptionHandled(0, $e, false);
    $this->assertExceptionHandled(1, $e, true);
  }

  public function testHandleExceptionFailure() : void {
    $e = new Exception("this one slips through");
    $this->expectThrowable(
      (ExceptableError::UncaughtException)([], $e),
      self::EXPECT_THROWABLE_CODE | self::EXPECT_THROWABLE_MESSAGE
    );

    $h = (new Handler(new Options()))
      ->onException($this->exceptionHandler(false))
      ->onException($this->exceptionHandler(true), Mismatched::class)
      ->handleException($e);
  }

  public function testLogsError() : void {
    $handler = new Handler(new Options());

    $logger = new TestLogger();
    $handler->setLogger($logger);

    $code = E_WARNING;
    $message = "Warning: example";
    $handler->handleError($code, $message, __FILE__, __LINE__);

    $this->assertCount(1, $logger->log);
    $log = $logger->log[0];
    $this->assertSame(LogLevel::WARNING, $log[0]);
    $this->assertSame($message, $log[1]);
  }

  public function testLogsDebugError() : void {
    $handler = new Handler(new Options());

    $logger = new TestLogger();
    $handler->setLogger($logger);
    $handler->options->debug = true;

    $code = E_WARNING;
    $message = "Warning: example";
    $handler->handleError($code, $message, __FILE__, __LINE__);

    $this->assertCount(2, $logger->log);

    $debug = $logger->log[0];
    $this->assertSame(LogLevel::DEBUG, $debug[0]);
    $this->assertSame($message, $debug[1]);

    $warning = $logger->log[1];
    $this->assertSame(LogLevel::WARNING, $warning[0]);
    $this->assertSame($message, $warning[1]);
  }

  public function testLogsException() : void {
    $this->markTestIncomplete();
  }

  public function testRegister() : void {
    $handler = (new Handler(new Options()))->register();

    $this->assertTrue(
      $this->getNonpublicProperty($handler, "registered"),
      "register() did not set \$registered flag"
    );

    $this->assertCount(3, self::$registered, "register() did not invoke registeration functions");
    $registered = array_column(self::$registered, null, 0);

    $this->assertArrayHasKey(
      "set_error_handler",
      $registered,
      "set_error_handler() was not invoked"
    );
    $this->assertEquals(
      $handler->handleError(...),
      $registered["set_error_handler"][1][0],
      "register() did not register Handler->handleError()"
    );
    $this->assertSame(
      -1,
      $registered["set_error_handler"][1][1],
      "register() did not register Handler->handleError() for all error types"
    );

    $this->assertArrayHasKey(
      "set_exception_handler",
      $registered,
      "set_exception_handler() was not invoked"
    );
    $this->assertEquals(
      $handler->handleException(...),
      $registered["set_exception_handler"][1][0],
      "register() did not register Handler->handleException()"
    );

    $this->assertArrayHasKey(
      "register_shutdown_function",
      $registered,
      "register_shutdown_function() was not invoked"
    );
    $this->assertEquals(
      $handler->handleShutdown(...),
      $registered["register_shutdown_function"][1][0],
      "register() did not register Handler->handleShutdown()"
    );
    $this->assertEmpty(
      $registered["register_shutdown_function"][1][1],
      "register() registered Handler->handleShutdown() with additional arguments"
    );
  }

  public function testTryCallback() : void {
    $e = new Exception("should be handled");
    (new Handler(new Options()))->onException($this->exceptionHandler(true))
      ->try($this->throwCallback($e));

    $this->assertExceptionHandled(0, $e, true);
  }

  public function testUnregister() : void {
    $handler = (new Handler(new Options()))->register();
    // clear registry
    self::$registered = [];
    $handler->unregister();

    $this->assertFalse(
      $this->getNonpublicProperty($handler, "registered"),
      "unregister() did not remove \$registered flag"
    );

    $this->assertCount(2, self::$registered, "unregister() did not invoke unregisteration functions");
    $registered = array_column(self::$registered, null, 0);

    $this->assertArrayHasKey(
      "restore_error_handler",
      $registered,
      "restore_error_handler() was not invoked"
    );

    $this->assertArrayHasKey(
      "restore_exception_handler",
      $registered,
      "restore_exception_handler() was not invoked"
    );
  }

  /**
   * @param int       $index
   * @param Throwable $e
   * @param bool      $success
   */
  protected function assertErrorHandled(int $i, array $error, bool $success) : void {
    if (! isset($this->stack[$i]) || ! is_array($this->stack[$i][0])) {
      $this->fail("no error [{$i}] was handled");
    }

    [$severity, $message, $file, $line] = $error + [null, null, null, null];

    if (isset($severity)) {
      $this->assertSame(
        $severity,
        $this->stack[$i][0][0],
        "error [{$i}] did not have expected severity {$severity}"
      );
    }

    if (isset($message)) {
      $this->assertSame(
        $message,
        $this->stack[$i][0][1],
        "error [{$i}] did not have expected message {$message}"
      );
    }

    if (isset($file)) {
      $this->assertSame(
        $file,
        $this->stack[$i][0][2],
        "error [{$i}] did not have expected file {$file}"
      );
    }

    if (isset($line)) {
      $this->assertSame(
        $line,
        $this->stack[$i][0][3],
        "error [{$i}] did not have expected line {$line}"
      );
    }

    $h ?
      $this->assertTrue($this->stack[$i][1], "error [{$i}] was not handled successfully") :
      $this->assertFalse($this->stack[$i][1], "error [{$i}] was handled successfully");
  }

  /**
   *
   */
  protected function assertErrorLogged(
    Handler $handler,
    int $i,
    array $error,
    bool $success
  ) : void {
    $log = $handler->debugLog();
    $this->assertIsArray($log);
    $this->assertArrayHasKey($i);
    $logItem = $log[$i];

    $this->assertArrayHasKey("time", $logItem, "[time] is missing");
    $this->assertIsFloat($logItem["time"], "[time] did not log as microtime");

    $this->assertArrayHasKey("type", $logItem, "[type] is missing");
    $this->assertSame("exception", $logItem["type"], "[type] was not logged as 'exception'");

    $this->assertArrayHasKey("handled", $logItem, "[handled] is missing");
    $success ?
      $this->assertTrue($logItem["handled"], "[handled] was not logged as true") :
      $this->assertFalse($logItem["handled"], "[handled] was not logged as false");

    [$code, $message, $controlled, $file, $line] = $error + [null, null, null, null, null];

    $this->assertArrayHasKey("code", $logItem, "[code] is missing");
    if (isset($code)) {
      $this->assertSame($code, $logItem["code"], "[code] was not logged as {$code}]");
    }

    $this->assertArrayHasKey("message", $logItem, "[message] is missing");
    if (isset($message)) {
      $this->assertSame($message, $logItem["message"], "[message] was not logged as '{$message}'");
    }

    $this->assertArrayHasKey("controlled", $logItem, "[controlled] is missing");
    if (isset($controlled)) {
      $this->assertSame(
        $controlled,
        $logItem["controlled"],
        "[controlled] was not logged as {$this->asString($controlled)}"
      );
    }

    $this->assertArrayHasKey("file", $logItem, "[file] is missing");
    if (isset($file)) {
      $this->assertSame($file, $logItem["file"], "[file] was not logged as '{$file}'");
    }

    $this->assertArrayHasKey("line", $logItem, "[line] is missing");
    if (isset($line)) {
      $this->assertSame($line, $logItem["line"], "[line] was not logged as {$line}");
    }
  }

  /**
   * @param int       $index
   * @param Throwable $e
   * @param bool      $success
   */
  protected function assertExceptionHandled(int $i, Throwable $e, bool $success) : void {
    if (! isset($this->stack[$i]) || ! $this->stack[$i][0] instanceof Throwable) {
      $this->fail("no exception [{$i}] was handled");
    }

    $this->assertSame($e, $this->stack[$i][0]);
    $success ?
      $this->assertTrue($this->stack[$i][1], "exception [{$i}] was not handled successfully") :
      $this->assertFalse($this->stack[$i][1], "exception [{$i}] was handled successfully");
  }

  /**
   * @param Handler   $handler
   * @param int       $index
   * @param Throwable $e
   * @param bool      $handled
   */
  protected function assertExceptionLogged(
    Handler $handler,
    int $i,
    Throwable $e,
    bool $success
  ) : void {
    $log = $handler->debugLog();
    $this->assertArrayHasKey($i, $log, "no LogEntry [{$i}] was logged");
    $logItem = $log[$i];

    $this->assertIsFloat($logItem->time, "LogEntry->time did not log as microtime");
    $success ?
      $this->assertTrue($logItem->handled, "LogEntry->handled was not logged as true") :
      $this->assertFalse($logItem->handled, "LogEntry->handled was not logged as false");
    $this->assertSame(
      $e,
      $logItem->exception,
      "LogEntry->exception did not log expected exception '{$this->asString($e)}'"
    );
  }

  /**
   * Makes an error handler and logs usage.
   *
   * @param bool $success Should handler succeed?
   * @return callable
   */
  protected function errorHandler(bool $success) : callable {
    return function ($c, $m, $f, $l) use ($success) {
      $this->stack[] = [$c, $m, $f, $l];
      return $success;
    };
  }

  /**
   * Makes an exception handler and logs usage.
   *
   * @param bool $success Should handler succeed?
   * @return ExceptionHandler
   */
  protected function exceptionHandler(bool $success) : ExceptionHandler {
    $callback = function (Throwable $t) use ($success) : bool {
      $this->stack[] = [$t, $success];
      return $success;
    };
    return new class($callback) implements ExceptionHandler {
      public function __construct(protected Closure $callback) {}
      public function run(Throwable $t) : bool {
        return ($this->callback)($t);
      }
    };
  }

  /**
   * Makes a callback that throws an exception.
   *
   * @param Throwable $e The exception to throw
   * @return callable
   */
  protected function throwCallback(Throwable $e) : callable {
    return function () use ($e) {
      throw $e;
    };
  }

  /**
   * Makes a callback that triggers an error.
   *
   * @param int    $c Error code
   * #param string $m Error message
   * @return callable
   */
  protected function triggerCallback(int $c, string $m = "") : callable {
    return function () use ($c, $m) {
      trigger_error($m, $c);
    };
  }
}

/** Default Test Logger class. */
class TestLogger implements Logger {

  public $log = [];

  public function emergency(string|Stringable $message, array $context = array()) : void {
    $this->log("emergency", $message, $context);
  }
  public function alert(string|Stringable $message, array $context = array()) : void {
    $this->log("alert", $message, $context);
  }
  public function critical(string|Stringable $message, array $context = array()) : void {
    $this->log("critical", $message, $context);
  }
  public function error(string|Stringable $message, array $context = array()) : void {
    $this->log("error", $message, $context);
  }
  public function warning(string|Stringable $message, array $context = array()) : void {
    $this->log("warning", $message, $context);
  }
  public function notice(string|Stringable $message, array $context = array()) : void {
    $this->log("notice", $message, $context);
  }
  public function info(string|Stringable $message, array $context = array()) : void {
    $this->log("info", $message, $context);
  }
  public function debug(string|Stringable $message, array $context = array()) : void {
    $this->log("debug", $message, $context);
  }
  public function log($level, string|Stringable $message, array $context = array()) : void {
    $this->log[] = [$level, $message, $context];
  }
}
