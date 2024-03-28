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
  ResourceBundle,
  Throwable;

use at\exceptable\ {
  Error,
  Exceptable,
  Spl\RuntimeException
};

use at\peekaboo\ {
  MakesMessages,
  MessageBundle
};

/**
 * Defines error cases for an Exceptable.
 *
 * Implementing Enums may be integer-backed; these values will be used as error codes.
 * Otherwise, error codes will be determined by the case's declaration order.
 *
 * Implementing Enums may define MESSAGES to provide default message templates.
 * Defined messages will be wrapped in an array indexed with the Error classname,
 *  using underscores instead of backslashes.
 * Otherwise, messages will be built using the Error class and case name.
 */
trait isError {
  use MakesMessages;

  /** @see MakeMessages::messageBundle() */
  public static function messageBundle() : ResourceBundle {
    // @see Error::MESSAGES
    // @phan-suppress-next-line PhanUndeclaredConstantOfClass
    return new MessageBundle([strtr(static::class, ["\\" => "_"]) => static::MESSAGES]);
  }

  /** @see Error::throw() */
  public function __invoke(array $context = [], Throwable $previous = null) : Exceptable {
    assert($this instanceof Error);
    $x = $this->exceptableType();
    assert(is_a($x, Exceptable::class, true));

    return $this->adjustExceptable(new $x($this, $context, $previous), 0);
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

  /**
   * Gets the full human-readable name for this Error.
   *
   * This is expected to be usable as the message $key.
   *
   * @return string Error class and name
   */
  final public function errorName() : string {
    assert($this instanceof Error);

    return static::class . ".{$this->name}";
  }

  /** @see Error::exceptableType() */
  public function exceptableType() : string {
    return RuntimeException::class;
  }

  /** @see Error::message() */
  public function message(array $context = []) : string {
    assert($this instanceof Error);

    $error = $this->errorName();
    $message = $this->makeMessage(strtr($error, ["\\", "_"]), $context);
    return (empty($message) || $this->isDefaultFormat($message)) ?
      $error :
      "{$error}: {$message}";
  }

  /** @see Error::throw() */
  public function newExceptable(array $context = [], Throwable $previous = null) : Exceptable {
    assert($this instanceof Error);
    $x = $this->exceptableType();
    assert(is_a($x, Exceptable::class, true));

    return $this->adjustExceptable(new $x($this, $context, $previous), 0);
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

  /**
   * Is the given message identical to the default message format string, and does it include formatting tokens
   *  (i.e., were replacements expected but none were actually made)?
   *
   * @param string $message The message to inspect
   * @return bool True if the message is identical to the default format string; false otherwise
   */
  private function isDefaultFormat(string $message) : bool {
    if (preg_match("(\{.*\})", $message) < 1) {
      return false;
    }

    $defaultMessage = static::messageBundle();
    foreach (explode(".", $this->errorName()) as $next) {
      if (! $defaultMessage instanceof ResourceBundle) {
        return false;
      }

      $defaultMessage = $defaultMessage->get($next);
    }

    return $message === strtr($defaultMessage, ["''" => "'"]);
  }
}
