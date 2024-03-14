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
  ExceptableError,
  Spl\LogicException,
  Spl\RuntimeException,
  Tests\ErrorTestCase
};

class ExceptableErrorTest extends ErrorTestCase {

  public static function exceptableTypeProvider() : array {
    return [
      [ExceptableError::UnknownError, RuntimeException::class],
      [ExceptableError::UnacceptableError, LogicException::class],
      [ExceptableError::UncaughtException, RuntimeException::class],
      [ExceptableError::HandlerFailed, LogicException::class]
    ];
  }

  public static function messageProvider() : array {
    return [
      [ExceptableError::UnknownError, ["__rootMessage__" => "hello, world"], "hello, world", true],
      [
        ExceptableError::UnacceptableError,
        ["type" => "Foo"],
        "Invalid Error type 'Foo' (expected enum implementing at\\exceptable\\Error)",
        true
      ],
      [
        ExceptableError::UncaughtException,
        ["__rootType__" => "FooException", "__rootMessage__" => "hello, world"],
        "Uncaught Exception (FooException): hello, world",
        true
      ],
      [
        ExceptableError::HandlerFailed,
        ["type" => "BadHandler", "__rootMessage__" => "hello, world"],
        "ExceptionHandler (BadHandler) failed: hello, world",
        true
      ]
    ];
  }

  public static function newExceptableProvider() : array {
    $t = new Exception("hello, world");
    return [
      [
        ExceptableError::UnknownError,
        [],
        $t,
        new RuntimeException(ExceptableError::UnknownError, [], $t)
      ],
      [
        ExceptableError::UnacceptableError,
        ["type" => "Foo"],
        null,
        new LogicException(ExceptableError::UnacceptableError, ["type" => "Foo"])
      ],
      [
        ExceptableError::UncaughtException,
        [],
        $t,
        new RuntimeException(ExceptableError::UncaughtException, [], $t)
      ],
      [
        ExceptableError::HandlerFailed,
        ["type" => "BadHandler"],
        $t,
        new LogicException(ExceptableError::HandlerFailed, ["type" => "BadHandler"], $t)
      ]
    ];
  }

  protected static function errorType() : string {
    return ExceptableError::class;
  }
}
