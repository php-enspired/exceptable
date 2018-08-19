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

use MessageFormatter,
  ResourceBundle,
  Throwable;

use at\exceptable\ {
  Exceptable,
  ExceptableException,
  HasContext
};

/**
 * implementation for Exceptable interface,
 * including Intl-based message formatting and translations.
 *
 * the Intl extension must be loaded for this trait to work properly;
 * otherwise it will fall back on HasContext behavior.
 *
 * exceptables using this trait will try to build the exception message as follows:
 *  - looks for an ICU message formatting string:
 *    - from a ResourceBundle, if available
 *    - from getInfo()[intlMessage], if exists
 *  - falls back to the parent (HasContext) message process otherwise
 *
 * the ResourceBundle should be structured like
 *  "exceptable" {
 *    <YourExceptableClassname> {
 *      <YourExceptableClassname::YOUR_ERROR_CODE> icu message pattern
 *    }
 *  }
 *
 * for more info on the message string,
 * @see https://php.net/MessageFormatter.formatMessage $pattern
 * @see https://www.sitepoint.com/localization-demystified-understanding-php-intl
 */
trait HasTranslations {
  use HasContext { HasContext::_makeMessage as _makeContextMessage; }

  /** @type string  desired locale. */
  protected static $_locale = 'en_US';

  /** @type ResourceBundle  bundle of ICU translation strings. */
  protected static $_rb;

  /**
   * sets the locale to use for translations.
   *
   * @param string $locale  locale identifier
   */
  protected static function setLocale(string $locale) {
    static::$_locale = $locale;
  }

  /**
   * sets the ResourceBundle for the implementing class to look for messages on.
   *
   * @param ResourceBundle $rb  resource bundle
   */
  public static function setResourceBundle(ResourceBundle $rb) {
    static::$_rb = $rb;
  }

  /**
   * gets the pattern for this exceptable's message.
   *
   * @param int $code  the exception code
   * @return string|null  an ICU message pattern on success; null otherwise
   */
  protected function _getMessagePattern(int $code) : ?string {
    try {
      $pattern = static::$_rb->get('exceptable')->get(static::class)->get($code);
    } catch (Throwable $e) {
      $pattern = null;
    }
    return $pattern ?? static::getInfo($code)['intlPattern'] ?? null;
  }

  /** {@inheritDoc} */
  protected function _makeMessage(int $code) : ?string {
    $pattern = $this->_getMessagePattern($code);
    if ($pattern === null) {
      return $this->_makeContextMessage($code);
    }

    return MessageFormatter::formatMessage(static::$_locale, $pattern, $this->getContext());
  }
}
