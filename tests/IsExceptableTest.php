<?php
/**
 * @package    at.exceptable
 * @subpackage tests
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2023
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
  Exceptable,
  ExceptableError,
  IsExceptable,
  Tests\TestCase
};

/**
 * Basic tests for the default Exceptable implementation (the IsExceptable trait).
 *
 * @covers at\exceptable\IsExceptable
 *
 * This test case can (should) be extended to test other concrete implementations:
 *  - override exceptableFQCN() method to provide the name of the exceptable to test
 *  - override *Provider methods to provide appropriate input and expectations
 */
class IsExceptableTest extends TestCase {

  /**
   * Path to resource bundle used for this test.
   * This is specific to the base test suite and MUST NOT be used by child tests.
   *
   * @var string
   */
  private const RESOURCE_BUNDLE = __DIR__ . "/../resources/language/";

  /**
   * @dataProvider newExceptableProvider
   *
   * @param int        $code     Exceptable code to test
   * @param ?array     $context  Contextual information to provide
   * @param ?Throwable $previous Previous exception to provide
   * @param string     $message  Expected exceprable message
   */
  public function testNewExceptable(
    int $code,
    ?array $context,
    ?Throwable $previous,
    string $message
  ) : void {
    $fqcn = $this->exceptableFQCN();

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
    $this->assertOrigination($actual, __FILE__, $line);
    $this->assertHasCode($actual, $code);
    $this->assertHasMessage($actual, $message);
    $this->assertHasContext($actual, $context);
    $this->assertHasPrevious($actual, $previous);
    $this->assertHasRoot($actual, $previous ?? $actual);
  }

  /**
   * @see ::testNewExceptable()
   * @dataProvider newExceptableProvider
   */
  public function testCreateExceptable(
    int $code,
    ?array $context,
    ?Throwable $previous,
    string $message
  ) : void {
    $fqcn = $this->exceptableFQCN();

    if (isset($previous)) {
      $line = __LINE__ + 1;
      $actual = $fqcn::create($code, $context, $previous);
    } elseif (isset($context)) {
      $line = __LINE__ + 1;
      $actual = $fqcn::create($code, $context);
    } else {
      $line = __LINE__ + 1;
      $actual = $fqcn::create($code);
    }

    $this->assertIsExceptable($actual, $fqcn);
    $this->assertOrigination($actual, __FILE__, $line);
    $this->assertHasCode($actual, $code);
    $this->assertHasMessage($actual, $message);
    $this->assertHasContext($actual, $context);
    $this->assertHasPrevious($actual, $previous);
    $this->assertHasRoot($actual, $previous ?? $actual);
  }

  /**
   * @see ::testNewExceptable()
   * @dataProvider newExceptableProvider
   */
  public function testThrowExceptable(
    int $code,
    ?array $context,
    ?Throwable $previous,
    string $message
  ) : void {
    try {
    $fqcn = $this->exceptableFQCN();

      $actual = null;
      if (isset($previous)) {
        $line = __LINE__ + 1;
        $fqcn::throw($code, $context, $previous);
      } elseif (isset($context)) {
        $line = __LINE__ + 1;
        $fqcn::throw($code, $context);
      } else {
        $line = __LINE__ + 1;
        $fqcn::throw($code);
      }
    } catch (Exceptable $e) {
      $actual = $e;
    }

    $this->assertNotNull($actual, "throw() did not throw an Exceptable");

    $this->assertIsExceptable($actual, $fqcn);
    $this->assertOrigination($actual, __FILE__, $line);
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
        TestExceptable::UNKNOWN_FOO,
        null,
        null,
        "unknown foo"
      ],
      "UNKNOWN_FOO with context" => [
        TestExceptable::UNKNOWN_FOO,
        ["foo" => "foobedobedoo"],
        null,
        "i don't know who, you think is foo, but it's not foobedobedoo"
      ],
      "UNKNOWN_FOO with context and previous exception" => [
        TestExceptable::UNKNOWN_FOO,
        ["foo" => "foobedobedoo"],
        new Exception("it's not just you"),
        "i don't know who, you think is foo, but it's not foobedobedoo"
      ],
      "UNKNOWN_FOO with previous exception" => [
        TestExceptable::UNKNOWN_FOO,
        null,
        new Exception("it's not just you"),
        "unknown foo"
      ],

