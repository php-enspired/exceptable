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

use OutOfRangeException as SplOutOfRangeException;

use at\exceptable\ {
  Exceptable,
  IsExceptable
};

/**
 * Exceptable implementation of Spl's OutOfRangeException.
 * @see https://php.net/OutOfRangeException
 */
class OutOfRangeException extends SplOutOfRangeException implements Exceptable {
  use IsExceptable;

  /** @var int Out of range. */
  public const OUT_OF_RANGE = 0;

  /** @see IsExceptable::getInfo() */
  public const INFO = [
    self::OUT_OF_RANGE => [
      "message" => "Out of range",
      "formatKey" => "exceptable.spl.outofrange",
      "format" => "Out of range: {__rootMessage__}"
    ]
  ];
}
