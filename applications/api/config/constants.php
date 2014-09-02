<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


/* bitcoind constants */
define("RETURN_OUTPUTTYPE", "json");

define("DEBUG_API", false);

define('NO_USER', 'no user/guid found');
define('WRONG_PASSWD', 'wrong password');
define('NO_TX_ID', 'no tx id');
define('NO_ADDRESS', 'no address specified');
define('NO_USER_ADDRESS', 'no address for user found');
define('INVALID_ADDRESS', 'invalid address specified');
define('ADDRESS_AMOUNT_NOT_SPECIFIED', 'address or amount not specified');
define('NO_TX_ID_PROVIDED', 'no tx id provided...');
define('NO_SECRET_FOR_CALLBACK', 'incorrect or no secret for callback provided');
define('TX_SEND', 'sent');
define('TX_RECEIVE', 'received');
define('TX_RECEIVE_INVOICING', 'received-invoice');
define('TX_INVOICE', 'invoice');
define('TX_API_USER', 'api-user');
define('NO_FUNDS', 'insufficient funds');
define('NO_CREATE_METHOD_ON_INVOICE', 'Incorrect method used for invoicing');
define('SATOSHIS_FRACTION', 100000000);
