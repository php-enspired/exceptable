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

use LogicException as SplLogicException;

use at\exceptable\ {
  Exceptable,
  IsExceptable
};

/**
 * Exceptable implementation of Spl's LogicException.
 * @see https://php.net/LogicException
 */
class LogicException extends SplLogicException implements Exceptable {
  use IsExceptable;

  /** @var int Program logic error. */
  public const PROGRAM_LOGIC_ERROR = 1;

  /** @see IsExceptable::getInfo() */
  public const INFO = [
    self::PROGRAM_LOGIC_ERROR => [
      "message" => "Program logic error",
      "formatKey" => "exceptable.spl.programlogicerror",
      "format" => "Program logic error: {__rootMessage__}"
    ]
  ];
}
