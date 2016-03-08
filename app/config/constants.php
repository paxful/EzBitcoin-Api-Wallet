<?php

define('API_DEBUG', getenv('DEBUG'));
define('SATOSHIS_FRACTION', 100000000);

/* error messages */
define('AUTHENTICATION_FAIL', 'user authentication failed');
define('NO_USER', 'no user/guid found');
define('WRONG_PASSWD', 'wrong password');
define('NO_TX_ID', 'no tx id provided');
define('NO_ADDRESS', 'no address specified');
define('NO_CREATE_METHOD_ON_INVOICE', 'Incorrect method used for invoicing');
define('NO_FUNDS', 'insufficient funds');
define('INVALID_ADDRESS', 'invalid address specified');
define('ADDRESS_AMOUNT_NOT_SPECIFIED', 'address or amount not specified');
define('ADDRESS_AMOUNT_NOT_SPECIFIED_SEND_MANY', 'You must provide an address and amount');
define('SECRET_MISMATCH', 'secret mismatch');
define('NO_BLOCKHASH', 'no blockhash provided');

/* transaction types */
define('TX_SEND', 'sent');
define('TX_RECEIVE', 'received');
define('TX_RECEIVE_INVOICING', 'received-invoice');
define('TX_INVOICE', 'invoice');
define('TX_API_USER', 'api-user');
define('TX_UNREGISTERED_ADDRESS', 'unregistered address');