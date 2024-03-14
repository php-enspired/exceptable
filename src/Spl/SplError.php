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

namespace at\exceptable\Spl;

use at\exceptable\ {
  Error,
  IsError,
  Spl\BadFunctionCallException,
  Spl\BadMethodCallException,
  Spl\DomainException,
  Spl\InvalidArgumentException,
  Spl\LengthException,
  Spl\LogicException,
  Spl\OutOfBoundsException,
  Spl\OutOfRangeException,
  Spl\OverflowException,
  Spl\RangeException,
  Spl\RuntimeException,
  Spl\UnderflowException,
  Spl\UnexpectedValueException
};

/**
 * Error cases corresponding to the Spl Exception types.
 *
 * @phan-suppress PhanInvalidConstantExpression
 */
enum SplError : int implements Error {
  use IsError;

  case BadFunctionCall = 1;
  case BadMethodCall = 2;
  case Domain = 3;
  case InvalidArgument = 4;
  case Length = 5;
  case Logic = 6;
  case OutOfBounds = 7;
  case OutOfRange = 8;
  case Overflow = 9;
  case Range = 10;
  case Runtime = 11;
  case Underflow = 12;
  case UnexpectedValue = 13;

  /** @see Error::MESSAGES */
  public const MESSAGES = [
    self::class => [
      self::BadFunctionCall->name => "{__rootMessage__}",
      self::BadMethodCall->name => "{__rootMessage__}",
      self::Domain->name => "{__rootMessage__}",
      self::InvalidArgument->name => "{__rootMessage__}",
      self::Length->name => "{__rootMessage__}",
      self::Logic->name => "{__rootMessage__}",
      self::OutOfBounds->name => "{__rootMessage__}",
      self::OutOfRange->name => "{__rootMessage__}",
      self::Overflow->name => "{__rootMessage__}",
      self::Range->name => "{__rootMessage__}",
      self::Runtime->name => "{__rootMessage__}",
      self::Underflow->name => "{__rootMessage__}",
      self::UnexpectedValue->name => "{__rootMessage__}"
    ]
  ];

  /** @see Error::exceptable() */
  public function exceptableType() : string {
    assert($this instanceof Error);
    return match ($this) {
      self::BadFunctionCall => BadFunctionCallException::class,
      self::BadMethodCall => BadMethodCallException::class,
      self::Domain => DomainException::class,
      self::InvalidArgument => InvalidArgumentException::class,
      self::Length => LengthException::class,
      self::Logic => LogicException::class,
      self::OutOfBounds => OutOfBoundsException::class,
      self::OutOfRange => OutOfRangeException::class,
      self::Overflow => OverflowException::class,
      self::Range => RangeException::class,
      self::Runtime => RuntimeException::class,
      self::Underflow => UnderflowException::class,
      self::UnexpectedValue => UnexpectedValueException::class,
      default => RuntimeException::class
    };
  }
}
