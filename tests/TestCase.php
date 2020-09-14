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

use BadMethodCallException,
  Throwable;

use PHPUnit\Framework\TestCase as PhpUnitTestCase;

/**
 * Base Test Case for Exceptables.
 */
abstract class TestCase extends PhpUnitTestCase {

  /** @var int Flag: expectThrowable() should expect code to match. */
  public const EXPECT_THROWABLE_CODE = 1;

  /** @var int Flag: expectThrowable() should expect message to match. */
  public const EXPECT_THROWABLE_MESSAGE = 1 << 1;

  /**
   * Sets phpunit's expectException*() methods from an example.
   *
   * @param Throwable $expected Exception the test expects to be thrown
   * @param int       $flags    Bitmask of ::EXPECT_THROWABLE_* flags
   */
  public function expectThrowable(Throwable $expected, int $flags = 0) : void {
    $this->expectException(get_class($expected));

    if ((self::EXPECT_THROWABLE_CODE & $flags) === self::EXPECT_THROWABLE_CODE) {
      $this->expectExceptionCode($expected->getCode());
    }

    if ((self::EXPECT_THROWABLE_MESSAGE & $flags) === self::EXPECT_THROWABLE_MESSAGE) {
      $this->expectExceptionMessage($expected->getMessage());
    }
  }

  /**
   * Gets the value of a nonpublic property of an object under test.
   *
   * @param object $object   The object to inspect
   * @param string $property The property to access
   * @return mixed           Property value on success
   */
  protected function getNonpublicProperty(object $object, string $property) {
    $ro = new ReflectionObject($object);
    if (! $ro->hasProperty($property)) {
      throw new BadMethodCallException(
        "Object [" . get_class($object) . "] has no property '{$property}'"
      );
    }

    $rp = $ro->getProperty($property);
    $rp->setAccessible(true);
    return $rp->getValue($object);
  }

  /**
   * Invokes a nonpublic method on an object under test.
   *
   * Note, it's VERY EASY to BREAK EVERYTHING using this method.
   *
   * @param object $object  The object under test
   * @param string $method  The method to invoke
   * @param mixed  ...$args Argument(s) to use for invocation
   * @return mixed          The return value from the method invocation
   */
  protected function invokeNonpublicMethod(object $object, string $method, ...$args) {
    $ro = new ReflectionObject($object);
    if (! $ro->hasMethod($method)) {
      throw new BadMethodCallException(
        "Object [" . get_class($object) . "] has no method '{$method}'"
      );
    }

    $rm = $ro->getMethod($method);
    $rm->setAccessible(true);
    return $rm->invoke($object, ...$args);
  }

  /**
   * Sets the value of a nonpublic property of an object under test.
   *
   * Note, it's VERY EASY to BREAK EVERYTHING using this method.
   *
   * @param object $object   Object to modify
   * @param string $property Property to set
   * @param mixed  $value    Value to set
   * @return void
   */
  protected function setNonpublicProperty(object $object, string $property, $value) : void {
    $ro = new ReflectionObject($object);
    if (! $ro->hasProperty($property)) {
      throw new BadMethodCallException(
        "Object [" . get_class($object) . "] has no property '{$property}'"
      );
    }

    $rp = $ro->getProperty($property);
    $rp->setAccessible(true);
    $rp->setValue($object, $value);
  }
}
