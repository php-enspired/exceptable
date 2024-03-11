<?php
/**
 * @package    at.exceptable
 * @subpackage tests
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

namespace at\exceptable\Tests;

use Exception;

use at\exceptable\ {
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
  Spl\SplError,
  Spl\UnderflowException,
  Spl\UnexpectedValueException,
  Tests\ErrorTestCase
};

/**
 * Basic tests for the default Error implementations.
 *
 * @covers at\exceptable\IsError
 * @covers at\exceptable\Spl\SplError
 *
 * Base class to test implementations of Error.
 *  - override error() to provide the Error to test
 *  - override *Provider() methods to provide appropriate input and expectations
 */
class SplErrorTest extends ErrorTestCase {

  public static function exceptableTypeProvider() : array {
    return [
      [SplError::BadFunctionCall, BadFunctionCallException::class],
      [SplError::BadMethodCall, BadMethodCallException::class],
      [SplError::Domain, DomainException::class],
      [SplError::InvalidArgument, InvalidArgumentException::class],
      [SplError::Length, LengthException::class],
      [SplError::Logic, LogicException::class],
      [SplError::OutOfBounds, OutOfBoundsException::class],
      [SplError::OutOfRange, OutOfRangeException::class],
      [SplError::Overflow, OverflowException::class],
      [SplError::Range, RangeException::class],
      [SplError::Runtime, RuntimeException::class],
      [SplError::Underflow, UnderflowException::class],
      [SplError::UnexpectedValue, UnexpectedValueException::class]
    ];
  }

  public static function messageProvider() : array {
    $context = ["__rootMessage__" => "hello, world"];
    return [
      [SplError::BadFunctionCall, $context, "hello, world", true],
      [SplError::BadMethodCall, $context, "hello, world", true],
      [SplError::Domain, $context, "hello, world", true],
      [SplError::InvalidArgument, $context, "hello, world", true],
      [SplError::Length, $context, "hello, world", true],
      [SplError::Logic, $context, "hello, world", true],
      [SplError::OutOfBounds, $context, "hello, world", true],
      [SplError::OutOfRange, $context, "hello, world", true],
      [SplError::Overflow, $context, "hello, world", true],
      [SplError::Range, $context, "hello, world", true],
      [SplError::Runtime, $context, "hello, world", true],
      [SplError::Underflow, $context, "hello, world", true],
      [SplError::UnexpectedValue, $context, "hello, world", true]
    ];
  }

  public static function newExceptableProvider() : array {
    $context = ["this" => "is only a test"];
    $previous = new Exception("this is the root exception");
    return [
      [
        SplError::BadFunctionCall,
        $context,
        $previous,
        new BadFunctionCallException(SplError::BadFunctionCall, $context, $previous)
      ],
      [
        SplError::BadMethodCall,
        $context,
        $previous,
        new BadMethodCallException(SplError::BadMethodCall, $context, $previous)
      ],
      [
        SplError::Domain,
        $context,
        $previous,
        new DomainException(SplError::Domain, $context, $previous)
      ],
      [
        SplError::InvalidArgument,
        $context,
        $previous,
        new InvalidArgumentException(SplError::InvalidArgument, $context, $previous)
      ],
      [
        SplError::Length,
        $context,
        $previous,
        new LengthException(SplError::Length, $context, $previous)
      ],
      [
        SplError::Logic,
        $context,
        $previous,
        new LogicException(SplError::Logic, $context, $previous)
      ],
      [
        SplError::OutOfBounds,
        $context,
        $previous,
        new OutOfBoundsException(SplError::OutOfBounds, $context, $previous)
      ],
      [
        SplError::OutOfRange,
        $context,
        $previous,
        new OutOfRangeException(SplError::OutOfRange, $context, $previous)
      ],
      [
        SplError::Overflow,
        $context,
        $previous,
        new OverflowException(SplError::Overflow, $context, $previous)
      ],
      [
        SplError::Range,
        $context,
        $previous,
        new RangeException(SplError::Range, $context, $previous)
      ],
      [
        SplError::Runtime,
        $context,
        $previous,
        new RuntimeException(SplError::Runtime, $context, $previous)
      ],
      [
        SplError::Underflow,
        $context,
        $previous,
        new UnderflowException(SplError::Underflow, $context, $previous)
      ],
      [
        SplError::UnexpectedValue,
        $context,
        $previous,
        new UnexpectedValueException(SplError::UnexpectedValue, $context, $previous
        )
      ]
    ];
  }

  protected static function errorType() : string {
    return SplError::class;
  }
}
