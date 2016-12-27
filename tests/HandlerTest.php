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

namespace at\exceptable\tests;

use ErrorException,
    Throwable;
use at\exceptable\Handler;
use PHPUnit_Framework_Error as PHPError,
    PHPUnit\Framework\TestCase;

/**
 * tests Handler.
 */
class HandlerTest extends TestCase {

  /**
   * @covers Handler::during()
   * @covers Handler::register()
   * @covers Handler::unregister()
   */
  public function testDuring() {
    // @todo: also test that an exception handler is properly registered.
    $this->markTestIncomplete();

    $f1 = function(...$e) use (&$list1) { $list1[] = $e; return true; };
    $f2 = function(...$e) use (&$list2) { $list2[] = $e; return true; };

    // $f2 should be invoked
    $h1 = (new Handler)->onError($f1)->register();
    (new Handler)->onError($f2)->during('trigger_error', 'foo 2', E_USER_ERROR);
    $this->assertEmpty($list1);
    $this->assertCount(1, $list2);

    // $f1 should be invoked
    trigger_error('foo 1', E_USER_ERROR);
    $this->assertCount(1, $list1);

    // neither should be invoked
    $h1->unregister();
    $this->expectException(PHPError::class);
    trigger_error('foo 0', E_USER_ERROR);
  }

  /**
   * @covers Handler::_error()
   */
  public function testError() {
    $this->markTestIncomplete();
  }

  /**
   * @covers Handler::_exception()
   */
  public function testException() {
    $this->markTestIncomplete();
  }

  /**
   * @covers Handler::_shutdown()
   */
  public function testShutdown() {
    $this->markTestIncomplete();
  }

  /**
   * @covers Handler::throw()
   */
  public function testThrow() {
    $this->markTestIncomplete();
  }

  /**
   * @covers Handler::onError()
   * @covers Handler::onException()
   * @covers Handler::onShutdown()
   */
  public function testOn() {
    $f1 = function() {};
    $f2 = function() {};
    foreach (['error', 'exception', 'shutdown'] as $type) {
      $handler = (new Handler)->{"on{$type}"}($f1)->{"on{$type}"}($f2);
      $registered = $this->_getHandlerList($type, $handler);
      $this->assertContains($f1, $registered);
      $this->assertContains($f2, $registered);
    }
  }

  /**
   * gets the callable handler from each _Handler registered for a given type.
   *
   * @param string  $type     one of error|exception|shutdown
   * @param Handler $handler  subject Handler
   * @return callable[]       the target callables
   */
  protected function _getHandlerList(string $type, Handler $handler) : array {
    return (function() use ($type) {
      $handlers = [];
      foreach ($this->{"_{$type}Handlers"} as $_handler) {
        $handlers[] = (function() { return $this->_handler; })->call($_handler);
      }
      return $handlers;
    })->call($handler);
  }
}
