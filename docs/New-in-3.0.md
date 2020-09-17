# Version 3.0 is Coming Soon!

## Requires PHP 7.3

PHP 7.2 is now end-of-life. You shouldn't be using it!

Note that this is a minimum requirement. [PHP 7.2 is now in security-only support and will be EOL on November 30, 2020](https://php.net/supported-versions). 

Version 2 will be supported until that time as well, in order to give existing users time to migrate their projects to PHP 7.3 (or, better yet - PHP 7.4). On December 1st, however, version 2.0 will become unsupported.

If you're still running PHP 7.2, it's time to upgrade!

## ICU Support

When the intl extension is installed, Exceptables now support ICU localization, message formatting, and resource bundles.

## Exceptable SPL Exceptions

Exceptable versions of the built-in SPL Exceptions are now available. 

These classes extend from their corresponding built-in Spl Exceptions, and can be used as-is (you'll get a generic message and code `1`), or extend them to build out your own error cases and take full advantage of their _Exceptable_ features.

## Debug Mode

Handlers now have a "debug" mode, which causes errors and exceptions to be collected and stored for later inspection.

## Logger Support

Handlers now implement [PSR-3](https://www.php-fig.org/psr/psr-3)'s `LoggerAware` interface, so you can provide a Logger implementation (e.g., [Monolog](https://packagist.org/packages/monolog/monolog)) and the Handler will manage logging errors/exceptions.