<?php

return array(
	'is_testnet' => getenv('TESTNET'),
	'callback_secret' => getenv('CALLBACK_SECRET'),
	'app_secret' => getenv('APP_SECRET'),
	'private_invoicing' => getenv('PRIVATE_INVOICING'),
);