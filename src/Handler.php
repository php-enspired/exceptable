<?php
/**
 * @package    at.exceptable
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
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

namespace at\exceptable;

use ErrorException,
    Throwable;
use at\exceptable\Exceptable;

class Handler {

  /** @type _Handler[]  list of registered error handlers. */
  private $_errorHandlers = [];

  /** @type _Handler[]  list of registered exception handlers. */
  private $_exceptionHandlers = [];

  /** @type _Handler[]  list of registered shutdown handlers. */
  private $_shutdownHandlers = [];

  /** @type bool  is this Handler currently registered (active)? */
  private $_registered = false;

  /** @type int  error types which should be thrown as ErrorExceptions. */
  private $_throw = 0;

  /**
   * registers this handler to invoke a callback, and then restores the previous handler(s).
   *
   * @param callable $callback    the callback to execute
   * @param mixed    …$arguments  arguments to pass to the callback
   * @return mixed                the value returned from the callback
   */
  public function during(callable $callback, ...$arguments) {
    $this->register();
    $value = $callback(...$arguments);
    $this->unregister();
    return $value;
  }

  /**
   * adds an error handler.
   * @see <http://php.net/set_error_handler> $error_handler
   *
   * @param callable $handler  the error handler to add
   * @param int      $types    the error type(s) to trigger this handler
   *                           (bitmask of E_* constants, or omit for "any severity")
   * @return Handler           $this
   */
  public function onError(callable $handler, int $types=null) : Handler {
    $this->_errorHandlers[] = new _Handler($handler, _Handler::TYPE_ERROR, $types);
    return $this;
  }

  /**
   * adds an exception handler.
   * @see <http://php.net/set_exception_handler> $exception_handler
   *
   * @param callable $handler   the exception handler to add
   * @param int      $severity  the exception severity to trigger this handler
   *                            (one of Exceptable::ERROR|WARNING|NOTICE, or omit for "any severity")
   * @return Handler            $this
   */
  public function onException(callable $handler, int $severity=null) : Handler {
    $this->_exceptionHandlers[] = new _Handler($handler, _Handler::TYPE_EXCEPTION, $severity);
    return $this;
  }

  /**
   * adds a shutdown handler.
   * @see <http:/php.net/register_shutdown_handler> $callback
   *
   * @param callable $handler    the shutdown handler to add
   * @param mixed    $arguments  optional agrs to pass to shutdown handler when invoked
   * @return Handler             $this
   */
  public function onShutdown(callable $handler, ...$arguments) : Handler {
    $this->_shutdownHandlers[] = (new _Handler($handler, _Handler::TYPE_SHUTDOWN))
      ->defaultArguments($arguments);
    return $this;
  }

  /**
   * registers this Handler's error, exception, and shutdown handlers.
   *
   * @return Handler  $this
   */
  public function register() : Handler {
    set_error_handler(function(...$args) { return $this->_error(...$args); });
    set_exception_handler(function(...$args) { return $this->_exception(...$args); });
    register_shutdown_function(function(...$args) { return $this->_shutdown(...$args); });
    $this->_registered = true;
    return $this;
  }

  /**
   * specifies the ErrorException class for this handler to use.
   * @todo keep this?
   *
   * @param string $fqcn  fully qualified ErrorException classname
   * @return Handler      $this
   */
  public function setErrorExceptionClass(string $fqcn) : Handler {
    throw new ExceptableException('not yet implemented');
  }

  /**
   * sets error types which should be thrown as ErrorExceptions.
   *
   * @param int $types  the error types to be thrown
   *                    (defaults to E_ERROR|E_WARNING; use 0 to stop throwing)
   * @return Handler    $this
   */
  public function throw(int $types=E_ERROR|E_WARNING) : Handler {
    $this->_throw = $types;
    return $this;
  }

  /**
   * un-registers this Handler.
   *
   * @return Handler  $this
   */
  public function unregister() : Handler {
    restore_error_handler();
    restore_exception_handler();
    // shutdown functions can't be unregistered; just have to flag them so they're non-ops  :(
    $this->_registered = false;
    return $this;
  }

  /**
   * handles php errors.
   *
   * @param int    $s        error severity
   * @param string $m        error message
   * @param string $f        error file
   * @param int    $l        error line
   * @param array  $c        error context
   * @throws ErrorException  if error severity matches $_throw setting
   * @return bool            true if error handled; false if php's error handler should continue
   */
  protected function _error(int $s, string $m, string $f, int $l, array $c) : bool {
    if (! $this->_registered) {
      return false;
    }

    if (($s & $this->_throw) === $s) {
      throw new ErrorException($m, 0, $s, $f, $l);
    }

    foreach ($this->_errorHandlers as $handler) {
      if ($handler->handles(_Handler::TYPE_ERROR, $s) && $handler->handle($s, $m, $f, $l, $c)) {
        return true;
      }
    }
    return false;
  }

  /**
   * handles uncaught exceptions.
   *
   * @param Throwable $e          the exception
   * @throws ExceptableException  if no registered handler handles the exception
   */
  protected function _exception(Throwable $e) {
    if (! $this->_registered) {
      return;
    }

    $severity = method_exists($e, 'getSeverity') ? $e->getSeverity() : Exceptable::ERROR;
    foreach ($this->_exceptionHandlers as $handler) {
      if ($handler->handles(_Handler::TYPE_EXCEPTION, $severity) && $handler->handle($e)) {
        return;
      }
    }

    throw new ExceptableException(ExceptableException::UNCAUGHT_EXCEPTION, $e);
  }

  /**
   * handles shutdown sequence.
   *
   * @throws ErrorException  if shutdown is due to a fatal error
   */
  protected function _shutdown() {
    if (! $this->_registered) {
      return;
    }

    $e = error_get_last();
    if ($e && $e['type'] === E_ERROR) {
      $this->_error($e['type'], $e['message'], $e['file'], $e['line'], $e['context'] ?? []);
    }

    foreach ($this->_shutdownHandlers as $handler) {
      $handler();
    }
  }
}

