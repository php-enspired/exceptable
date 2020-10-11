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

namespace AT\Exceptable\Spl;

use BadFunctionCallException as SplBadFunctionCallException;

use AT\Exceptable\ {
  Exceptable,
  IsExceptable
};

/**
 * Exceptable implementation of Spl's BadFunctionCallException.
 * @see https://php.net/BadFunctionCallException
 */
class BadFunctionCallException extends SplBadFunctionCallException implements Exceptable {
  use IsExceptable;

  /** @var int Bad function call. */
  public const BAD_FUNCTION_CALL = 0;

  /** @see IsExceptable::getInfo() */
  public const INFO = [
    self::BAD_FUNCTION_CALL => [
      "message" => "Bad function call",
      "formatKey" => "exceptable.spl.badfunctioncall",
      "format" => "Bad function call: {__rootMessage__}"
    ]
  ];
}
