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

namespace at\exceptable;

use Throwable;

use at\exceptable\ {
  Error,
  Exceptable,
  ExceptableError
};

/**
 * Implementation of the Result pattern: provides a [result, error] tuple
 *  for functional error handling without throwing exceptions.
 */
class Result {

  /**
   * Factory: builds a Result for an error case.
   *
   * @param Error $error The error case
   * @return Result
   */
  public static function error(Error $error) : static {
    return new static(null, $error);
  }

  /**
   * Factory: builds a Result for a return case.
   *
   * @param mixed $value The return value
   * @return Result
   */
  public static function return($value) : static {
    return new static($value, null);
  }

  /**
   * Factory: invokes a callable and builds a Result from its return value or thrown exception.
   *
   * @param callable $callback The callable to invoke
   * @return Result
   */
  public static function try(callable $callback) : static {
    try {
      $result = $callback();
      if (! $result instanceof Result) {
        $result = static::return($result);
      }

      return $result;
    } catch (Throwable $t) {
      $error = ($t instanceof Exceptable) ?
        $t->error() :
        ExceptableError::UncaughtException;

      $result = static::error($error);
      $result->exception = $t;
      return $result;
    }
  }

  /**
   * Invokes a callable that returns a Result and returns its return value (or throws its error value).
   *
   * @param callable $callback The callback to unpack
   * @throws Throwable On error
   * @return mixed The callback's return value on success
   */
  public static function unpack(callable $callback) : mixed {
    $result = $callback();
    if (! $result instanceof Result) {
      return $result;
    }

    if ($result->isError()) {
      throw $result->exception();
    }

    return $result->value;
  }

  /** @internal */
  private Throwable $exception;

  /**
   * @param mixed $value The success value, if any
   * @param ?Error $error The error value, if any
   */
  private function __construct(
    public readonly mixed $value = null,
    public readonly ? Error $error = null
  ) {}

  /**
   * Is this an error Result?
   *
   * @return bool True if this is an error result; false otherwise
   */
  public function isError() : bool {
    return ! empty($this->error);
  }

  /**
   * Gets an exception for this Result, if it's an error result.
   *
   * @return Throwable|null
   */
  public function exception() : ? Throwable {
    if (! $this->isError()) {
      return null;
    }

    $this->exception ??= ($this->error)([]);

    return $this->exception;
  }
}
