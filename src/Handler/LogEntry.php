<?php
/**
 * @package    at.exceptable
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

namespace at\exceptable\Handler;

/** Base class for error log entries. */
abstract class LogEntry {

  /** @var int Error code. */
  public ? int $code = null;

  /** @var string Filename. */
  public ? string $file = null;

  /** @var bool Was this error handled by a registered handler? */
  public bool $handled = false;

  /** @var int Line number. */
  public ? int $line = null;

  /** @var string Error message. */
  public ? string $message = null;

  /** @var float Unixtime error was logged, with microsecond precision. */
  public float $time;

  /** @param array $details Log entry values to set, in property:value format */
  public function __construct(array $details = []) {
    foreach ($details as $property => $value) {
      if (property_exists($this, $property)) {
        $this->$property = $value;
      }
    }

    $this->time = microtime(true);
  }

  /**
   * Casts log entry to array (for use with PSR-3)
   *
   * @return array
   */
  public function toArray() : array {
    return (array) $this;
  }
}
