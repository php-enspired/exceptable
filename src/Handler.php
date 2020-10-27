<?php
/**
 * @package    at.exceptable
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2020
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

namespace AT\Exceptable;

use ErrorException,
  Exception,
  Throwable;

use AT\Exceptable\ExceptableException;

use Psr\Log\ {
  LoggerAwareInterface as LoggerAware,
  LoggerInterface as Logger,
  LogLevel
};

/**
 * Exceptable Handler
 */
class Handler implements LoggerAware {

  /** @var bool Debug mode? */
  protected $debug = false;

  /** @var callable[][] List of registered error handlers, grouped by error type. */
  protected $errorHandlers = [];

  /** @var array[] List of errors/exceptions encountered in debug mode. */
  protected $errors = [];

  /**
   * @var callable[][][] $exceptionHandlers List of registered exception handlers,
   *  indexed by Throwable FQCN and code to catch.
   */
  protected $exceptionHandlers = [];

  /** @var Logger Logging mechanism. */
  protected $logger;

  /** @var array<callable,mixed[]>[] List of registered shutdown handlers. */
  protected $shutdownHandlers = [];

  /** @var bool Is this Handler currently registered (active)? */
  protected $registered = false;

  /** @var bool Ignore the error control operator? */
  protected $scream = false;

  /** @var int Error types which should be thrown as ErrorExceptions. */
  protected $throw = 0;

  /**
   * Sets debug mode (tracks and traces all errors and exceptions).
   *
   * @param bool $debug Debug?
   * @return Handler    $this
   */
  public function debug(bool $debug = true) : Handler {
    $this->debug = $debug;

    return $this;
  }

  /**
   * Gets a list of errors/exceptions encountered while debug mode was active.
   *
   * @return array[]
   */
  public function getDebugLog() : array {
    return $this->errors;
  }

  /**
   * Registers this handler to invoke a callback, and then restores the previous handler(s).
   *
   * @param callable $callback  The callback to execute
   * @param mixed ...$arguments Arguments to pass to the callback
   * @return mixed              The value returned from the callback
   */
  public function handle(callable $callback, ...$arguments) {
    $registered = $this->registered;
    if (! $registered) {
      $this->register();
    }

    $value = $callback(...$arguments);

    if (! $registered) {
      $this->unregister();
    }

    return $value;
  }

  /**
   * Handles php errors.
   *
   * @param int    $c       Error code
   * @param string $m       Error message
   * @param string $f       Error file
   * @param int    $l       Error line
   * @throws ErrorException If error severity matches $throw setting
   * @return bool           True if error handled; false if php's error handler should continue
   */
  public function handleError(int $c, string $m, string $f, int $l) : bool {
    $this->throwIfMatches($c, $m, $f, $l);

    $error = [
      "code" => $c,
      "message" => $m,
      "file" => $f,
      "line" => $l
    ];

    if (! $this->scream && error_reporting() === 0) {
      $this->logError(true, $error);
      return true;
    }

    foreach ($this->errorHandlers as $severity => $handlers) {
      if (($c & $severity) === $c) {
        foreach ($handlers as $handler) {
          if ($handler($c, $m, $f, $l) === true) {
            $this->logError(true, $error);
            return true;
          }
        }
      }
    }

    $this->logError(false, $error);
    return false;
  }

  /**
   * Handles uncaught exceptions.
   *
   * @param Throwable $e         The uncaught exception
   * @throws ExceptableException If no registered handler handles the exception
   */
  public function handleException(Throwable $e) : void {
    $type = get_class($e);
    $code = $e->getCode();

    // exact type and code
    foreach ($this->exceptionHandlers[$type][$code] ?? [] as $handler) {
      if ($this->runExceptionHandler($handler, $e)) {
        return;
      }
    }

    // work up inheritance chain with catch-all code
    do {
      foreach ($this->exceptionHandlers[$type][0] ?? [] as $handler) {
        if ($this->runExceptionHandler($handler, $e)) {
          return;
        }
      }

      $type = get_parent_class($type);
    } while (! empty($type));

    // try any lowest-level catch-all handlers
    foreach ($this->exceptionHandlers[Throwable::class][0] ?? [] as $handler) {
      if ($this->runExceptionHandler($handler, $e)) {
        return;
      }
    }

    $this->logException(false, $e);
    throw new ExceptableException(ExceptableException::UNCAUGHT_EXCEPTION, [], $e);
  }

