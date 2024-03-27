<?php
/**
 * @package    at.exceptable
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2024
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

namespace at\exceptable\Handler;

use ErrorException,
  SplObjectStorage as ObjectStore,
  Throwable;

use at\exceptable\ {
  ExceptableError,
  Handler\ErrorHandler,
  Handler\ErrorLogEntry,
  Handler\ExceptionHandler,
  Handler\ExceptionLogEntry,
  Handler\LogEntry,
  Handler\Options,
  Handler\ShutdownHandler,
  Spl\LogicException,
  Spl\RuntimeException
};

use Psr\Log\ {
  LoggerAwareInterface as LoggerAware,
  LoggerInterface as Logger,
  LogLevel
};

class Handler implements LoggerAware {

  /** @var ErrorHandler[][] Registered error handlers, grouped by error type(s) to handle. */
  protected array $errorHandlers = [];

  /** @var ObjectStore<Error: ExceptionHandler[]> Registered exceptable handlers, grouped by Error case. */
  protected ObjectStore $exceptableHandlers;

  /** @var ExceptionHandler[][] Registered exception handlers, grouped by Throwable type and code to handle. */
  protected array $exceptionHandlers = [];

  /** @var LogEntry[] Runtime log of errors/exceptions. */
  protected array $log = [];

  /** Is this Handler registered (active)? */
  protected bool $registered = false;

  /** @var ShutdownHandler[] Registered shutdown handlers and their arguments. */
  protected array $shutdownHandlers = [];

  public function __construct(
    /** @var Options Runtime options for this Handler. */
    public Options $options,
    /** @var Logger Logging mechanism. */
    protected ? Logger $logger = null
  ) {
    $this->exceptableHandlers = new ObjectStore();
  }

  /**
   * Gets a log of errors/exceptions encountered while debug mode was active.
   *
   * @return LogEntry[]
   */
  public function debugLog() : array {
    return $this->log;
  }

  /** @see https://php.net/set_error_handler $callback */
  public function handleError(int $c, string $m, string $f, int $l) : bool {
    $this->throwIfMatches($c, $m, $f, $l);

    $error = ["code" => $c, "message" => $m, "file" => $f, "line" => $l];
    if (! $this->options->scream && error_reporting() === 0) {
      $this->logError(true, $error);
      return true;
    }

    foreach ($this->errorHandlers as $severity => $handlers) {
      if (($c & $severity) === $c) {
        foreach ($handlers as $handler) {
          if ($handler->run($c, $m, $f, $l) === true) {
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
   * @phan-suppress PhanTypeNoPropertiesForeach
   *  $this->exceptionHandlers is an array of arrays of ExceptionHandlers.
   *
   * @param Throwable $t The uncaught exception
   * @throws RuntimeException ExceptableError::UncaughtException If no registered handler handles the exception
   */
  public function handleException(Throwable $t) : void {
    $type = get_class($t);
    $code = $t->getCode();

    // error case
    $x = ($t instanceof Exceptable) ? $t : (ExceptableError::UncaughtException)([], $t);
    foreach ($this->exceptionHandlers[Error::class] as $error => $handlers) {
      if ($x->has($error)) {
        foreach ($handlers as $handler) {
          if ($this->runExceptionHandler($handler, $t)) {
            $this->logException(true, $t);
            return;
          }
        }
      }
    }

    // exact type and code
    if (! empty($this->exceptionHandlers[$type][$code])) {
      foreach ($this->exceptionHandlers[$type][$code] as $handler) {
        if ($this->runExceptionHandler($handler, $t)) {
          $this->logException(true, $t);
          return;
        }
      }
    }

    // work up inheritance chain with catch-all code
    if (! empty($this->exceptionHandlers[$type][0])) {
      do {
        foreach ($this->exceptionHandlers[$type][0] ?? [] as $handler) {
          if ($this->runExceptionHandler($handler, $t)) {
            $this->logException(true, $t);
            return;
          }
        }

        $type = get_parent_class($type);
      } while (! empty($type));
    }

    // try any lowest-level catch-all handlers
    if (! empty($this->exceptionHandlers[Throwable::class][0])) {
      foreach ($this->exceptionHandlers[Throwable::class][0] ?? [] as $handler) {
        if ($this->runExceptionHandler($handler, $t)) {
          $this->logException(true, $t);
          return;
        }
      }
    }

    $this->logException(false, $t);
    throw (ExceptableError::UncaughtException)([], $t);
  }

  /**
   * Handles shutdown sequence;
   * triggers errorHandler() if shutdown was due to a fatal error.
   */
  public function handleShutdown() : void {
    if (! $this->registered) {
      return;
    }

    // handle the last error, if it was the cause of the shutdown
    $e = error_get_last();
    if ($e && $e['type'] === E_ERROR) {
      $this->handleError($e['type'], $e['message'], $e['file'], $e['line']);
    }

    foreach ($this->shutdownHandlers as $handler) {
      $handler->run();
    }
  }

  /**
   * Adds an error handler.
   *
   * @param ErrorHandler $handler The error handler to add
   * @param int $types The error type(s) to trigger this handler (bitmask of E_* constants); defaults to all
   * @return Handler $this
   */
  public function onError(ErrorHandler $handler, int $types = -1) : Handler {
    $this->errorHandlers[$types][] = $handler;

    return $this;
  }

  /**
   * Adds an exception handler for Exceptables.
   * This is the same as onException(), but matches against Error cases.
   *
   * @param ExceptionHandler $handler The exception handler to add
   * @param string ...$errors Error case(s) the handler should handle
   * @return Handler $this
   */
  public function onExceptable(ExceptionHandler $handler, Error ...$errors) : Handler {
    foreach ($errors as $error) {
      // false positive (trouble with type annotation)
      // @phan-suppress-next-line PhanTypeMismatchProperty
      $this->exceptableHandlers[$error] = [...($this->exceptableHandlers[$error] ?? []), $handler];
    }

    return $this;
  }

  /**
   * Adds an exception handler.
   *
   * @param ExceptionHandler $handler The exception handler to add
   * @param string $catch Exception FQCN this handler should handle; defaults to any
   * @param int $code Exception code this handler should handle; defaults to any
   * @return Handler $this
   */
  public function onException(ExceptionHandler $handler, string $catch = Throwable::class, int $code = 0) : Handler {
    // false positive (trouble with type annotation)
    // @phan-suppress-next-line PhanTypeMismatchProperty
    $this->exceptionHandlers[$catch][$code][] = $handler;

    return $this;
  }

  /**
   * Adds a shutdown handler.
   *
   * @param ShutdownHandler $handler The shutdown handler to add
   * @return Handler $this
   */
  public function onShutdown(ShutdownHandler $handler) : Handler {
    $this->shutdownHandlers[] = $handler;

    return $this;
  }

  /**
   * Registers this Handler's error, exception, and shutdown handlers.
   *
   * @return Handler $this
   */
  public function register() : Handler {
    set_error_handler($this->handleError(...), -1);
    set_exception_handler($this->handleException(...));
    register_shutdown_function($this->handleShutdown(...));
    $this->registered = true;

    return $this;
  }

  /** @see Logger::setLogger */
  public function setLogger(Logger $logger) : void {
    $this->logger = $logger;
  }

  /**
   * Registers this handler and tries to invoke a callback, and then restores the previous handler(s).
   *
   * @param callable $callback The callback to run
   * @throws RuntimeException ExceptableError::UncaughtException if callback throws and no registered Handler handles it.
   * @return mixed The handled exception on failure; the value returned from the callback otherwise
   */
  public function try(callable $callback) {
    $registered = $this->registered;
    if (! $registered) {
      $this->register();
    }

    try {
      $value = $callback();
    } catch (Throwable $t) {
      $this->handleException($t);
    }

    if (! $registered) {
      $this->unregister();
    }

    return $t ?? $value ?? null;
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
    return match ($code) {
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
      E_USER_DEPRECATED => LogLevel::INFO,
      default => LogLevel::NOTICE
    };
  }

  /**
   * Logs an error according to debug settings and logger availability.
   *
   * The following information is passed to the logger:
   *  - float $time Unixtime error was logged, with microsecond precision
   *  - string $type Always "error"
   *  - bool $handled Was this error handled by a registered handler?
   *  - bool $controlled Was this error suppressed by the error control operator?
   *  - int $code Error code
   *  - string $message Error message
   *  - string $file File
   *  - int $line Line
   * If debug mode is enabled, a backtrace is added as well:
   *  - array $trace Backtrace
   *
   * @param bool $handled Was this error handled by a registered handler?
   * @param array $details Error details
   */
  protected function logError(bool $handled, array $details) : void {
    $log = new ErrorLogEntry($details);
    $log->handled = $handled;

    if ($this->options->debug) {
      $log->trace = debug_backtrace();
      $this->log[] = $log;
    }

    if (isset($this->logger)) {
      if ($this->options->debug) {
        $this->logger->log(LogLevel::DEBUG, $log->message, $log->toArray());
      }

      if (! $handled) {
        $this->logger->log($this->getLogLevel($log->code), $log->message, $log->toArray());
      }
    }
  }

  /**
   * Logs an exception according to debug settings and logger availability.
   *
   * The following information is passed to the logger:
   *  - float $time Unixtime error was logged, with microsecond precision
   *  - string $type Always "exception"
   *  - bool $handled Was this error handled by a registered handler?
   *  - int $exception Exception
   *
   * @param bool $handled Was this exception handled by a registered handler?
   * @param Throwable $t The exception instance
   */
  protected function logException(bool $handled, Throwable $t) : void {
    $log = new ExceptionLogEntry($t);
    $log->handled = $handled;

    if ($this->options->debug) {
      $this->log[] = $log;
    }

    if (isset($this->logger)) {
      if ($this->options->debug) {
        $this->logger->log(LogLevel::DEBUG, $t->getMessage(), $log->toArray());
      }

      if(! $handled) {
        $this->logger->log(LogLevel::CRITICAL, $t->getMessage(), $log->toArray());
      }
    }
  }

  /**
   * Invokes an exception handler.
   *
   * @param ExceptionHandler $handler The handler to invoke
   * @param Throwable $t The exception to handle
   * @return bool True if handler ran successfully; false otherwise
   * @throws LogicException ExceptableError::HandlerFailed if the handler errors
   */
  protected function runExceptionHandler(ExceptionHandler $handler, Throwable $t) : bool {
    try {
      if ($handler->run($t) === true) {
        if ($this->options->debug) {
          $this->logException(true, $t);
        }

        return true;
      }

      return false;
    } catch (Throwable $x) {
      throw (ExceptableError::HandlerFailed)(
        ["type" => gettype($handler), "unhandled" => $t],
        $x
      );
    }
  }

  /**
   * Throws an ErrorException if the given error code matches $throw setting.
   *
   * @param int $c Error code
   * @param string $m Error message
   * @param string $f Error file
   * @param int $l Error line
   * @throws ErrorException If error severity matches $throw setting
   */
  protected function throwIfMatches(int $c, string $m, string $f, int $l) : void {
    if (($c & $this->options->throw) === $c) {
      throw new ErrorException($m, $c, $c, $f, $l);
    }
  }
}
