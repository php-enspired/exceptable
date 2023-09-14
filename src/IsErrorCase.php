<?php
/**
 * @package    at.exceptable
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
namespace at\exceptable;

use BackedEnum,
  Throwable;

use at\peekaboo\HasMessages;

/**
 * Defines error cases for an Exceptable.
 *
 * Implementing enums MUST:
 * - be integer-backed (ints are error codes)
 * and SHOULD:
 * - define EXCEPTABLE with the fully-qualified classname for the Exceptable class it should throw
 *   (will throw RuntimeErrors otherwise)
 * - implement ->substituterMessageFormat() to provide a basic message template for each case
 *   (tip: use match(this) { ... })
 */
trait isErrorCase {
  use HasMessages;

  /** {@inheritDoc} */
  public function throw(array $context = [], Throwable $previous = null) : void {
    throw $this->exceptableType()::fromCase($this, $context, $previous)->adjust();
  }

  /**
   * Finds the Exceptable FQCN this ErrorCase should throw.
   *
   * @throws ExceptableError
   *  UNACCEPTABLE_EXCEPTABLE if static::EXCEPTABLE is defined but is not an Exceptable FQCN
   * @return string A fully qualified Exceptable classname
   */
  protected function exceptableType() : string {
    if (defined("static::EXCEPTABLE")) {
      if (! is_a(static::EXCEPTABLE, Exceptable::class, true)) {
        ExceptableError::E::UNACCEPTABLE_EXCEPTABLE->throw([
          "type" => is_string(static::EXCEPTABLE) ?
            static::EXCEPTABLE :
            get_debug_type(static::EXCEPTABLE)
        ]);
      }

      return static::EXCEPTABLE;
    }

    return RuntimeException::class;
  }

  /** {@inheritDoc} */
  protected function findSubstituterMessageFormat(string $key) : ? string {
    $message = "{$this->exceptableType()}::E::{$this->name}";
    $format = $this->substituterMessageFormat();
    return empty($format) ?
      $message :
      "{$message}: {$format}";
  }

  /**
   * Provides a message format for this case.
   *
   * Returns an empty format by default.
   * Override this method to return an appropriate message format for each of your cases.
   *
   * @return string A message format string with {tokens} for replacements
   */
  protected function substituterMessageFormat() : string {
    return match($this) {
      default => ""
    };
  }
}
