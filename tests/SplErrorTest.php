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

  public static function codeProvider() : array {
    return [
      [SplError::BadFunctionCall, 1],
      [SplError::BadMethodCall, 2],
      [SplError::Domain, 3],
      [SplError::InvalidArgument, 4],
      [SplError::Length, 5],
      [SplError::Logic, 6],
      [SplError::OutOfBounds, 7],
      [SplError::OutOfRange, 8],
      [SplError::Overflow, 9],
      [SplError::Range, 10],
      [SplError::Runtime, 11],
      [SplError::Underflow, 12],
      [SplError::UnexpectedValue, 13]
    ];
  }

  public static function messageProvider() : array {
    $context = ["__rootMessage__" => "hello, world"];
    return [
      [SplError::BadFunctionCall, $context, "at\\exceptable\\Spl\\SplError.BadFunctionCall: hello, world"],
      [SplError::BadMethodCall, $context, "at\\exceptable\\Spl\\SplError.BadMethodCall: hello, world"],
      [SplError::Domain, $context, "at\\exceptable\\Spl\\SplError.Domain: hello, world"],
      [SplError::InvalidArgument, $context, "at\\exceptable\\Spl\\SplError.InvalidArgument: hello, world"],
      [SplError::Length, $context, "at\\exceptable\\Spl\\SplError.Length: hello, world"],
      [SplError::Logic, $context, "at\\exceptable\\Spl\\SplError.Logic: hello, world"],
      [SplError::OutOfBounds, $context, "at\\exceptable\\Spl\\SplError.OutOfBounds: hello, world"],
      [SplError::OutOfRange, $context, "at\\exceptable\\Spl\\SplError.OutOfRange: hello, world"],
      [SplError::Overflow, $context, "at\\exceptable\\Spl\\SplError.Overflow: hello, world"],
      [SplError::Range, $context, "at\\exceptable\\Spl\\SplError.Range: hello, world"],
      [SplError::Runtime, $context, "at\\exceptable\\Spl\\SplError.Runtime: hello, world"],
      [SplError::Underflow, $context, "at\\exceptable\\Spl\\SplError.Underflow: hello, world"],
      [SplError::UnexpectedValue, $context, "at\\exceptable\\Spl\\SplError.UnexpectedValue: hello, world"]
    ];
  }

  public static function newExceptableProvider() : array {
    try {
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
  } catch (\Throwable $t) { echo $t; var_dump($t->context()); exit; }
  }
}
