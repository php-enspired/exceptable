<?php
/**
 * @package    at.exceptable
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2018
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

/**
 * augmented interface for exceptions.
 *
 * caution:
 *  - the implementing class must extend from a Throwable class (e.g., Exception).
 *  - if the implementing class extends ErrorException
 *    (which already has a (final) method getSeverity()),
 *    exceptable::getSeverity() will need to be aliased when the trait is used.
 *  - implementations cannot extend from PDOException,
 *    because it breaks the Throwable interface (its getCode() returns a string).
 */
interface Exceptable extends Throwable {

  /**
   * exception severity levels.
   *
   * @type int ERROR    error
   * @type int WARNING  warning
   * @type int NOTICE   notice
   */
  public const ERROR = E_ERROR;
  public const WARNING = E_WARNING;
  public const NOTICE = E_NOTICE;

  /**
   * gets information about a code known to the implementing class.
   *
   * @param int $code    the exception code to look up
   * @throws Exceptable  if the code is not known to the implementation
   * @return array       a map of info about the error condition
   */
  public static function getInfo(int $code) : array;

  /**
   * checks whether the implementation has info about the given code.
   *
   * @param int $code  the code to check
   * @return bool      true if the code is known; false otherwise
   */
  public static function hasInfo(int $code) : bool;

  /**
   * @param int       $code       exception code
   * @param array     $context    additional exception context
   * @param Throwable $previous   previous exception
   * @throws ExceptableException  if code is invalid
   */
  public function __construct(int $code, array $context = [], Throwable $previous = null);

  /**
   * gets contextual info about this exception.
   *
   * @return array  map of contextual info about this exception
   */
  public function getContext() : array;

  /**
   * traverses the chain of previous exception(s) and gets the root exception.
   *
   * @return Throwable  the root exception
   */
  public function getRoot() : Throwable;

  /**
   * gets exception severity.
   *
   * @return int  the exception severity
   */
  public function getSeverity() : int;
}