  /**
   * Handles shutdown sequence;
   * triggers errorHandler() if shutdown was due to a fatal error.
   */
  public function handleShutdown() : void {
    if (! $this->registered) {
      return;
    }

    $e = error_get_last();
    if ($e && $e['type'] === E_ERROR) {
      $this->handleError($e['type'], $e['message'], $e['file'], $e['line']);
    }

    foreach ($this->shutdownHandlers as [$handler, $arguments]) {
      $handler(...$arguments);
    }
  }

  /**
   * Adds an error handler.
   * @see <http://php.net/set_error_handler> $error_handler
   *
   * @param callable $handler The error handler to add
   * @param int      $types   The error type(s) to trigger this handler
   *                           (bitmask of E_* constants)
   * @return Handler          $this
   */
  public function onError(callable $handler, int $types) : Handler {
    $this->errorHandlers[$types][] = $handler;

    return $this;
  }

  /**
   * Adds an exception handler.
   * @see <http://php.net/set_exception_handler> $exception_handler
   *
   * @param callable      $handler The exception handler to add
   * @param string|null   $catch   Exception FQCN this handler should handle (defaults to any)
   * @param int           $code    Exception code this handler should handle (defaults to any)
   * @return Handler               $this
   */
  public function onException(callable $handler, string $catch = null, int $code = 0) : Handler {
    $this->exceptionHandlers[$catch ?? Throwable::class][$code][] = $handler;

    return $this;
  }

  /**
   * Adds a shutdown handler.
   * @see <http:/php.net/register_shutdown_handler> $callback
   *
   * @param callable $handler   The shutdown handler to add
   * @param mixed ...$arguments Optional agrs to pass to shutdown handler when invoked
   * @return Handler            $this
   */
  public function onShutdown(callable $handler, ...$arguments) : Handler {
    $this->shutdownHandlers[] = [$handler, $arguments];

    return $this;
  }

  /**
   * Registers this Handler's error, exception, and shutdown handlers.
   *
   * @return Handler $this
   */
  public function register() : Handler {
    set_error_handler([$this, 'handleError'], -1);
    set_exception_handler([$this, 'handleException']);
    register_shutdown_function([$this, 'handleShutdown']);
    $this->registered = true;

    return $this;
  }

  /**
   * Sets whether the error control operator should be ignored.
   *
   * @param bool $scream Ignore the error control operator?
   * @return Handler $this
   */
  public function scream(bool $scream) : Handler {
    $this->scream = $scream;

    return $this;
  }

  /** @see https://www.php-fig.org/psr/psr-3/#4-psrlogloggerawareinterface */
  public function setLogger(Logger $logger) : void {
    $this->logger = $logger;
  }

  /**
   * Sets error types which should be thrown as ErrorExceptions.
   *
   * @param int $types The error types to be thrown
   *                    (defaults to E_ERROR|E_WARNING; use 0 to stop throwing)
   * @return Handler   $this
   */
  public function throwErrors(int $types = E_ERROR | E_WARNING) : Handler {
    $this->throw = $types;

    return $this;
  }

  /**
   * Invokes a callback and handles any exceptions using registered handlers.
   *
   * Will throw ErrorExceptions during invocation even if the instance is not registered.
   *
   * @param callable $callback   The callback to execute
   * @param mixed ...$arguments  Arguments to pass to the callback
   * @throws ExceptableException If an exception is thrown and no registered handler handles it
   * @return mixed               The value returned from the callback on success
   */
  public function try(callable $callback, ...$arguments) {
    try {
      $throwErrorExceptions = $this->throw > 0 && ! $this->registered;
      if ($throwErrorExceptions) {
        set_error_handler([$this, "throwIfMatches"], $this->throw);
      }
      return $callback(...$arguments);
    } catch (Throwable $e) {
      $this->handleException($e);
      return null;
    } finally {
      if ($throwErrorExceptions) {
        restore_error_handler();
      }
    }
  }

  /**
   * Un-registers this Handler.
   *
   * @return Handler $this
   */
  public function unregister() : Handler {
    restore_error_handler();
    restore_exception_handler();
    // shutdown functions can't be unregistered; just have to flag them so they're non-ops  :(
    $this->registered = false;

    return $this;
  }

