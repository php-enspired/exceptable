<?php
/**
 * @package    at.exceptable
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2018
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

namespace at\exceptable;

use Exception as BaseException,
  Throwable;

use at\exceptable\ {
  Exceptable,
  ExceptableException,
  HasContext
};

/**
 * convenience base class for Exceptables.
 */
abstract class Exception extends BaseException implements Exceptable {
  use HasContext;

  /**
   * @type array INFO {
   *    @type array ${$code} {
   *      @type string $message         the exception message
   *      @type int    $severity        the exception severity
   *      @type string $contextMessage  an exception message with {}-delimited placeholders
   *      @type mixed  $...             implementation-specific additional info
   *    }
   *    ...
   *  }
   */
  const INFO = [];

  /** {@inheritDoc} */
  public static function getInfo(int $code) : array {
    if (! static::hasInfo($code)) {
      throw new ExceptableException(ExceptableException::NO_SUCH_CODE, ['code' => $code]);
    }

    return static::INFO[$code] + [
      'code' => $code,
      'severity' => Exceptable::ERROR
    ];
  }

  /** {@inheritDoc} */
  public static function hasInfo(int $code) : bool {
    return isset(static::INFO[$code]['message']);
  }
}
