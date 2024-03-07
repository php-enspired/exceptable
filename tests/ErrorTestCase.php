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

use Exception,
  ResourceBundle,
  Throwable;

use at\exceptable\ {
  Error,
  Exceptable,
  IsExceptable,
  Tests\TestCase
};

/**
 * Basic tests for the default Error implementations.
 *
 * @covers at\exceptable\IsError
 *
 * Base class to test implementations of Error.
 *  - override error() to provide the Error to test
 *  - override *Provider() methods to provide appropriate input and expectations
 */
abstract class ErrorTestCase extends TestCase {

  abstract public static function codeProvider() : array;
  abstract public static function messageProvider() : array;
  abstract public static function newExceptableProvider() : array;

  /** @dataProvider codeProvider */
  public function testCode(Error $error, int $expected) {
    $this->assertSame($error->code(), $expected, "Error returns wrong error code");
  }

  /** @dataProvider messageProvider */
  public function testMessage(Error $error, array $context, string $expected) {
    $this->assertSame($expected, $error->message($context), "Error returns wrong error message");
  }

  /** @dataProvider newExceptableProvider */
  public function testNewExceptable(
    Error $error,
    ? array $context,
    ? Throwable $previous,
    Exceptable $expected
  ) {
    $line = __LINE__ + 1;
    $actual = $error($context, $previous);

    $this->assertExceptableIsExceptable($actual, get_class($expected));
    $this->assertExceptableOrigination($actual, __FILE__, $line);
    $this->assertExceptableHasCode($actual, $expected->getCode());
    $this->assertExceptableHasMessage($actual, $expected->getMessage());
    $this->assertExceptableHasError($actual, $error);
    $this->assertExceptableHasContext($actual, $expected->context());
    $this->assertExceptableHasPrevious($actual, $expected->getPrevious());
    $this->assertExceptableHasRoot($actual, $expected->getPrevious() ?? $actual);
  }

  /**
   * Asserts test subject is an instance of Exceptable and of the given FQCN.
   *
   * @param mixed $actual Test subject
   * @param string $fqcn Fully-qualified classname of the intended Exceptable
   */
  protected function assertExceptableIsExceptable($actual, string $fqcn) : void {
    $this->assertInstanceOf(Exceptable::class, $actual, "Exceptable is not Exceptable");
    $this->assertInstanceOf($fqcn, $actual, "Exceptable is not an instance of {$fqcn}");
  }

  /**
   * Asserts test subject has the expected origin file and line number.
   *
   * @param mixed $actual Test subject
   * @param string $file Expected filename
   * @param int $line Expected line number
   */
  protected function assertExceptableOrigination(Exceptable $actual, string $file, int $line) : void {
    $this->assertSame(
      $file,
      $actual->getFile(),
      "Exceptable does not report expected filename '{$file}'"
    );
    $this->assertSame(
      $line,
      $actual->getLine(),
      "Exceptable does not report expected line number '{$line}'"
    );
  }

  /**
   * Asserts test subject has the expected error case.
   *
   * @param mixed $actual Test subject
   * @param Error $rttot Expected error case
   */
  protected function assertExceptableHasError(Exceptable $actual, Error $error) : void {
    $this->assertSame(
      $error,
      $actual->error(),
      "Exceptable does not report expected error case '{$error->name}'"
    );
  }

  /**
   * Asserts test subject has the expected code.
   *
   * @param mixed $actual Test subject
   * @param int $code Expected exceptable code
   */
  protected function assertExceptableHasCode(Exceptable $actual, int $code) : void {
    $this->assertSame(
      $code,
      $actual->getCode(),
      "Exceptable does not report expected code '{$code}'"
    );
  }

  /**
   * Asserts test subject has the expected (possibly formatted) message.
   *
   * @param mixed $actual Test subject
   * @param string #message Expected exceptable message
   */
  protected function assertExceptableHasMessage(Exceptable $actual, string $message) : void {
    $this->assertSame(
      $message,
      $actual->getMessage(),
      "Exceptable does not report expected message '{$message}'"
    );
  }

  /**
   * Asserts test subject has the expected contextual information.
   *
   * @param mixed $actual Test subject
   * @param ?array $context Expected contextual information
   */
  protected function assertExceptableHasContext(Exceptable $actual, ? array $context) : void {
    $actual = $actual->context();

    $this->assertArrayHasKey("__rootMessage__", $actual, "context()[__rootMessage_] is missing");
    $this->assertIsString($actual["__rootMessage__"]);

    if (isset($context)) {
      foreach ($context as $key => $value) {
        $this->assertArrayHasKey($key, $actual, "context()[{$key}] is missing");

        $this->assertSame(
          $value,
          $actual[$key],
          "context()[{$key}] does not hold expected value ({$this->asString($value)})"
        );
      }
    }
  }

  /**
   * Asserts test subject has the expected previous Exception.
   *
   * @param mixed      $actual   Test subject
   * @param ?Throwable $previous Expected previous exception
   */
  protected function assertExceptableHasPrevious(Exceptable $actual, ?Throwable $previous) : void {
    $message = isset($previous) ?
      "getPrevious() does not report expected exception (" . get_class($previous) . ")" :
      "getPrevious() reports a previous exception but none was expected";
    $this->assertSame($previous, $actual->getPrevious(), $message);
  }

  /**
   * Asserts test subject has the expected root Exception.
   *
   * @param mixed      $actual Test subject
   * @param ?Throwable $root   Expected root (most-previous) exception
   */
  protected function assertExceptableHasRoot(Exceptable $actual, Throwable $root) : void {
    $fqcn = get_class($root);
    $this->assertSame(
      $root,
      $actual->root(),
      "getPrevious() does not report expected root exception ({$fqcn})"
    );
  }
}
