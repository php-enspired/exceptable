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

use ResourceBundle,
  Throwable;

use AT\Exceptable\ExceptableException;

/**
 * Augmented interface for exceptional exceptions.
 * Exceptables uniquely identify specific error cases by exception FQCN + code.
 *
 * Caution:
 *  - the implementing class must extend from Exception (or a subclass) and implement Exceptable.
 *  - implementations cannot extend from PDOException,
 *    because it breaks the Throwable interface (its getCode() returns a string).
 */
interface Exceptable extends Throwable {

  /**
   * Gets information about a code known to the implementing class.
   *
   * @param int $code            The exception code to look up
   * @throws ExceptableException If the code is not known to the implementation
   * @return array               Information about the error case, including:
   *  - string $class   Exception class
   *  - int    $code    Error code
   *  - string $message Error description
   *  - string $format  ICU formatting template for contextualized error message
   *  - mixed  $...     Additional, implementation-specific information
   */
  public static function getInfo(int $code) : array;

  /**
   * Checks whether the implementation has info about the given code.
   *
   * @param int $code The code to check
   * @return bool     True if the code is known; false otherwise
   */
  public static function hasInfo(int $code) : bool;

  /**
   * Sets up localized message support.
   *
   * @param string         $locale   Preferred locale
   * @param ResourceBundle $messages Message format patterns
   */
  public static function localize(string $locale, ResourceBundle $messages) : void;

  /**
   * @param int            $code     Exception code
   * @param array          $context  Additional exception context
   * @param Throwable|null $previous Previous exception
   * @throws ExceptableException     If code is invalid
   */
  public function __construct(int $code = 0, array $context = [], Throwable $previous = null);

  /**
   * Gets contextual information about this exception.
   *
   * @return array Exception context, including:
   *  - string $__message__     The top-level exception message
   *  - string $__rootMessage__ The root exception message (may be the same as $__message__)
   *  - mixed  $__...__         Additional, implementation-specific information
   *  - mixed  $...             Additional context provided at time of error
   */
  public function getContext() : array;

  /**
   * Traverses the chain of previous exception(s) and gets the root exception.
   *
   * @return Throwable The root exception
   */
  public function getRoot() : Throwable;
}
