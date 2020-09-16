<?php
/**
 * @package    at.exceptable
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2020
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

namespace AT\Exceptable\Tests;

use Exception,
  Throwable;

use AT\Exceptable\ {
  Exceptable,
  ExceptableException,
  IsExceptable,
  Tests\TestCase
};

/**
 * Basic tests for the default Exceptable implementation (the IsExceptable trait).
 *
 * @covers AT\Exceptable\Exceptable
 * @covers AT\Exceptable\IsExceptable
 *
 * This test case can be extended to test other concrete implementations:
 *  - override newIsExceptable() method to provide an instance of the appropriate class
 *  - override *Provider methods as needed to provide appropriate input and expectations:
 *    - infoProvider()
 *    - badInfoProvider()
 */
class IsExceptableTest extends TestCase {

  /**
   * @dataProvider newExceptableProvider
   *
   * @param string     $fqcn     Fully qualified classname of Exceptable to test
   * @param int        $code     Exceptable code to test
   * @param ?array     $context  Contextual information to provide
   * @param ?Throwable $previous Previous exception to provide
   * @param string     $message  Expected exceprable message
   */
  public function testNewExceptable(
    string $fqcn,
    int $code,
    ?array $context,
    ?Throwable $previous,
    string $message
  ) : void {
    if (isset($previous)) {
      $line = __LINE__ + 1;
      $actual = new $fqcn($code, $context, $previous);
    } elseif (isset($context)) {
      $line = __LINE__ + 1;
      $actual = new $fqcn($code, $context);
    } else {
      $line = __LINE__ + 1;
      $actual = new $fqcn($code);
    }

    $this->assertIsExceptable($actual, $fqcn);
    $this->assertExceptionOrigination($actual, __FILE__, $line);
    $this->assertHasCode($actual, $code);
    $this->assertHasMessage($actual, $message);
    $this->assertHasContext($actual, $context);
    $this->assertHasPrevious($actual, $previous);
    $this->assertHasRoot($actual, $previous ?? $actual);
  }

  /**
   * @return array[] Testcases - @see ::testNewExceptable()
   */
  public function newExceptableProvider() : array {
    return [
      "UNKNOWN_FOO with code only" => [
        __TestExceptable::class,
        __TestExceptable::UNKNOWN_FOO,
        null,
        null,
        "unknown foo"
      ],
      "UNKNOWN_FOO with context" => [
        __TestExceptable::class,
        __TestExceptable::UNKNOWN_FOO,
        ["foo" => "foobedobedoo"],
        null,
        "i don't know who, you think is foo, but it's not foobedobedoo"
      ],
      "UNKNOWN_FOO with context and previous exception" => [
        __TestExceptable::class,
        __TestExceptable::UNKNOWN_FOO,
        ["foo" => "foobedobedoo"],
        new Exception("it's not just you"),
        "i don't know who, you think is foo, but it's not foobedobedoo"
      ],
      "UNKNOWN_FOO with previous exception" => [
        __TestExceptable::class,
        __TestExceptable::UNKNOWN_FOO,
        null,
        new Exception("it's not just you"),
        "unknown foo"
      ],

      "TOO_MUCH_FOO with code only" => [
        __TestExceptable::class,
        __TestExceptable::TOO_MUCH_FOO,
        null,
        null,
        "too much foo"
      ],
      "TOO_MUCH_FOO with context" => [
        __TestExceptable::class,
        __TestExceptable::TOO_MUCH_FOO,
        ["count" => 42],
        null,
        "too much foo is bad for you (got 42 foo)"
      ],
      "TOO_MUCH_FOO with context and previous exception" => [
        __TestExceptable::class,
        __TestExceptable::TOO_MUCH_FOO,
        ["count" => 42],
        new Exception("it's not just you"),
        "too much foo is bad for you (got 42 foo)"
      ],
      "TOO_MUCH_FOO with previous exception" => [
        __TestExceptable::class,
        __TestExceptable::TOO_MUCH_FOO,
        null,
        new Exception("it's not just you"),
        "too much foo"
      ]
    ];
  }

  /**
   * Asserts test subject is an instance of Exceptable and of the given FQCN.
   *
   * @param mixed  $actual Test subject
   * @param string $fqcn   Fully-qualified classname of the intended Exceptable
   */
  protected function assertIsExceptable($actual, string $fqcn) : void {
    $this->assertInstanceOf(Exceptable::class, $actual, "Exceptable is not Exceptable");
    $this->assertInstanceOf($fqcn, $actual, "Exceptable is not an instance of {$fqcn}");
  }

  /**
   * Asserts test subject has the expected origin file and line number.
   *
   * @param mixed  $actual Test subject
   * @param string $file   Expected filename
   * @param int    $line   Expected line number
   */
  protected function assertExceptionOrigination(Exceptable $actual, string $file, int $line) : void {
    $this->assertSame(
      $file,
      $actual->getFile(),
      "Exceptable does not report expected filename ('{$file}')"
    );
    $this->assertSame(
      $line,
      $actual->getLine(),
      "Exceptable does not report expected line number ({$line})"
    );
  }

  /**
   * Asserts test subject has the expected code.
   *
   * @param mixed $actual Test subject
   * @param int   $code   Expected exceptable code
   */
  protected function assertHasCode(Exceptable $actual, int $code) : void {
    $this->assertTrue($actual::hasInfo($code), "Exceptable does not understand code {$code}");
    $this->assertSame(
      $code,
      $actual->getCode(),
      "Exceptable does not report expected code ({$code})"
    );
  }

  /**
   * Asserts test subject has the expected (possibly formatted) message.
   *
   * @param mixed  $actual  Test subject
   * @param string #message Expected exceptable message
   */
  protected function assertHasMessage(Exceptable $actual, string $message) : void {
    $this->assertSame(
      $message,
      $actual->getMessage(),
      "Exceptable does not report expected message ('{$message}')"
    );
  }

