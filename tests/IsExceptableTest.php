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

use Exception;

use AT\Exceptable\ {
  Exceptable,
  ExceptableException,
  IsExceptable,
  Tests\TestCase
};

/**
 * Basic tests for the default Exceptable implementation (the IsExceptable trait).
 *
 * This test case can be extended to test other concrete implementations:
 *  - override newIsExceptable() method to provide an instance of the appropriate class
 *  - override *Provider methods as needed to provide appropriate input and expectations:
 *    - infoProvider()
 *    - badInfoProvider()
 */
class IsExceptableTest extends TestCase {

  /**
   * @var array[] Test Exceptable INFO const value.
   * @internal This is public only due to technical limitations; child classes must ignore it.
   */
  public const TEST_INFO = [
    1 => [
      "message" => "unknown foo",
      "format" => "i don't know who, you think is foo, but it's not {foo}"
    ],
    2 => [
      "message" => "too much foo",
      "format" => "too much foo is bad for you (got {count} foo)"
    ]
  ];

  /**
   * @covers IsExceptable::getInfo()
   * @dataProvider infoProvider
   *
   * @param int        $code     Known exceptable code to get info for
   * @param array|null $expected Expected [message, ?format]
   */
  public function testGetInfo(int $code, array $expected) : void {
    // minimum requirements (these _should_ always be provided)
    $expected += ["code" => $code, "message" => ""];

    $actual = $this->newIsExceptable($code)::getInfo($code);

    $this->assertIsArray($actual);
    foreach ($expected as $key => $expectedValue) {
      $this->assertArrayHasKey($key, $actual, "getInfo()[{$key}] exists");
      $this->assertSame(
        $expectedValue,
        $actual[$key],
        "[$key] {$actual[$key]} matches expected value {$expectedValue}"
      );
    }
  }

  /**
   * @return array[] Testcases:
   *  - int   $0 Known Exceptable code
   *  - array $1 Information expected to be returned:
   *    - string $message Plain exception message
   *    - string $format  Formatting string for message with context
   *    - mixed  $...     Additional key:value pairs
   */
  public function infoProvider() : array {
    return [
      [1, self::TEST_INFO[1]],
      [2, self::TEST_INFO[2]]
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
    return new class(...$args) extends Exception implements Exceptable {
      use IsExceptable;

      const UNKNOWN_FOO = 1;
      const TOO_MUCH_FOO = 2;

      const INFO = IsExceptableTest::TEST_INFO;
    };
  }
}
