<?php
/**
 * @package    at.exceptable
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2018
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

/**
 * base implementation for Exceptable interface, including contexted message construction.
 *
 * this trait MUST be used by a class which implements Throwable.
 */
trait HasContext {

  /**
   * @type int    $_severity  the exception severity
   * @type array  $_context   additional exception context
   */
  protected $_severity;
  protected $_context = [];

  /** {@inheritDoc} */
  abstract public static function getInfo(int $code) : array;

  /** @see https://php.net/Throwable.getPrevious */
  abstract public function getPrevious();

  /** {@inheritDoc} */
  abstract public static function hasInfo(int $code) : bool;

  /** {@inheritDoc} */
  public function __construct(int $code, array $context = [], Throwable $previous = null) {
    $info = static::getInfo($code);

    $context['__rootMessage__'] = $previous ? $previous->getMessage() : '';
    $context['__severity__'] = $this->_severity = $info['severity'];
    $this->_context = $context;

    // @phan-suppress-next-line PhanTraitParentReference
    parent::__construct($this->_makeMessage($code) ?? $info['message'], $code, $previous);
  }

  /** @see https://php.net/__toString */
  public function __toString() {
    try {
      // @phan-suppress-next-line PhanTraitParentReference
      return parent::__toString() . "\ncontext: " . json_encode(
        $this->getContext(),
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
      );
    } catch (Throwable $e) {
      // @phan-suppress-next-line PhanTraitParentReference
      return parent::__toString();
    }
  }

  /** {@inheritDoc} */
  public function getContext() : array {
    return $this->_context;
  }

  /** {@inheritDoc} */
  public function getRoot() : Throwable {
    $root = $this;
    while (($previous = $root->getPrevious()) !== null) {
      $root = $previous;
    }
    return $root;
  }

  /** {@inheritDoc} */
  public function getSeverity() : int {
    return $this->_severity;
  }

  /**
   * generates an exception message using available context information.
   *
   * @param int $code     exception code
   * @return string|null  a translated exception message on success; null otherwise
   */
  protected function _makeMessage(int $code) : ?string {
    $message = static::getInfo($code)['contextMessage'] ?? null;
    if ($message === null) {
      return null;
    }

    $context = $this->getContext();

    preg_match_all('(\{(\w+)\})', $message, $matches);
    $placeholders = $matches[1];
    $replacements = [];
    foreach ($placeholders as $placeholder) {
      if (! isset($context[$placeholder])) {
        return null;
      }
      $replacement = is_scalar($context[$placeholder]) ?
        $context[$placeholder] :
        json_encode($context[$placeholder], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
      $replacements["{{$placeholder}}"] = $replacement;
    }

    return strtr($message, $replacements);
  }
}
