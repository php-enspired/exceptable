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

use Throwable;

use at\exceptable\ {
  Error,
  Exceptable,
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
  protected const MESSAGES = [
    self::class => [
      self::BadFunctionCall->value => "{__rootMessage__}",
      self::BadMethodCall->value => "{__rootMessage__}",
      self::Domain->value => "{__rootMessage__}",
      self::InvalidArgument->value => "{__rootMessage__}",
      self::Length->value => "{__rootMessage__}",
      self::Logic->value => "{__rootMessage__}",
      self::OutOfBounds->value => "{__rootMessage__}",
      self::OutOfRange->value => "{__rootMessage__}",
      self::Overflow->value => "{__rootMessage__}",
      self::Range->value => "{__rootMessage__}",
      self::Runtime->value => "{__rootMessage__}",
      self::Underflow->value => "{__rootMessage__}",
      self::UnexpectedValue->value => "{__rootMessage__}"
    ]
  ];

  /** @see Error::exceptable() */
  public function exceptable(array $context = [], Throwable $previous = null) : Exceptable {
    assert($this instanceof Error);
    return match ($this) {
      self::BadFunctionCall => BadFunctionCallException::from($this, $context, $previous),
      self::BadMethodCall => BadMethodCallException::from($this, $context, $previous),
      self::Domain => DomainException::from($this, $context, $previous),
      self::InvalidArgument => InvalidArgumentException::from($this, $context, $previous),
      self::Length => LengthException::from($this, $context, $previous),
      self::Logic => LogicException::from($this, $context, $previous),
      self::OutOfBounds => OutOfBoundsException::from($this, $context, $previous),
      self::OutOfRange => OutOfRangeException::from($this, $context, $previous),
      self::Overflow => OverflowException::from($this, $context, $previous),
      self::Range => RangeException::from($this, $context, $previous),
      self::Runtime => RuntimeException::from($this, $context, $previous),
      self::Underflow => UnderflowException::from($this, $context, $previous),
      self::UnexpectedValue => UnexpectedValueException::from($this, $context, $previous),
      default => RuntimeException::from($this, $context, $previous)
    };

    // return match ($this) {
    //   self::BadFunctionCall => (fn () => new BadFunctionCallException($this, $context, $previous, 2))->call($e, $e),
    //   self::BadMethodCall => (fn () => new BadMethodCallException($this, $context, $previous, 2))->call($e, $e),
    //   self::Domain => (fn () => new DomainException($this, $context, $previous, 2))->call($e, $e),
    //   self::InvalidArgument => (fn () => new InvalidArgumentException($this, $context, $previous, 2))->call($e, $e),
    //   self::Length => (fn () => new LengthException($this, $context, $previous, 2))->call($e, $e),
    //   self::Logic => (fn () => new LogicException($this, $context, $previous, 2))->call($e, $e),
    //   self::OutOfBounds => (fn () => new OutOfBoundsException($this, $context, $previous, 2))->call($e, $e),
    //   self::OutOfRange => (fn () => new OutOfRangeException($this, $context, $previous, 2))->call($e, $e),
    //   self::Overflow => (fn () => new OverflowException($this, $context, $previous, 2))->call($e, $e),
    //   self::Range => (fn () => new RangeException($this, $context, $previous, 2))->call($e, $e),
    //   self::Runtime => (fn () => new RuntimeException($this, $context, $previous, 2))->call($e, $e),
    //   self::Underflow => (fn () => new UnderflowException($this, $context, $previous, 2))->call($e, $e),
    //   self::UnexpectedValue => (fn () => new UnexpectedValueException($this, $context, $previous, 2))->call($e, $e),
    //   default => RuntimeException::from($this, $context, $previous)
    // };
  }
}
