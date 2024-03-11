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

use BackedEnum,
  Exception,
  ResourceBundle,
  Throwable;

use at\exceptable\ {
  Error,
  Exceptable,
  IsExceptable,
  Tests\TestCase
};

/**
 * Basic tests for Error implementations.
 *
 * @covers at\exceptable\IsError
 *
 * Extend this class to test your Error.
 *  - override error() to provide the Error to test
 *  - override *Provider() methods to provide appropriate input and expectations
 */
abstract class ErrorTestCase extends TestCase {

  /** @return array[] @see ErrorTestCase::testExceptableType() */
  abstract public static function exceptableTypeProvider() : array;

  /** @return array[] @see ErrorTestCase::testMessage() */
  abstract public static function messageProvider() : array;

  /** @return array[] @see ErrorTestCase::testNewExceptable() */
  abstract public static function newExceptableProvider() : array;

  /** @return string Fully qualified classname of Error under test. */
  abstract protected static function errorType() : string;

  public static function errorProvider() : array {
    return array_map(
      fn ($error) => [$error],
      static::errorType()::cases()
    );
  }

  /** @dataProvider errorProvider */
  public function testCode(Error $error) {
    if ($error instanceof BackedEnum) {
      $expected = $error->value;
    } else {
      foreach ($error::cases() as $code => $case) {
        if ($case === $error) {
          $expected = $code + 1;
          break;
        }
      }
    }

    $this->assertSame($error->code(), $expected, "Error returns wrong error code");
  }

  /** @dataProvider errorProvider */
  public function testErrorName(Error $error) : void {
    $expected = $error::class . ".{$error->name}";
    $this->assertSame($expected, $error->errorName(), "Error does not report expected error name '{$expected}'");
  }

  /** @dataProvider exceptableTypeProvider */
  public function testExceptableType(Error $error, string $expected) : void {
    $this->assertSame($expected, $error->exceptableType());
  }

  /**
   * @dataProvider messageProvider
   *
   * @param Error $error The Error instance to test
   * @param array $context Contextual information for the message
   * @param string $expected The expected message
   * @param bool $isContextRequired Does omitting context result in an invalid message?
   */
  public function testMessage(Error $error, array $context, string $expected, bool $isContextRequired) {
    $errorName = $error->errorName();

    $this->assertSame(
      "{$errorName}: {$expected}",
      $error->message($context),
      "Error does return expected message with context"
    );

    if ($isContextRequired) {
      $this->assertSame($errorName, $error->message(), "Error does not return expected message without context");
    }
  }

  /** @dataProvider newExceptableProvider */
  public function testNewExceptable(
    Error $error,
    ? array $context,
    ? Throwable $previous,
    Exceptable $expected
  ) {
    // we're testing both __invoke() and newExceptable() - both should behave identically
    foreach ([$error, $error->newExceptable(...)] as $method) {
      $line = __LINE__ + 1;
      $actual = $method($context, $previous);

      $this->assertExceptableIsExceptable($actual, get_class($expected));
      $this->assertExceptableOrigination($actual, __FILE__, $line);
      $this->assertExceptableHasCode($actual, $expected->getCode());
      $this->assertExceptableHasMessage($actual, $expected->getMessage());
      $this->assertExceptableIsError($actual, $error);
      $this->assertExceptableHasError($actual, $error);
      if ($previous instanceof Exceptable) {
        $this->assertExceptableHasError($previous->error());
      }
      $this->assertExceptableHasContext($actual, $expected->context());
      $this->assertExceptableHasPrevious($actual, $expected->getPrevious());
      $this->assertExceptableHasRoot($actual, $expected->getPrevious() ?? $actual);
    }
  }

  /** Asserts test subject is an instance of Exceptable and of the given FQCN. */
  protected function assertExceptableIsExceptable($actual, string $fqcn) : void {
    $this->assertInstanceOf(Exceptable::class, $actual, "Exceptable is not Exceptable");
    $this->assertInstanceOf($fqcn, $actual, "Exceptable is not an instance of {$fqcn}");
  }

  /** Asserts test subject has the expected origin file and line number. */
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

  /** Asserts test subject has the expected code. */
  protected function assertExceptableHasCode(Exceptable $actual, int $code) : void {
    $this->assertSame(
      $code,
      $actual->getCode(),
      "Exceptable does not report expected code '{$code}'"
    );
  }

  /** Asserts test subject has the expected (possibly formatted) message. */
  protected function assertExceptableHasMessage(Exceptable $actual, string $message) : void {
    $this->assertSame(
      $message,
      $actual->getMessage(),
      "Exceptable does not report expected message '{$message}'"
    );
  }

  /** Asserts test subject has the expected contextual information. */
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

  /** Asserts test subject has the expected previous Exception. */
  protected function assertExceptableHasPrevious(Exceptable $actual, ?Throwable $previous) : void {
    $message = isset($previous) ?
      "getPrevious() does not report expected exception (" . get_class($previous) . ")" :
      "getPrevious() reports a previous exception but none was expected";
    $this->assertSame($previous, $actual->getPrevious(), $message);
  }

  /** Asserts test subject has the expected root Exception. */
  protected function assertExceptableHasRoot(Exceptable $actual, Throwable $root) : void {
    $fqcn = get_class($root);
    $this->assertSame(
      $root,
      $actual->root(),
      "getPrevious() does not report expected root exception ({$fqcn})"
    );
  }

  /** Asserts test subject has the given Error case. */
  protected function assertExceptableHasError(Exceptable $actual, Error $error) : void {
    $this->assertTrue(
      $actual->has($error),
      "Exceptable->has() does not have expected Error {$error->errorName()}"
    );
  }

  /** Asserts test subject matches the expected Error case. */
  protected function assertExceptableIsError(Exceptable $actual, Error $error) : void {
    $this->assertSame(
      $actual->error(),
      $error,
      "Exceptable does not match expected Error {$error->errorName()}"
    );
    $this->assertTrue(
      $actual->is($error),
      "Exceptable->is() does not match expected Error {$error->errorName()}"
    );
  }
}
