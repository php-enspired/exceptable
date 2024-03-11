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

  /** @see Exceptable::__construct() */
  public function __construct(
    protected ? Error $error = null,
    protected array $context = [],
    Throwable $previous = null
  ) {
    assert($this instanceof Exceptable);

    $this->error ??= $this->defaultError();

    // if there's no previous exception, these won't be available to the message formatter.
    if (! empty($previous)) {
      $root = $this->findRoot($previous);
      $this->context["__rootType__"] = $root::class;
      $this->context["__rootMessage__"] = $root->getMessage();
      $this->context["__rootCode__"] = $root->getCode();
    } else {
      $this->context["__rootType__"] = static::class;
      $this->context["__rootMessage__"] = "";
      $this->context["__rootCode__"] = $this->error->code();
    }

    // @phan-suppress-next-line PhanTraitParentReference
    parent::__construct($this->error->message($this->context), $this->error->code(), $previous);
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
    // @phan-suppress-next-line PhanTypeMismatchReturnNullable
    return $this->error;
  }

  /** @see Exceptable::has() */
  public function has(Error $error) : bool {
    $t = $this;
    while ($t instanceof Throwable) {
      if ($t instanceof Exceptable && $t->error === $error) {
        return true;
      }

      $t = $t->getPrevious();
    }

    return false;
  }

  /** @see Exceptable::is() */
  public function is(Error $error) : bool {
    return $this->error === $error;
  }

  /** @see Exceptable::root() */
  public function root() : Throwable {
    assert($this instanceof Throwable);
    return $this->findRoot($this);
  }

  /**
   * Gets the default (code 0) Error case for this Exceptable.
   *
   * @return Error
   */
  protected function defaultError() : Error {
    return ExceptableError::UnknownError;
  }

  /**
   * Finds the previous-most exception from the given exception.
   *
   * @param Throwable $t the exception to start from
   * @return Throwable The root exception (may be the same as the starting exception)
   */
  private function findRoot(Throwable $t) : Throwable {
    $root = $t;
    while (($previous = $root->getPrevious()) instanceof Throwable) {
      $root = $previous;
    }

    return $root;
  }
}
