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

use Throwable,
  UnitEnum;

use at\peekaboo\HasMessages;

/** Defines error cases for use by an Exceptable, or as a standalone error value. */
interface Error extends HasMessages, UnitEnum {

  /** @see Error::newExceptable() */
  public function __invoke(array $context = [], Throwable $previous = null) : Exceptable;

  /**
   * Gets the error code for this case.
   *
   * @return int An error code
   */
  public function code() : int;

  /**
   * Gets the fully qualified name of the proper Exceptable class to throw this Error as.
   *
   * @return string Exceptable FQCN
   */
  public function exceptableType() : string;

  /**
   * Gets the error message for this case, using the given context.
   *
   * @param array $context Exception context
   * @return string An error message
   */
  public function message(array $context) : string;

  /**
   * Creates an Exceptable from this Error case.
   *
   * @param array $context Additional exception context
   * @param ?Throwable $previous Previous exception, if any
   * @return Exceptable A new Exceptable using this Error case
   */
  public function newExceptable(array $context = [], Throwable $previous = null) : Exceptable;
}
