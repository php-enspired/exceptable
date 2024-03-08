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

  public static function codeProvider() : array {
    return [
      [ExceptableError::UnknownError, 0],
      [ExceptableError::UnacceptableError, 1],
      [ExceptableError::UncaughtException, 2],
      [ExceptableError::HandlerFailed, 3]
    ];
  }

  public static function messageProvider() : array {
    return [
      [
        ExceptableError::UnknownError,
        [],
        "at\\exceptable\\ExceptableError.UnknownError"
      ],
      [
        ExceptableError::UnknownError,
        ["__rootMessage__" => "hello, world"],
        "at\\exceptable\\ExceptableError.UnknownError: hello, world"
      ],
      [
        ExceptableError::UnacceptableError,
        ["type" => "Foo"],
        "at\\exceptable\\ExceptableError.UnacceptableError:" .
          " Invalid Error type 'Foo' (expected enum implementing at\\exceptable\\Error)"
      ],
      [
        ExceptableError::UncaughtException,
        ["__rootType__" => "FooException", "__rootMessage__" => "hello, world"],
        "at\\exceptable\\ExceptableError.UncaughtException: Uncaught Exception (FooException): hello, world"
      ],
      [
        ExceptableError::HandlerFailed,
        ["type" => "BadHandler", "__rootMessage__" => "hello, world"],
        "at\\exceptable\\ExceptableError.HandlerFailed: ExceptionHandler (BadHandler) failed: hello, world"
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
}
