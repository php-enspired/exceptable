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

use at\exceptable\ {
  Exceptable,
  Exception
};

/**
 * exceptableexceptionsexceptableexceptionsexceptableexceptions
 */
class ExceptableException extends Exception {

  /**
   * @type int NO_SUCH_CODE        invalid exception code
   * @type int UNCAUGHT_EXCEPTION  uncaught/unhandled exception during runtime
   * @type int INVALID_HANDLER     invalid handler (e.g., wrong signature, or throws)
   */
  const NO_SUCH_CODE = 1;
  const UNCAUGHT_EXCEPTION = 2;
  const INVALID_HANDLER = 3;

  /** @see Exceptable::INFO */
  const INFO = [
    self::NO_SUCH_CODE => [
      'message' => 'no such code',
      'severity' => Exceptable::WARNING,
      'tr_message' => "no exception code '{code}' is known"
    ],
    self::UNCAUGHT_EXCEPTION => [
      'message' => 'uncaught exception',
      'severity' => Exceptable::ERROR,
      'tr_message' => 'no registered handler caught exception: {__rootMessage__}'
    ],
    self::INVALID_HANDLER => [
      'message' => 'invalid handler',
      'severity' => Exceptable::ERROR,
      'tr_message' => 'invalid handler [{type}]: {__rootMessage__}'
    ]
  ];
}
