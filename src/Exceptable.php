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
 *
 * @method string    Throwable::__toString( void )
 * @method int       Throwable::getCode( void )
 * @method string    Throwable::getFile( void )
 * @method int       Throwable::getLine( void )
 * @method string    Throwable::getMessage( void )
 * @method Throwable Throwable::getPrevious( void )
 * @method array     Throwable::getTrace( void )
 * @method string    Throwable::getTraceAsString( void )
 */
interface Exceptable extends \Throwable {

  /**
   * exception severity levels.
   *
   * @type int ERROR    error
   * @type int WARNING  warning
   * @type int NOTICE   notice
   */
  const ERROR = E_ERROR;
  const WARNING = E_WARNING;
  const NOTICE = E_NOTICE;

  /**
   * default info for unknown/generic exception cases.
   *
   * @type int   DEFAULT_CODE
   * @type int   DEFAULT_MESSAGE
   * @type int   DEFAULT_SEVERITY
   */
  const DEFAULT_CODE = 0;
  const DEFAULT_MESSAGE = '';
  const DEFAULT_SEVERITY = self::ERROR;

  /**
   * gets information about a code known to the implementing class.
   *
   * @param int $code             the exception code to look up
   * @throws ExceptableException  if the code is not known to the implementation
   * @return array                a map of info about the code,
   *                              including (at a minimum) its "code", "severity", and "message".
   */
  public static function get_info(int $code) : array;

  /**
   * checks whether the implementation has info about the given code.
   *
   * @param int $code  the code to check
   * @return bool      true if the code is known; false otherwise
   */
  public static function has_info(int $code) : bool;

  /**
   * @param string    $0          exception message
   *                              if omitted, a message must be set based on the exception code
   * @param int       $1          exception code
   *                              if omitted, a default code must be set
   * @param Throwable $2          previous exception
   * @param array     $3          additional exception context
   * @throws ExceptableException  if argument(s) are invalid
   */
  public function __construct(...$args);

  /**
   * adds contextual info to this exception.
   *
   * @param array $context  map of info to add
   * @return $this
   */
  public function addContext(array $context) : Exceptable;

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
  public function getRoot() : \Throwable;

  /**
   * gets exception severity.
   *
   * @return int  the exception severity
   */
  public function getSeverity() : int;

  /**
   * checks the exception severity.
   *
   * @return bool  true if exception severity is "Error"; false otherwise
   */
  public function isError() : bool;

  /**
   * checks the exception severity.
   *
   * @return bool  true if exception severity is "Warning"; false otherwise
   */
  public function isWarning() : bool;

  /**
   * checks the exception severity.
   *
   * @return bool  true if exception severity is "Notice"; false otherwise
   */
  public function isNotice() : bool;

  /**
   * adds contextual info to this exception.
   *
   * @param int $severity         one of Exceptable::ERROR|WARNING|NOTICE
   * @throws ExceptableException  if severity is invalid
   * @return $this
   */
  public function setSeverity(int $severity) : Exceptable;
}