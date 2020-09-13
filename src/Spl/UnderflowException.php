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

namespace at\exceptable\Spl;

use UnderflowException as SplUnderflowException;

use at\exceptable\ {
  Exceptable,
  IsExceptable
};

/**
 * Exceptable implementation of Spl's UnderflowException.
 * @see https://php.net/UnderflowException
 */
class UnderflowException extends SplUnderflowException implements Exceptable {
  use IsExceptable;

  /** @var int Underflow. */
  public const UNDERFLOW = 0;

  /** @see IsExceptable::getInfo() */
  public const INFO = [
    self::UNDERFLOW => [
      "message" => "Underflow",
      "formatKey" => "exceptable.spl.underflow",
      "format" => "Underflow: {__rootMessage__}"
    ]
  ];
}
