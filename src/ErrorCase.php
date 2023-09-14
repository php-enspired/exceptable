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

use BackedEnum,
  Throwable;

use at\peekaboo\HasMessages;

/**
 * Defines error cases for an Exceptable.
 *
 * Implementing enums MUST be integer-backed (ints are error codes).
 */
interface ErrorCase extends BackedEnum, HasMessages {

  /**
   * Builds and throws an exception based on this ErrorCase.
   *
   * @param array $context Additional exception context
   * @param ?Throwable $previous Previous exception
   * @throws Exceptable
   */
  public function throw(array $context = [], Throwable $previous = null) : void;
}
