<?php
/**
 * @package    at.util.tests
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
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

namespace at\util\tests;

use Throwable;
use at\exceptable\Exceptable,
    at\exceptable\ExceptableException,
    at\exceptable\Exception;
use PHPUnit\Framework\TestCase;

/**
 * tests Exception (also suitable for testing concrete implementations).
 */
class ExceptableTest extends TestCase {

  /**
   * @covers Exception::get_info()
   */
  public function testGetInfo() {
    $exceptable = $this->_getExceptable();
    foreach (array_keys($exceptable::INFO) as $code) {
      $actual = $exceptable::get_info($code);
      foreach (['code', 'message', 'severity'] as $key) {
        $this->assertArrayHasKey($key, $actual);
      }
      $this->assertEquals($code, $actual['code']);
      $this->assertContains(
        $actual['severity'],
        [Exceptable::ERROR, Exceptable::WARNING, Exceptable::NOTICE],
        true
      );
      $this->assertInternalType('string', $actual['message']);
    }
  }

  /**
   * @covers Exception::has_info()
   */
  public function testHasInfo() {
    $exceptable = $this->_getExceptable();

    // has
    $this->assertTrue($exceptable::has_info(1));
    $this->assertTrue($exceptable::has_info(2));

    // has not
    $this->assertFalse($exceptable::has_info(3));
  }

  /**
   * @covers Exception::__construct()
   * @dataProvider _exceptableProvider
   *
   * @param array          $args      exceptable constructor arguments
   * @param Throwable|null $expected  expected exception if any; null otherwise
   */
  public function testExceptable(array $args, Throwable $expected=null) {
    if ($expected) {
      $this->_setExceptionExpectations($expected);
    }

    $actual = $this->_getExceptable(...$args);

    // this test only cares about whether the constructor works.
    $this->assertTrue($actual instanceof Exceptable);
  }

  /**
   * @covers Exceptable::__construct()
   * @covers Exceptable::getCode()
   */
  public function testGetCode() {
    // default code
    $this->assertEquals(0, $this->_getExceptable()->getCode());

    // explicit code
    $this->assertEquals(1, $this->_getExceptable(1)->getCode());
    $this->assertEquals(2, $this->_getExceptable(2)->getCode());

    // unknown code
    $this->_setExceptionExpectations(
      new ExceptableException(ExceptableException::NO_SUCH_CODE, ['code' => 99])
    );
    $this->_getExceptable(99);
  }

  /**
   * @covers Exceptable::__construct()
   * @covers Exceptable::addContext()
   * @covers Exceptable::getContext()
   */
  public function testGetContext() {
    $context = ['foo' => 'foo'];
    $this->assertEquals($context, $this->_getExceptable($context)->getContext());
    $this->assertEquals($context, $this->_getExceptable()->addContext($context)->getContext());
  }

  /**
   * @covers Exceptable::__construct()
   * @covers Exceptable::getMessage()
   * @dataProvider _messageProvider
   *
   * @param array  $args      exceptable constructor arguments
   * @param string $expected  the expected exception message
   */
  public function testGetMessage(array $args, string $expected) {
    $this->assertEquals($expected, $this->_getExceptable(...$args)->getMessage());
  }

  /**
   * @covers Exceptable::__construct()
   * @covers Exceptable::getRoot()
   */
  public function testGetRoot() {
    $root = $this->_getExceptable();
    $previous = $this->_getExceptable($root);
    $actual = $this->_getExceptable($previous)->getRoot();
    $this->assertEquals(
      get_class($root) . ':' . spl_object_hash($root),
      get_class($actual) . ':' . spl_object_hash($actual)
    );
  }

