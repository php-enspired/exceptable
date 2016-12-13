<?php
/**
 * @package    at.exceptable
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
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

use Exception;
use at\exceptable\api\Exceptable;

/**
 * base implementation for Exceptable interface.
 */
abstract class ExceptableException extends Exception implements Exceptable {

  /**
   * @const array INFO {
   *    @type array ${$code} {
   *      @type string $message     the exception message
   *      @type int    $severity    the exception severity
   *      @type string $tr_message  a translatable exception message with {}-delimited placeholders
   *      @type mixed  $...         implementation-specific additional info
   *    }
   *    ...
   *  }
   */

  /**
   * @type int    $_code      the exception code
   * @type string $_message   the exception message
   * @type int    $_severity  the exception severity
   * @type array  $_context   additional exception context
   */
  protected $_code;
  protected $_message;
  protected $_severity;
  protected $_context = [];

  /** @see Exceptable::get_info() */
  public static function get_info(int $code) : array {
    if (! static::has_info($code)) {
      $m = "no exception code [{$code}] is known";
      throw new \UnderflowException($m, E_USER_WARNING);
    }

    return static::INFO[$code] + [
      'code' => $code,
      'severity' => E_ERROR
    ];
  }

  /** @see Exceptable::has_info() */
  public static function has_info(int $code) : bool {
    return isset(static::INFO[$code]['message']);
  }

  /** @see Exceptable::__construct() */
  public function __construct(...$args) {
    if (is_array(end($args))) {
      $this->_context = array_pop($args);
    }
    $previous = (end($args) instanceof \Throwable) ? array_pop($args) : null;
    $this->_code = is_int(end($args)) ?
      array_pop($args) :
      $this->_makeCode();
    $this->_message = is_string(end($args)) ?
      array_pop($args) :
      $this->_makeMessage();
    $this->_severity = $this->_makeSeverity();

    // exceptional exceptable: bad args
    if (! empty($args)) {
      // what we could parse from the args becomes the new previous exception.
      $previous = new static($this->_message, $this->_code, $previous, $this->_context);
      $message = "arguments passed to Exceptable::__construct are invalid and/or out of order:\n"
        . json_encode($args, JSON_PRETTY_PRINT);
      throw new \RuntimeException($message, E_ERROR, $previous);
    }

    parent::__construct($this->_message, $this->_code, $previous);
  }

  /** @see <http://php.net/__toString> */
  public function __toString() {
    return parent::__toString()
      . "\ncontext: "
      . json_encode($this->getContext(), JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
  }

  /** @see Exceptable::getContext() */
  public function getContext() : array {
    return $this->_context;
  }

  /** @see Exceptable::getRoot() */
  public function getRoot() : \Throwable {
    $root = $this;
    while ($root->getPrevious() !== null) {
      $root = $root->getPrevious();
    }
    return $root;
  }

  /** @see Exceptable::getSeverity() */
  public function getSeverity() : int {
    return $this->_severity;
  }

  /**
   * generates a default exception code.
   *
   * @return int  an exception code
   */
  protected function _makeCode() : int {
    return ExceptableAPI::DEFAULT_CODE;
  }

  /**
   * generates a default exception severity.
   *
   * a severity must be one of E_ERROR|E_WARNING|E_NOTICE|E_DEPRECATED.
   * if no (valid) severity provided, falls back on:
   *  - severity from exception info
   *  - severity from previous exception
   *  - E_ERROR
   *
   * @return int  an exception severity
   */
  protected function _makeSeverity() {
    $severity = $this->_context['severity'] ??
      static::get_info($this->_code)['severity'] ??
      E_ERROR;

    return in_array($severity, [E_ERROR, E_WARNING, E_NOTICE, E_DEPRECATED]) ?
      $severity :
      E_ERROR;
  }

  /**
   * generates a default exception message.
   *
   * @return string  an exception message
   */
  protected function _makeMessage() : string {
    return $this->_makeTrMessage() ??
      static::get_info($this->_code)['message'] ??
      static::get_info(ExceptableAPI::DEFAULT_CODE)['message'] ??
      ExceptableAPI::DEFAULT_MESSAGE;
  }

  /**
   * generates a translated default exception message from context.
   *
   * @return string|null  a translated exception message on success; null otherwise
   */
  protected function _makeTrMessage() {
    $message = static::get_info($this->_code)['tr_message'] ?? null;
    if (! $message) {
      return null;
    }

    preg_match_all('(\{(\w+)\})', $message, $matches);
    $placeholders = $matches[1];
    $replacements = [];
    foreach ($placeholders as $placeholder) {
      if (! isset($this->_context[$placeholder])) {
        return null;
      }
      $replacements["{{$placeholder}}"] = $this->_context[$placeholder];
    }

    return strtr($message, $replacements);
  }
}
