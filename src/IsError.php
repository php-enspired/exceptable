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
  Throwable;

use at\exceptable\ {
  Error,
  Spl\RuntimeException
};

use at\peekaboo\MakesMessages;

/**
 * Defines error cases for an Exceptable.
 *
 * Implementing Enums may be integer-backed; these values will be used as error codes.
 * Otherwise, error codes will be determined by the case's declaration order.
 *
 * Implementing Enums may define MESSAGES to provide default message templates.
 * Otherwise, messages will be built using the Error class and case name.
 */
trait isError {
  use MakesMessages;

  /** @see Error::throw() */
  public function __invoke(array $context = [], Throwable $previous = null) : Exceptable {
    assert($this instanceof Error);
    return $this->adjustExceptable(new RuntimeException($this, $context, $previous), 2);
  }

  /** @see Error::code() */
  public function code() : int {
    assert($this instanceof Error);

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
    assert($this instanceof Error);

    $error = static::class . ".{$this->name}";
    $message = $this->makeMessage($this->name, $context);
    return empty($message) ?
      $error :
      "{$error}: {$message}";
  }

  /** @see Error::throw() */
  public function newExceptable(array $context = [], Throwable $previous = null) : Exceptable {
    assert($this instanceof Error);
    return $this->adjustExceptable(new RuntimeException($this, $context, $previous), 1);
  }

  /**
   * Adjusts the Exceptable's $file and $line to reflect the location in code it was thrown from
   *  (vs. where it was actually instantiated).
   *
   * @param Exceptable $x The Exceptable to modify
   * @return Exceptable The modified Exceptable
   */
  private function adjustExceptable(Exceptable $x, int $adjust) : Exceptable {
    (function () use ($x, $adjust) {
      $frame = $x->getTrace()[$adjust] ?? null;
      // no-op if no such frame
      if (! empty($frame)) {
        // @phan-suppress-next-line PhanUndeclaredProperty
        $x->file = $frame["file"];
        // @phan-suppress-next-line PhanUndeclaredProperty
        $x->line = $frame["line"];
      }
    })->call($x, $x);

    return $x;
  }
}