      "TOO_MUCH_FOO with code only" => [
        TestExceptable::TOO_MUCH_FOO,
        null,
        null,
        "too much foo"
      ],
      "TOO_MUCH_FOO with context" => [
        TestExceptable::TOO_MUCH_FOO,
        ["count" => 42],
        null,
        "too much foo is bad for you (got 42 foo)"
      ],
      "TOO_MUCH_FOO with context and previous exception" => [
        TestExceptable::TOO_MUCH_FOO,
        ["count" => 42],
        new Exception("it's not just you"),
        "too much foo is bad for you (got 42 foo)"
      ],
      "TOO_MUCH_FOO with previous exception" => [
        TestExceptable::TOO_MUCH_FOO,
        null,
        new Exception("it's not just you"),
        "too much foo"
      ]
    ];
  }

  /**
   * @dataProvider infoProvider
   *
   * @param int   $code     Known exceptable code to get info for
   * @param array $expected Information expected to be returned
   */
  public function testGetInfo(int $code, array $expected) : void {
    $fqcn = $this->exceptableFQCN();

    $actual = $fqcn::getInfo($code);

    $this->assertIsArray($actual, "getInfo() did not return an array");

    $this->assertArrayHasKey("code", $actual, "getInfo()[code] is missing");
    $this->assertIsInt($actual["code"], "getInfo()[code] is not a integer");

    $this->assertArrayHasKey("message", $actual, "getInfo()[message] is missing");
    $this->assertIsString($actual["message"], "getInfo()[message] is not a string");

    $this->assertArrayHasKey("format", $actual, "getInfo()[format] is missing");
    if (isset($actual["format"])) {
      $this->assertIsString($actual["format"], "getInfo()[format] is not a string|null");
    }

    // these are the required keys and will fail if expectations are not provided
    $expected += ["code" => $code, "message" => null, "format" => null];
    foreach ($expected as $key => $expectedValue) {
      $this->assertArrayHasKey($key, $actual, "getInfo()[{$key}] is missing");
      $this->assertSame(
        $expectedValue,
        $actual[$key],
        "getInfo()[{$key}] does not match expected value {$this->asString($expectedValue)}"
      );
    }
  }

  /**
   * @dataProvider infoProvider
   *
   * @param int $code Known exceptable code to get info for
   */
  public function testHasInfo(int $code) : void {
    $fqcn = $this->exceptableFQCN();

    $this->assertTrue($fqcn::hasInfo($code), "{$fqcn} reports it has no info for code {$code}");
  }

  /**
   * @return array[] Testcases - @see testGetInfo()
   */
  public function infoProvider() : array {
    return [
      "TestExceptable::UNKNOWN_FOO" => [
        TestExceptable::UNKNOWN_FOO,
        TestExceptable::INFO[TestExceptable::UNKNOWN_FOO]
      ],
      "TestExceptable::TOO_MUCH_FOO" => [
        TestExceptable::TOO_MUCH_FOO,
        TestExceptable::INFO[TestExceptable::TOO_MUCH_FOO]
      ]
    ];
  }

  /**
   * @dataProvider badInfoProvider
   *
   * @param int $code Unknown exceptable code to get info for
   */
  public function testGetBadInfo(int $code) : void {
    $fqcn = $this->exceptableFQCN();

    $this->expectThrowable(
      new ExceptableError(ExceptableError::NO_SUCH_CODE, ["code" => $code]),
      self::EXPECT_THROWABLE_CODE | self::EXPECT_THROWABLE_MESSAGE
    );

    $fqcn::getInfo($code);
  }

  /**
   * @dataProvider badInfoProvider
   *
   * @param int $code Known exceptable code to get info for
   */
  public function testNotHasInfo(int $code) : void {
    $fqcn = $this->exceptableFQCN();

    $this->assertFalse($fqcn::hasInfo($code), "{$fqcn} reports it has info for code {$code}");
  }

  /**
   * @return array[] Testcases - @see testGetBadInfo()
   */
  public function badInfoProvider() : array {
    return [[66]];
  }

  /**
   * @dataProvider isProvider
   *
   * @param int $code Unknown exceptable code to get info for
   */
  public function testIs(int $code, Throwable $e, bool $expected) : void {
    $fqcn = $this->exceptableFQCN();

    $e_code = get_class($e) . "::{$e->getCode()}";

    if ($expected) {
      $this->assertTrue(
        $fqcn::is($e, $code),
        "{$e_code} does not match expected {$fqcn}::{$code}"
      );

      return;
    }

    $this->assertFalse($fqcn::is($e, $code), "{$e_code} matches unexpected {$fqcn}::{$code}");
  }

  /**
   * @return array[] Testcases - @see testIs()
   */
  public function isProvider() : array {
    return [
      "same class and code" => [
        TestExceptable::UNKNOWN_FOO,
        new TestExceptable(TestExceptable::UNKNOWN_FOO),
        true
      ],
      "same class, different code" => [
        TestExceptable::UNKNOWN_FOO,
        new TestExceptable(TestExceptable::TOO_MUCH_FOO),
        false
      ],
      "subclass, same code" => [
        TestExceptable::UNKNOWN_FOO,
        new class (TestExceptable::UNKNOWN_FOO) extends TestExceptable {},
        false
      ],
      "non-exceptable, same code" => [
        TestExceptable::UNKNOWN_FOO,
        new Exception("", TestExceptable::TOO_MUCH_FOO),
        false
      ],
      "non-exceptable, different code" => [
        TestExceptable::UNKNOWN_FOO,
        new Exception("", 66),
        false
      ]
    ];
  }

  /**
   * @dataProvider localizationProvider
   *
   * @param string $locale          Locale to test
   * @param string $resource_bundle ICU resource bundle directory
   */
  public function testLocalization(string $locale, string $resource_bundle) : void {
    if (! extension_loaded("intl")) {
      $this->markTestSkipped("ext/intl is not loaded");
      return;
    }

    $fqcn = $this->exceptableFQCN();

    // assure clean state (localization not initialized)
    $this->setNonpublicStaticProperty($fqcn, "locale", null);
    $this->setNonpublicStaticProperty($fqcn, "messages", null);

    $messages = new ResourceBundle($locale, $resource_bundle);
    $fqcn::localize($locale, $messages);

    $this->assertSame(
      $this->getNonpublicStaticProperty($fqcn, "locale"),
      $locale,
      "\$locale does not contain expected locale '{$locale}'"
    );
    $this->assertSame(
      $this->getNonpublicStaticProperty($fqcn, "messages"),
      $messages,
      "\$messages does not contain expected message bundle"
    );
  }

  /**
   * @return array[] Testcases - @see testLocalization()
   */
  public function localizationProvider() : array {
    return [
      "root" => ["en", self::RESOURCE_BUNDLE],
      "other" => ["ko_KR", self::RESOURCE_BUNDLE]
    ];
  }

  /**
   * @dataProvider localizedMessagesProvider
   *
   * @param ?string    $locale          Locale to test
   * @param ?string    $resource_bundle ICU resource bundle directory
   * @param int        $code            Exceptable code to test
   * @param ?array     $context         Contextual information
   * @param ?Throwable $previous        Previous exception to provide
   * @param string     $expected        Expected message (localized, formatted)
   */
  public function testLocalizedMessages(
    ?string $locale,
    ?string $resource_bundle,
    int $code,
    ?array $context,
    ?Throwable $previous,
    string $expected
  ) : void {
    $fqcn = $this->exceptableFQCN();

    // assure clean state (localization not initialized)
    $this->setNonpublicStaticProperty($fqcn, "locale", null);
    $this->setNonpublicStaticProperty($fqcn, "messages", null);

    if (isset($locale, $resource_bundle)) {
      if (! extension_loaded("intl")) {
        $this->markTestSkipped("ext/intl is not loaded");
        return;
      }

      $fqcn::localize($locale, new ResourceBundle($locale, $resource_bundle));
    }

    $this->assertSame(
      $expected,
      $fqcn::create($code, $context, $previous)->getMessage(),
      "getMessage() does not report expected localized message"
    );
  }

  /**
   * ko_KR was chosen at random; I don't speak Korean nor do I have a translator.
   * If someone would like to provide better (or more) translations, that would be welcome.
   *
   * @return array[] Testcases - @see testLocalizedMessages()
   */
  public function localizedMessagesProvider() : array {
    return [
      "no localization, no context" => [
        null,
        null,
        TestExceptable::UNKNOWN_FOO,
        null,
        null,
        "unknown foo"
      ],
      "no localization, context" => [
        null,
        null,
        TestExceptable::UNKNOWN_FOO,
        ["foo" => "foobedobedoo"],
        null,
        "i don't know who, you think is foo, but it's not foobedobedoo"
      ],

      "root localization, no context" => [
        "en",
        self::RESOURCE_BUNDLE,
        TestExceptable::UNKNOWN_FOO,
        null,
        null,
        "unknown foo"
      ],
      "root localization, wrong context" => [
        "en",
        self::RESOURCE_BUNDLE,
        TestExceptable::UNKNOWN_FOO,
        ["bar" => "none"],
        null,
        "unknown foo"
      ],
      "root localization, context" => [
        "en",
        self::RESOURCE_BUNDLE,
        TestExceptable::UNKNOWN_FOO,
        ["foo" => "foobedobedoo"],
        null,
        "i don't know who, you think is foo, but it's not foobedobedoo"
      ],

      "other localization, no context" => [
        "ko_KR",
        self::RESOURCE_BUNDLE,
        TestExceptable::UNKNOWN_FOO,
        null,
        null,
        "unknown foo"
      ],
      "other localization, wrong context" => [
        "ko_KR",
        self::RESOURCE_BUNDLE,
        TestExceptable::UNKNOWN_FOO,
        ["bar" => "none"],
        null,
        "unknown foo"
      ],
      "other localization, context" => [
        "ko_KR",
        self::RESOURCE_BUNDLE,
        TestExceptable::UNKNOWN_FOO,
        ["foo" => "foobedobedoo"],
        null,
        "나는 당신이 foo 라고 생각하는 사람을 모르지만 foobedobedoo 가 아닙니다"
      ],

      "no localization, complex format" => [
        null,
        null,
        TestExceptable::TOO_MUCH_FOO,
        ["count" => 42],
        null,
        "too much foo is bad for you (got 42 foo)"
      ],
      "root localization, complex format" => [
        "en",
        self::RESOURCE_BUNDLE,
        TestExceptable::TOO_MUCH_FOO,
        ["count" => 42],
        null,
        "too much foo is bad for you (got forty-two foo)"
      ],
      "other localization, complex format" => [
        "ko_KR",
        self::RESOURCE_BUNDLE,
        TestExceptable::TOO_MUCH_FOO,
        ["count" => 42],
        null,
        "너무 많은 foo는 당신에게 나쁩니다 (사십이 foo를 얻었습니다)"
      ],

      "unsupported locale, no context" => [
        "nd_ZW",
        self::RESOURCE_BUNDLE,
        TestExceptable::UNKNOWN_FOO,
        null,
        null,
        "unknown foo"
      ],
      "unsupported locale, with localization and context" => [
        "nd_ZW",
        self::RESOURCE_BUNDLE,
        TestExceptable::UNKNOWN_FOO,
        ["foo" => "foobedobedoo"],
        null,
        "i don't know who, you think is foo, but it's not foobedobedoo"
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
  protected function assertOrigination(Exceptable $actual, string $file, int $line) : void {
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
   * The Fully-qualified Exceptable classname for this test.
   *
   * @return string
   */
  protected function exceptableFQCN() : string {
    return TestExceptable::class;
  }
}

/** Default test class. */
class TestExceptable extends Exception implements Exceptable {
  use IsExceptable;

  const UNKNOWN_FOO = 1;
  const TOO_MUCH_FOO = 2;

  const INFO = [
    self::UNKNOWN_FOO => [
      "message" => "unknown foo",
      "format" => "i don't know who, you think is foo, but it's not {foo}",
      "formatKey" => "exceptable.tests.testexceptable.unknownfoo"
    ],
    self::TOO_MUCH_FOO => [
      "message" => "too much foo",
      "format" => "too much foo is bad for you (got {count} foo)",
      "formatKey" => "exceptable.tests.testexceptable.toomuchfoo"
    ]
  ];
}
