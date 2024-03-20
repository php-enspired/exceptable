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

use at\exceptable\ {
  Error,
  IsError,
  Spl\LogicException,
  Spl\RuntimeException
};

/**
 * @phan-suppress PhanInvalidConstantExpression
 * false positive
 */
enum ExceptableError : int implements Error {
  use IsError;

  case UnknownError = 0;
  case UnacceptableError = 1;
  case UncaughtException = 2;
  case HandlerFailed = 3;

  /** @see MakesMessages::MESSAGES */
  public const MESSAGES = [
    self::UnknownError->name => "{__rootMessage__}",
    self::UnacceptableError->name =>
      "Invalid Error type ''{type}'' (expected enum implementing " . Error::class . ")",
    self::UncaughtException->name => "Uncaught Exception ({__rootType__}): {__rootMessage__}",
    self::HandlerFailed->name => "ExceptionHandler ({type}) failed: {__rootMessage__}"
  ];

  /** @see Error::exceptable() */
  public function exceptableType() : string {
    assert($this instanceof Error);
    return match ($this) {
      self::UnacceptableError, self::HandlerFailed => LogicException::class,
      self::UncaughtException, self::UnknownError => RuntimeException::class,
      default => RuntimeException::class
    };
  }
}