  /**
   * Determines target logging level for a given error code.
   *
   * @param int $code Error code
   * @return string One of the LogLevel::* constants
   */
  protected function getLogLevel(int $code) : string {
    return [
      E_ERROR => LogLevel::CRITICAL,
      E_USER_ERROR => LogLevel::CRITICAL,
      E_WARNING => LogLevel::WARNING,
      E_USER_WARNING => LogLevel::WARNING,
      E_PARSE => LogLevel::ERROR,
      E_NOTICE => LogLevel::NOTICE,
      E_USER_NOTICE => LogLevel::NOTICE,
      E_STRICT => LogLevel::DEBUG,
      E_RECOVERABLE_ERROR => LogLevel::CRITICAL,
      E_DEPRECATED => LogLevel::INFO,
      E_USER_DEPRECATED => LogLevel::INFO
    ][$code] ?? LogLevel::NOTICE;
  }

  /**
   * Logs an error according to debug settings and logger availability.
   *
   * The following information is passed to the logger:
   *  - float  $time       Unixtime error was logged, with microsecond precision
   *  - string $type       Always "error"
   *  - bool   $handled    Was this error handled by a registered handler?
   *  - bool   $controlled Was this error suppressed by the error control operator?
   *  - int    $code       Error code
   *  - string $message    Error message
   *  - string $file       File
   *  - int    $line       Line
   * If debug mode is enabled, a backtrace is added as well:
   *  - array  $trace      Backtrace
   *
   * @param bool  $handled   Was this error handled by a registered handler?
   * @param array $error     Error details
   */
  protected function logError(bool $handled, array $error) : void {
    $error = [
      "time" => microtime(true),
      "type" => "error",
      "handled" => $handled,
      "controled" => error_reporting() === 0
    ] + $error + [
      "code" => 0,
      "message" => "unknown error",
      "file" => "unknown",
      "line" => 0
    ];

    if ($this->debug) {
      $error["trace"] = debug_backtrace();
      $this->errors[] = $error;
    }

    if (isset($this->logger)) {
      if ($this->debug) {
        $this->logger->log(LogLevel::DEBUG, $error["message"], $error);
      }

      if (! $handled) {
        $this->logger->log(
          $this->getLogLevel($error["code"]),
          $error["message"],
          $error
        );
      }
    }
  }

  /**
   * Logs an exception according to debug settings and logger availability.
   *
   * The following information is passed to the logger:
   *  - float  $time      Unixtime error was logged, with microsecond precision
   *  - string $type      Always "exception"
   *  - bool   $handled   Was this error handled by a registered handler?
   *  - int    $exception Exception
   *
   * @param bool      $handled Was this exception handled by a registered handler?
   * @param Throwable $e       The exception instance
   */
  protected function logException(bool $handled, Throwable $e) : void {
    $error = [
      "time" => microtime(true),
      "type" => "exception",
      "handled" => $handled,
      "exception" => $e
    ];

    if ($this->debug) {
      $this->errors[] = $error;
    }

    if (isset($this->logger)) {
      if ($this->debug) {
        $this->logger->log(LogLevel::DEBUG, $e->getMessage(), $error);
      }

      if(! $handled) {
        $this->logger->log(LogLevel::CRITICAL, $e->getMessage(), $error);
      }
    }
  }

  /**
   * Invokes an exception handler.
   *
   * @param callable  $handler   The handler to invoke
   * @param Throwable $e         The exception to handle
   * @return bool                True if handler ran successfully; false otherwise
   * @throws ExceptableException INVALID_HANDLER if the handler errors
   */
  protected function runExceptionHandler(callable $handler, Throwable $e) : bool {
    try {
      if ($handler($e) === true) {
        if ($this->debug) {
          $this->logException(true, $e);
        }

        return true;
      }

      return false;
    } catch (Throwable $x) {
      ExceptableException::throw(
        ExceptableException::INVALID_HANDLER,
        ["type" => gettype($handler), "unhandled" => $e],
        $x
      );
    }
  }

  /**
   * Throws an ErrorException if the given error code matches $throw setting.
   *
   * @param int    $c       Error code
   * @param string $m       Error message
   * @param string $f       Error file
   * @param int    $l       Error line
   * @throws ErrorException If error severity matches $throw setting
   */
  protected function throwIfMatches(int $c, string $m, string $f, int $l) : void {
    if (($c & $this->throw) === $c) {
      throw new ErrorException($m, $c, $c, $f, $l);
    }
  }
}
