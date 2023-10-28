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

use Throwable;

use at\exceptable\ {
  Error,
  Exceptable
};

/**
 * Base implementation for Exceptable interface, including contexted message construction.
 *
 * This trait is intended for use only by Exceptable implementations.
 * Methods assert() this but provide no runtime checks if asserts are disabled - approach with caution!
 */
trait IsExceptable {

  /** @see Exceptable::from() */
  public static function from(Error $e = null, array $context = [], Throwable $previous = null) : Exceptable {
    assert(is_a(static::class, Exceptable::class, true));
    // @phan-suppress-next-line PhanUndeclaredConstantOfClass
    $e ??= static::DEFAULT_ERROR;
    assert($e instanceof Error);
    // @todo Is [2] correct?
    // @phan-suppress-next-line PhanTypeInstantiateTraitStaticOrSelf
    $ex = new static($e, $context, $previous, 2);
    assert($ex instanceof Exceptable);

    return $ex;
  }

  /**
   * Finds the previous-most exception from the given exception.
   *
   * @param Throwable $ex the exception to start from
   * @return Throwable The root exception (may be the same as the starting exception)
   */
  private static function findRoot(Throwable $ex) : Throwable {
    $root = $ex;
    while (($previous = $root->getPrevious()) !== null) {
      $root = $previous;
    }

    return $root;
  }

  /** @see Exceptable::context() */
  public function context() : array {
    assert($this instanceof Throwable);

    return [
      "__message__" => $this->getMessage()
    ] + $this->context;
  }

  /** @see Exceptable::error() */
  public function error() : Error {
    return $this->error;
  }

  /** @see Exceptable::has() */
  public function has(Error $e) : bool {
    $ex = $this;
    while ($ex instanceof Exceptable) {
      if ($ex->error === $e) {
        return true;
      }

      $ex = $ex->getPrevious();
    }

    return false;
  }

  /** @see Exceptable::is() */
  public function is(Error $e) : bool {
    return ($this->error ?? null) === $e;
  }

  /** @see Exceptable::root() */
  public function root() : Throwable {
    assert($this instanceof Throwable);

    return static::findRoot($this);
  }

  /** Nonpublic constructor. Use Exceptable::from(). */
  private function __construct(
    protected Error $error,
    protected array $context = [],
    Throwable $previous = null,
    int $adjust = 1
  ) {
    assert($this instanceof Exceptable);

    if (! empty($previous)) {
      $root = static::findRoot($previous);
      $context["__rootType__"] = $root::class;
      $context["__rootMessage__"] = $root->getMessage();
      $context["__rootCode__"] = $root->getCode();
    }

    // @phan-suppress-next-line PhanTraitParentReference
    parent::__construct($this->error->message($context), $this->error->code(), $previous);

    $frame = $this->getTrace()[$adjust] ?? null;
    if (! empty($frame)) {
      // @phan-suppress-next-line PhanUndeclaredProperty
      $this->file = $frame["file"];
      // @phan-suppress-next-line PhanUndeclaredProperty
      $this->line = $frame["line"];
    }
  }
}
