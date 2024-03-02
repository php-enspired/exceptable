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
  ExceptableError
};

/**
 * Augmented interface for exceptional exceptions.
 * Exceptables uniquely identify specific error cases.
 *
 * Caution:
 *  - the implementing class must extend from Exception (or a subclass) and implement Exceptable.
 *  - implementations cannot extend from PDOException,
 *    because it breaks the Throwable interface (its getCode() returns a string).
 *
 * @property $error
 * @phan-suppress PhanCommentObjectInClassConstantType
 */
interface Exceptable extends Throwable {

  /**
   * The default (0) Error for this Exceptable.
   *
   * @var Error
   * @todo Add type constraint once php 8.2 support is dropped.
   */
  public const DEFAULT_ERROR = ExceptableError::UnknownError;

  /**
   * @param ?Error $e The Error case to build from
   * @param array $context Additional exception context
   * @param ?Throwable $previous Previous exception, if any
   * @return Exceptable A new Exceptable on success
   */
  public function __construct(Error $e = null, array $context = [], Throwable $previous = null);

  /**
   * Gets contextual information about this Exceptable.
   *
   * @return array Exceptable context, including:
   *  - string "__message__" The top-level exception message
   *  - string "__rootMessage__" The root exception message (may be the same as "__message__")
   *  - mixed  "__...__" Additional, implementation-specific information
   *  - mixed  "..." Additional context provided at time of error
   */
  public function context() : array;

  /**
   * Gets this Exceptable's Error case.
   *
   * @return Error The Error case this Exceptable was built from.
   */
  public function error() : Error;

  /**
   * Does this Exceptable contain the given Error case in its error chain?
   *
   * @param Error $e The Error case to compare against
   * @return bool True if the given Error belongs to this or a previous Exceptable; false otherwise
   */
  public function has(Error $e) : bool;

  /**
   * Checks whether this exception matches the given error case.
   *
   * @param Error $e The Error case to compare against
   * @return bool True if this Exceptable's Error case matches; false otherwise
   */
  public function is(Error $e) : bool;

  /**
   * Traverses the chain of previous exception(s) and gets the root exception.
   *
   * This may be the same as the top-level exception, if there are no previous exceptions.
   *
   * @return Throwable The root exception
   */
  public function root() : Throwable;
}