  /**
   * @covers Exceptable::__construct()
   * @covers Exceptable::getSeverity()
   * @covers Exceptable::setSeverity()
   * @covers Exceptable::isError()
   * @covers Exceptable::isWarning()
   * @covers Exceptable::isNotice()
   */
  public function testGetSeverity() {
    $severities = [
      'isError' => Exceptable::ERROR,
      'isWarning' => Exceptable::WARNING,
      'isNotice' => Exceptable::NOTICE
    ];

    // explicit (cool)
    foreach ($severities as $severity) {
      $this->assertEquals(
        $severity,
        $this->_getExceptable()->setSeverity($severity)->getSeverity()
      );
    }

    // default (cool)
    foreach ([0, true, null, [], 'foo'] as $invalid) {
      $this->assertEquals(
        Exceptable::DEFAULT_SEVERITY,
        $this->_getExceptable(['severity' => $invalid])->getSeverity()
      );
    }

    // is*() severity check methods
    foreach (array_keys($severities) as $method) {
      foreach ($severities as $check => $severity) {
        $actual = $this->_getExceptable()->setSeverity($severity)->$method();
        if ($method === $check) {
          $this->assertTrue($actual);
        } else {
          $this->assertFalse($actual);
        }
      }
    }
  }

  /**
   * @covers Exceptable::__toString()
   */
  public function testToString() {
    $context = ['foo' => 'foo'];
    $this->assertRegExp(
      "(\ncontext: "
        . json_encode($context, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)
        . "$)",
      $this->_getExceptable($context)->__toString()
    );
  }

  /**
   * provides example data for testExceptable().
   *
   * @return array[]  test cases
   */
  public function _exceptableProvider() : array {
    $message = 'message';
    $code = 1;
    $previous = $this->_getExceptable();
    $context = ['foo' => 'foo'];

    $tests = [];

    // cool
    foreach ([$message, null] as $a) {
      $args = [$a];
      foreach ([$code, null] as $b) {
        $args[] = $b;
        foreach ([$previous, null] as $c) {
          $args[] = $c;
          foreach ([$context, null] as $d) {
            $args[] = $d;
            $tests[] = [array_filter($args), null];
            $args = null;
          }
        }
      }
    }

    // uncool
    $exception = new ExceptableException(ExceptableException::INVALID_CONSTRUCT_ARGS);

    // wrong order
    foreach ($tests as $test) {
      $args = $test[0];
      if (count($args) < 2) {
        continue;
      }
      array_unshift($args, array_pop($args));
      $tests[] = [$args, $exception];
    }

    // invalid
    $tests[] = [[$code, $code, $code, $code], $exception];
    $tests[] = [[$message, $code, $previous, $context, 'foo'], $exception];

    return $tests;
  }

  /**
   * provides example data for testGetMessage().
   *
   * @return array[]  test cases
   */
  public function _messageProvider() : array {
    return [
      'default-1' => [[1], 'unknown foo'],
      'default-2' => [[2], 'too much foo'],
      'context-1' => [
        [1, ['foo' => 'foo']],
        "i don't know who, you think is foo, but it's not foo"
      ],
      'context-2' => [[2, ['count' => 42]], 'too much foo is bad for you (got 42 foo)'],
      'fallback-1' => [[1, ['bar' => 'bar']], 'unknown foo'],
      'fallback-2' => [[2, ['dalmations' => 101]], 'too much foo']
    ];
  }

  /**
   * gets an Exceptable test instance.
   *
   * @param int $code  the code to set
   * @return Exceptable
   */
  protected function _getExceptable(...$args) : Exceptable {
    return new class(...$args) extends ExceptableException {

      const UNKNOWN_FOO = 1;
      const TOO_MUCH_FOO = 2;
      const INFO = [
        self::UNKNOWN_FOO => [
          'message' => 'unknown foo',
          'severity' => E_WARNING,
          'tr_message' => "i don't know who, you think is foo, but it's not {foo}"
        ],
        self::TOO_MUCH_FOO => [
          'message' => 'too much foo',
          'severity' => E_NOTICE,
          'tr_message' => 'too much foo is bad for you (got {count} foo)'
        ]
      ];
    };
  }

  /**
   * sets test expectations for a given exception.
   *
   * @param Throwable $e  the exception on which to base expectations
   */
  protected function _setExceptionExpectations(Throwable $expected) {
    $this->expectException(get_class($expected));
    $message = $expected->getMessage();
    $code = $expected->getCode();
    if (! empty($message)) {
      $this->expectExceptionMessage($message);
    }
    if (! empty($code)) {
      $this->expectExceptionCode($code);
    }
  }
}
