<?php
/**
 * @package    at.exceptable
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2020
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

namespace AT\Exceptable;

use MessageFormatter,
  ResourceBundle,
  Throwable;

use AT\Exceptable\Exceptable;

/**
 * Base implementation for Exceptable interface, including contexted message construction.
 * This trait MUST be used by a class which extends from Exception and implements Exceptable.
 *
 * @phan-file-suppress PhanUndeclaredConstantOfClass
 *  const INFO is expected to be defined by implementations; all usage here checks first.
 */
trait IsExceptable {

  /** @var string Preferred locale for messages. */
  protected static $locale;

  /** @var ResourceBundle ICU messages bundle. */
  protected static $messages;

  /**
   * Factory: creates a new Exceptable from the given error code,
   * and adjusts exception info to reflect the location of the calling code.
   *
   * @phan-suppress PhanTypeInstantiateTraitStaticOrSelf
   *
   * @see Exceptable::__construct()
   * @return Exceptable
   */
  public static function create(int $code, array $context = [], Throwable $previous = null) : Exceptable {
    $exceptable = new static($code, $context, $previous);

    $frame = $exceptable->getTrace()[0];
    $exceptable->file = $frame["file"];
    $exceptable->line = $frame["line"];

    assert($exceptable instanceof Exceptable);
    return $exceptable;
  }

  /** @see Exceptable::getInfo() */
  public static function getInfo(int $code) : array {
    if (! static::hasInfo($code)) {
      throw ExceptableException::create(ExceptableException::NO_SUCH_CODE, ['code' => $code]);
    }

    return ["code" => $code] +
      static::INFO[$code] +
      ["format" => static::INFO[$code]["message"]];
  }

  /** @see Exceptable::hasInfo() */
  public static function hasInfo(int $code) : bool {
    return defined("static::INFO") &&
      isset(static::INFO[$code]["message"]) &&
      is_string(static::INFO[$code]["message"]);
  }

  /** @see Exceptable::localize() */
  public static function localize(string $locale, ResourceBundle $messages) : void {
    static::$locale = $locale;
    static::$messages = $messages;
  }

  /**
   * @see https://php.net/class.Exception
   *
   * @var int    $code
   * @var string $file
   * @var int    $line
   * @var string $message
   */
  protected $code;
  protected $file;
  protected $line;
  protected $message;

  /** @var array Contextual information. */
  protected $context = [];

  /** @see Exceptable::__construct() */
  public function __construct(int $code = 0, array $context = [], Throwable $previous = null) {
    $this->context = $context;
    $this->context["__rootMessage__"] = $this->getRoot()->getMessage();

    // @phan-suppress-next-line PhanTraitParentReference
    parent::__construct($this->makeMessage($code), $code, $previous);
  }

  /** @see Exceptable::getContext() */
  public function getContext() : array {
    return $this->context;
  }

  /** @see Exceptable::getRoot() */
  public function getRoot() : Throwable {
    $root = $this;
    while (($previous = $root->getPrevious()) !== null) {
      $root = $previous;
    }

    return $root;
  }

  /**
   * Looks up a message format by key, if a messages bundle is available.
   *
   * @param string|null $key Dot-delimited path to desired key
   * @return string|null Message format on success; null otherwise
   */
  protected function getMessageFormat(?string $key) : ?string {
    if (! isset($key, static::$messages)) {
      return null;
    }

    $message = static::$messages;
    foreach (explode(".", $key) as $next) {
      if ($message instanceof ResourceBundle) {
        $message = $message->get($next);
        continue;
      }

      return null;
    }

    return is_scalar($message) ? (string) $message : null;
  }

  /**
   * Builds the exception message based on error code.
   *
   * @param int $code Error code
   * @return string
   */
  protected function makeMessage(int $code) : string {
    $info = static::getInfo($code);

    $format = $this->getMessageFormat($info["formatKey"]) ?? $info["format"];
    if (extension_loaded('intl')) {
      return MessageFormatter::formatMessage(static::$locale, $format, $this->context) ?:
        $info["message"];
    }

    return $this->substituteMessage($format) ?? $info["message"];
  }

  /**
   * Fallback message formatter, used if Intl is not installed.
   * Supports simple value substitution only.
   *
   * @param string $format Message format string
   * @return string|null Formatted message on success; null otherwise
   */
  protected function substituteMessage(string $format) : ?string {
    preg_match_all("(\{(\w+)\})u", $format, $matches);
    $placeholders = $matches[1];
    $replacements = [];
    foreach ($placeholders as $placeholder) {
      if (! isset($this->context[$placeholder]) || ! is_scalar($this->context[$placeholder])) {
        return null;
      }

      $replacements["{{$placeholder}}"] = $this->context[$placeholder];
    }

    return strtr($format, $replacements);
  }

  /** @see https://php.net/Throwable.getCode */
  abstract public function getCode() : int;

  /** @see https://php.net/Throwable.getFile */
  abstract public function getFile() : string;

  /** @see https://php.net/Throwable.getLine */
  abstract public function getLine() : int;

  /** @see https://php.net/Throwable.getMessage */
  abstract public function getMessage() : string;

  /** @see https://php.net/Throwable.getPrevious */
  abstract public function getPrevious() : Throwable;

  /** @see https://php.net/Throwable.getTrace */
  abstract public function getTrace() : array;

  /** @see https://php.net/Throwable.getTraceasString */
  abstract public function getTraceAsString() : string;
}