  /**
   * Asserts test subject has the expected contextual information.
   *
   * @param mixed  $actual  Test subject
   * @param ?array $context Expected contextual information
   */
  protected function assertHasContext(Exceptable $actual, ?array $context) : void {
    $actual = $actual->getContext();

    $this->assertArrayHasKey(
      "__rootMessage__",
      $actual,
      "getContext()[___rootMessage_] is missing"
    );
    $this->assertIsString($actual["__rootMessage__"]);

    if (isset($context)) {
      foreach ($context as $key => $value) {
        $this->assertArrayHasKey($key, $actual, "getContext()[{$key}] is missing");

        $this->assertSame(
          $value,
          $actual[$key],
          "getContext()[{$key}] does not hold expected value ({$this->asString($value)})"
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
  protected function assertHasPrevious(Exceptable $actual, ?Throwable $previous) : void {
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
  protected function assertHasRoot(Exceptable $actual, Throwable $root) : void {
    $fqcn = get_class($root);
    $this->assertSame(
      $root,
      $actual->getRoot(),
      "getPrevious() does not report expected root exception ({$fqcn})"
    );
  }

  /**
   * Returns a string representation of the given value.
   *
   * @param mixed $value Value to encode
   * @return string      String representation of the value
   */
  protected function asString($value) : string {
    if (is_scalar($value) || is_callable([$value, "__toString"])) {
      return (string) $value;
    }

    try {
      $json = json_encode(
        $value,
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
      );
      return is_object($value) ?
        get_class($value) . " " . $json :
        $json;
    } catch (JsonException $e) {
      return is_object($value) ? get_class($value) : gettype($value);
    }
  }






  /**
   * @covers IsExceptable::getContext()
   * @dataProvider
   */

  /**
   * @covers IsExceptable::create()
   * @dataProvider createProvider
   *
   * @param string $fqcn Target Exceptable FQCN
   * @param int    $code Target Exceptable code
   */
  public function testCreate(string $fqcn, int $code) {
    $file = __FILE__;
    $line = __LINE__ + 1;
    $actual = $fqcn::create($code);

    $this->assertInstanceOf($fqcn, $actual);
    $this->assertSame($code, $actual->getCode(), "create() did not assign code {$code}");
    $this->assertSame(
      $file,
      $actual->getFile(),
      "create()->file does not reflect the file where the method was called"
    );
    $this->assertSame(
      $line,
      $actual->getLine(),
      "create()->line does not reflect the line where the method was called"
    );
  }

  /**
   * @return array[] Testcases: @see testCreate()
   */
  public function createProvider() : array {
    return [
      [__TestExceptable::class, __TestExceptable::UNKNOWN_FOO],
      [__TestExceptable::class, __TestExceptable::TOO_MUCH_FOO]
    ];
  }

  /**
   * @covers IsExceptable::getInfo()
   * @dataProvider infoProvider
   *
   * @param int   $code     Known exceptable code to get info for
   * @param array $expected Information expected to be returned:
   *  - string $message Plain exception message
   *  - string $format  Formatting string for message with context
   *  - mixed  $...     Additional key:value pairs
   */
  public function testGetInfo(int $code, array $expected) : void {
    // minimum requirements (these _should_ always be provided)
    $expected += ["code" => $code, "message" => ""];

    $actual = $this->newIsExceptable($code)::getInfo($code);

    $this->assertIsArray($actual);
    foreach ($expected as $key => $expectedValue) {
      $this->assertArrayHasKey($key, $actual, "getInfo()[{$key}] is not defined");
      $this->assertSame(
        $expectedValue,
        $actual[$key],
        "getInfo()[{$key}] does not match expected value {$expectedValue}"
      );
    }
  }

  /**
   * @return array[] Testcases: @see testGetInfo()
   */
  public function infoProvider() : array {
    return [
      [__TestExceptable::UNKNOWN_FOO, __TestExceptable::INFO[__TestExceptable::UNKNOWN_FOO]],
      [__TestExceptable::TOO_MUCH_FOO, __TestExceptable::INFO[__TestExceptable::TOO_MUCH_FOO]]
    ];
  }

  /**
   * @covers IsExceptable::getInfo()
   * @dataProvider badInfoProvider
   *
   * @param int $code Unknown exceptable code to get info for
   */
  public function testGetBadInfo(int $code) : void {
    $this->expectThrowable(
      new ExceptableException(ExceptableException::NO_SUCH_CODE, ["code" => $code]),
      self::EXPECT_THROWABLE_CODE | self::EXPECT_THROWABLE_MESSAGE
    );

    $this->newIsExceptable($code);
  }

  /**
   * @return array[] Testcases:
   *  - int $0 Unknown Exceptable code
   */
  public function badInfoProvider() : array {
    return [[7]];
  }

  /**
   * Creates a new Exceptable instance for basic tests.
   *
   * @param mixed ...$args
   * @return Exceptable
   */
  protected function newIsExceptable(...$args) : Exceptable {
    return new __TestExceptable(...$args);
  }
}

/** Default test class. */
class __TestExceptable extends Exception implements Exceptable {
  use IsExceptable;

  const UNKNOWN_FOO = 1;
  const TOO_MUCH_FOO = 2;

  const INFO = [
    self::UNKNOWN_FOO => [
      "message" => "unknown foo",
      "format" => "i don't know who, you think is foo, but it's not {foo}"
    ],
    self::TOO_MUCH_FOO => [
      "message" => "too much foo",
      "format" => "too much foo is bad for you (got {count} foo)"
    ]
  ];
}
