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
  IsError
};

/**
 * @phan-suppress PhanInvalidConstantExpression
 * false positive
 */
enum ExceptableError : int implements Error {
  use IsError;

  case UnacceptableError = 0;
  case UncaughtException = 1;
  case UnknownError = 2;

  /** @see MakesMessages::MESSAGES */
  protected const MESSAGES = [
    self::class => [
      self::UnacceptableError->name =>
        "Invalid Error type '{type}' (expected enum implementing " . Error::class . ")",
      self::UncaughtException->name => "Uncaught Exception ({__rootType__}): {__rootMessage__}",
      self::UnknownError->name => "{__rootMessage__}"
    ]
  ];
}
