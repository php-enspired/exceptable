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

use at\exceptable\Exceptable,
    at\exceptable\Exception;

/**
 * exceptableexceptionsexceptableexceptionsexceptableexceptions
 *
 * @method int       \Exception::getCode( void )
 * @method string    \Exception::getFile( void )
 * @method int       \Exception::getLine( void )
 * @method string    \Exception::getMessage( void )
 * @method Throwable \Exception::getPrevious( void )
 * @method array     \Exception::getTrace( void )
 * @method string    \Exception::getTraceAsString( void )
 *
 * @method array      Exception::get_info( int $code )
 * @method bool       Exception::has_info( int $code )
 * @method void       Exception::__construct( string|int|Throwable|array …$args )
 * @method string     Exception::__toString( void )
 * @method Exceptable Exception::addContext( array $context )
 * @method array      Exception::getContext( void )
 * @method Throwable  Exception::getRoot( void )
 * @method int        Exception::getSeverity( void )
 * @method bool       Exception::isError( void )
 * @method bool       Exception::isWarning( void )
 * @method bool       Exception::isNotice( void )
 * @method Exceptable Exception::setSeverity( int $severity )
 */
class ExceptableException extends Exception {

  /**
   * @type int NO_SUCH_CODE            invalid exception code.
   * @type int INVALID_CONSTRUCT_ARGS  invalid/out-of-order constructor arguments.
   */
  const NO_SUCH_CODE = 1;
  const INVALID_CONSTRUCT_ARGS = (1<<1);
  const INVALID_SEVERITY = (1<<2);

  /** @see Exceptable::INFO */
  const INFO = [
    self::NO_SUCH_CODE => [
      'message' => 'no such code',
      'severity' => Exceptable::WARNING,
      'tr_message' => "no exception code '{code}' is known"
    ],
    self::INVALID_CONSTRUCT_ARGS => [
      'message' => 'constructor arguments are invalid and/or out of order',
      'severity' => Exceptable::ERROR,
      'tr_message' => "constructor arguments are invalid and/or out of order: {args}"
    ],
    self::INVALID_SEVERITY => [
      'message' => 'invalid severity',
      'severity' => Exceptable::WARNING,
      'tr_message' =>
        'severity must be one of Exceptable::ERROR|WARNING|NOTICE; {severity} provided'
    ]
  ];
}
