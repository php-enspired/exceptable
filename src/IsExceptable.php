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

use MessageFormatter,
  ResourceBundle,
  Throwable;

use at\exceptable\ {
  ErrorCase,
  Exceptable
};

/**
 * Base implementation for Exceptable interface, including contexted message construction.
 * This trait MUST be used by a class which extends from Exception and implements Exceptable.
 *
 * @phan-file-suppress PhanUndeclaredConstantOfClass
 *  const INFO is expected to be defined by implementations; all usage here checks first.
 */
trait IsExceptable {
  use HasExceptableInternals;

  /** @see Exceptable::fromCase() $case */
  protected ErrorCase $case;

  /** @see Exceptable::fromCase() $context */
  protected array $context = [];

  /** @see Exceptable::fromCase() $previous */
  protected ? Throwable $previous = null;

  /** @see Exceptable::fromCase() */
  public static function fromCase(ErrorCase $case, array $context = [], ? Throwable $previous = null) : Exceptable {
    assert(is_a(static::class, Exceptable::class, true));
    return (new static($case->message($this->context), $case->value, $previous))->adjust();
  }

  /** @see Exceptable::is() */
  public function is(ErrorCase $case) : bool {
    return $this->case === $case;
  }

  /** @see Exceptable::case() */
  public function case() : ErrorCase {
    return $this->case;
  }

  /** @see Exceptable::context() */
  public function context() : array {
    return $this->context;
  }

  /** @see Exceptable::root() */
  public function root() : Throwable {
    assert($this instanceof Throwable);
    return $this->findRoot($this);
  }

  /**
   * Finds the root (most-previous) exception of the given exception.
   *
   * @param Throwable $root Subject exception
   * @return Throwable Root exception
   */
  protected function findRoot(Throwable $root) : Throwable {
    while (($previous = $root->getPrevious()) !== null) {
      $root = $previous;
    }

    return $root;
  }
}

/** Base implementation for Exceptable internal methods. */
trait HasExceptableInternals {

  /** @see ExceptableInternals::throw() */
  public function adjust(int $frame = 0) : Exceptable {
    $info = $this->getTrace()[$frame] ?? null;
    if (isset($frame)) {
      $this->file = $info["file"];
      $this->line = $info["line"];
    }

    return $this;
  }
}
