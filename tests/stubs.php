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

namespace at\exceptable\Handler;

use at\exceptable\Tests\HandlerTest;

if (! function_exists(register_shutdown_function::class)) {
  /**
   * Stubs register_shutdown_function() for tests.
   * @see https://php.net/register_shutdown_function
   */
  function register_shutdown_function(callable $callback, ...$args) : void {
    HandlerTest::notify(explode("\\", __FUNCTION__)[3], $callback, $args);
  }
}

if (! function_exists(restore_error_handler::class)) {
  /**
   * Stubs set_error_handler() for tests.
   * @see https://php.net/restore_error_handler
   */
  function restore_error_handler() {
    HandlerTest::notify(explode("\\", __FUNCTION__)[3]);
    return true;
  }
}

if (! function_exists(restore_exception_handler::class)) {
  /**
   * Stubs set_exception_handler() for tests.
   * @see https://php.net/restore_exception_handler
   */
  function restore_exception_handler() {
    HandlerTest::notify(explode("\\", __FUNCTION__)[3]);
    return true;
  }
}

if (! function_exists(set_error_handler::class)) {
  /**
   * Stubs set_error_handler() for tests.
   * @see https://php.net/set_error_handler
   */
  function set_error_handler(callable $error_handler, int $error_types = E_ALL | E_STRICT) {
    HandlerTest::notify(explode("\\", __FUNCTION__)[3], $error_handler, $error_types);
    return null;
  }
}

if (! function_exists(set_exception_handler::class)) {
  /**
   * Stubs set_exception_handler() for tests.
   * @see https://php.net/set_exception_handler
   */
  function set_exception_handler(callable $exception_handler) {
    HandlerTest::notify(explode("\\", __FUNCTION__)[3], $exception_handler);
    return null;
  }
}
