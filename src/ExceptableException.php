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

namespace at\exceptable;

use Exception;

use at\exceptable\ {
  Exceptable,
  IsExceptable
};

/**
 * exceptableexceptionsexceptableexceptionsexceptableexceptions
 */
class ExceptableError extends Exception implements Exceptable {
  use IsExceptable;

  /**
   * @var int NO_SUCH_CODE       Invalid exception code
   * @var int UNCAUGHT_EXCEPTION Uncaught/unhandled exception during runtime
   * @var int INVALID_HANDLER    Invalid handler (e.g., wrong signature, or throws)
   */
  public const NO_SUCH_CODE = 1;
  public const UNCAUGHT_EXCEPTION = 2;
  public const INVALID_HANDLER = 3;

  /** @see Exceptable::INFO */
  public const INFO = [
    self::NO_SUCH_CODE => [
      "message" => "No such code",
      "format" => "No exception code '{code}' is known",
      "formatKey" => "exceptable.exceptableexception.no_such_code"
    ],
    self::UNCAUGHT_EXCEPTION => [
      "message" => "Uncaught exception",
      "format" => "No registered handler caught exception: {__rootMessage__}",
      "formatKey" => "exceptable.exceptableexception.uncaught_exception"
    ],
    self::INVALID_HANDLER => [
      "message" => "Invalid handler",
      "format" => "Invalid handler [{type}]: {__rootMessage__}",
      "formatKey" => "exceptable.exceptableexception.invalid_handler"
    ]
  ];
}
