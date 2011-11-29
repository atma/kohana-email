<?php defined('SYSPATH') OR die('No direct access allowed.');
return array(
	/**
	 * SwiftMailer driver, used with the email module.
	 *
	 * Valid drivers are: native, sendmail, smtp
	 */
	'driver' => 'native',
	
	/**
	 * To use secure connections with SMTP, set "port" to 465 instead of 25.
	 * To enable TLS, set "encryption" to "tls".
	 * 
	 * Encryption can be one of 'ssl' or 'tls' (both require non-default PHP extensions
	 *
	 * Driver options:
	 * @param   null    native: no options
	 * @param   string  sendmail: executable path, with -bs or equivalent attached
	 * @param   array   smtp: hostname, (username), (password), (port), (encryption)
	 */
	'options' => NULL
);