/** @internal  utility class for wrapping callables as error/exception/shutdown handlers. */
class _Handler {

  const TYPE_ERROR = 1;
  const TYPE_EXCEPTION = 2;
  const TYPE_SHUTDOWN = 3;

  const ANY_SEVERITY = -1;

  protected $_arguments = [];

  protected $_handler;
  protected $_type;
  protected $_severity;

  public function __construct(callable $handler, int $type, int $severity=null) {
    $this->_handler = $handler;
    $this->_type = $type;
    $this->_severity = $severity ?? self::ANY_SEVERITY;
  }

  /** @internal  invokes the callback with given arguments. */
  public function handle(...$arguments) : bool {
    try {
      return (($this->_handler)(...($arguments + $this->_arguments)) === true) ?: false;
    } catch (Throwable $e) {
      throw new ExceptableException(
        ExceptableException::INVALID_HANDLER,
        $e,
        ['type' => $this->_type()]
      );
    }
  }

  /** @internal  checks whether this handler is registered for the given severity. */
  public function handles(int $type, int $severity) : bool {
    return $type === $this->_type &&
      ($this->_severity === self::ANY_SEVERITY || ($severity & $this->_severity) === $severity);
  }

  /** @internal  specifies default arguments to pass to handler. */
  public function defaultArguments(array $arguments) {
    $this->_arguments = $arguments;
    return $this;
  }

  /** @internal  gets a string representation of the handler type. */
  protected function _type() : string {
    switch ($this->_type) {
      case self::TYPE_ERROR :
        return 'error';
      case self::TYPE_EXCEPTION :
        return 'exception';
      case self::TYPE_SHUTDOWN :
        return 'shutdown';
      default :
        return (string) $this->_type;
    }
  }
}
