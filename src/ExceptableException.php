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

use Exception,
    Throwable;
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

  /** @type array  default exception information. */
  const DEFAULT_INFO = [
    'code' => Exceptable::DEFAULT_CODE,
    'message' => Exceptable::DEFAULT_MESSAGE,
    'severity' => Exceptable::DEFAULT_SEVERITY
  ];

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
      if ($code === Exceptable::DEFAULT_CODE) {
        return static::DEFAULT_INFO;
      }

      $m = "no exception code '{$code}' is known";
      throw new \UnderflowException($m, Exceptable::WARNING);
    }

    return static::INFO[$code] + [
      'code' => $code,
      'severity' => Exceptable::ERROR
    ];
  }

  /** @see Exceptable::has_info() */
  public static function has_info(int $code) : bool {
    return isset(static::INFO[$code]['message']);
  }

  /** @see Exceptable::__construct() */
  public function __construct(...$arguments) {
    $args = $arguments;
    $message = is_string(reset($args)) ? array_shift($args) : null;
    $code = is_int(reset($args)) ? array_shift($args) : null;
    $previous = (reset($args) instanceof Throwable) ? array_shift($args) : null;

    $this->_context = is_array(reset($args)) ? array_shift($args) : [];
    $code = $this->_makeCode($code);
    $message = $this->_makeMessage($message, $code);
    $this->_severity = $this->_makeSeverity($code);

    // exceptional exceptable: bad args
    if (! empty($args)) {
      $message = "arguments passed to Exceptable::__construct are invalid and/or out of order:\n"
        . json_encode($arguments, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
      throw new \RuntimeException($message, Exceptable::ERROR);
    }

    parent::__construct($message, $code, $previous);
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
  public function getRoot() : Throwable {
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

  /** @see Exceptable::isError() */
  public function isError() : bool {
    return $this->getSeverity() === Exceptable::ERROR;
  }

  /** @see Exceptable::isWarning() */
  public function isWarning() : bool {
    return $this->getSeverity() === Exceptable::WARNING;
  }

  /** @see Exceptable::isNotice() */
  public function isNotice() : bool {
    return $this->getSeverity() === Exceptable::NOTICE;
  }

  /**
   * generates a default exception code.
   *
   * @param int $code  code given on invocation
   * @return int       an exception code
   */
  protected function _makeCode(int $code=null) : int {
    if ($code === null) {
      return Exceptable::DEFAULT_CODE;
    }
    return static::get_info($code)['code'];
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
   * @param int $code  exception code
   * @return int       an exception severity
   */
  protected function _makeSeverity(int $code) {
    $severity = $this->_context['severity'] ??
      static::get_info($code)['severity'] ??
      static::get_info(Exceptable::DEFAULT_CODE)['severity'];

    $severities = [Exceptable::ERROR, Exceptable::WARNING, Exceptable::NOTICE];

    return in_array($severity, $severities, true) ?
      $severity :
      Exceptable::ERROR;
  }

  /**
   * generates a default exception message.
   *
   * @param string $message  message given on invocation
   * @param int $code        exception code
   * @return string          an exception message
   */
  protected function _makeMessage(string $message=null, int $code) : string {
    return $this->_makeTrMessage($message, $code) ??
      static::get_info($code)['message'] ??
      static::get_info(Exceptable::DEFAULT_CODE)['message'];
  }

  /**
   * generates a translated default exception message from context.
   *
   * @param string $message  message given on invocation
   * @param int $code        exception code
   * @return string|null     a translated exception message on success; null otherwise
   */
  protected function _makeTrMessage(string $message=null, int $code) {
    $message = $message ?? static::get_info($code)['tr_message'] ?? null;
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
