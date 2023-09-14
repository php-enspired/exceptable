<?php
/**
 * @package    at.exceptable
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

namespace at\exceptable;

use Throwable;

use at\exceptable\ {
  ErrorCase,
  ExceptableError
};

/**
 * Augmented interface for exceptional exceptions.
 * Exceptables uniquely identify specific error cases by error case.
 *
 * Caution:
 *  - the implementing class must extend from Exception (or a subclass) and implement Exceptable.
 *  - implementations cannot extend from PDOException,
 *    because it breaks the Throwable interface (its getCode() returns a string).
 */
interface Exceptable extends Throwable, ExceptableInternals {

  /**
   * Creates a new Exceptable from the given Error Case.
   *
   * @param ErrorCode $case Error case
   * @param array $context Additional exception context
   * @param ?Throwable $previous Previous exception
   * @throws ExceptableError If Error is invalid
   */
  public static function fromCase(
    ErrorCase $case,
    array $context = [],
    ? Throwable $previous = null
  ) : Exceptable;

  /**
   * Gets the ErrorCase this Exceptable uses.
   *
   * @return ErrorCase An ErrorCase
   */
  public function case() : ErrorCase;

  /**
   * Gets contextual information about this exception.
   *
   * @return array Exception context, including:
   *  - string "__message__" The top-level exception message
   *  - string "__rootMessage__" The root exception message (may be the same as "__message__")
   *  - mixed  "__...__" Additional, implementation-specific information
   *  - mixed  "..." Additional context provided at time of error
   */
  public function context() : array;

  /**
   * Checks whether this exception matches the given error case.
   *
   * @param Throwable $e Subject exception
   * @param int $code Target code
   * @return bool True if exception class and code matches; false otherwise
   */
  public function is(ErrorCase $case) : bool;

  /**
   * Traverses the chain of previous exception(s) and gets the root exception.
   *
   * @return Throwable The root exception
   */
  public function root() : Throwable;
}

/** Public Exceptable methods which are intended for internal use only. */
interface ExceptableInternals {

  /**
   * Adjusts this exceptable's file/line to the previous stack frame
   *  (to account for where it's actually constructed vs. intended to be thrown from).
   *
   * @internal {@used-by} Exceptable::fromCase(), ErrorCase::throw()
   * @return Exceptable $this
   */
  public function _adjust(int $frame = 0) : Exceptable;
}
