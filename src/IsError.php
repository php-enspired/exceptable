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
namespace at\exceptable;

use BackedEnum,
  Throwable,
  UnitEnum;

use at\exceptable\ {
  Error,
  Spl\RuntimeException
};

use at\peekaboo\MakesMessages;

/**
 * Defines error cases for an Exceptable.
 *
 * Implementing Enums may define MESSAGES to provide default message templates.
 * If not defined, the case name will be used.
 */
trait isError {
  use MakesMessages;

  /** @see Error::code() */
  public function code() : int {
    assert($this instanceof UnitEnum);

    // return enum value if integers provided
    if ($this instanceof BackedEnum && is_int($this->value)) {
      return $this->value;
    }

    // else determine code based on case order
    foreach ($this->cases() as $code => $case) {
      if ($case === $this) {
        return $code + 1;
      }
    }
  }

  /** @see Error::message() */
  public function message(array $context) : string {
    assert($this instanceof UnitEnum);

    $error = static::class . ".{$this->name}";
    $message = $this->makeMessage($this->name, $context);
    return empty($message) ?
      $error :
      "{$error}: {$message}";
  }

  /** @see Error::throw() */
  public function exceptable(array $context = [], Throwable $previous = null) : Exceptable {
    assert($this instanceof Error);
    return RuntimeException::from($this, $context, $previous);
  }

  /** @see UnitEnum::cases() */
  abstract public static function cases() : array;
}
