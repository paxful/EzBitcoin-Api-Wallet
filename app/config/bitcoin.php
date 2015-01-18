<?php

return array(
	'is_testnet' => $_ENV['TESTNET'],
	'callback_secret' => $_ENV['CALLBACK_SECRET'],
	'app_secret' => $_ENV['APP_SECRET'],
	'private_invoicing' => $_ENV['PRIVATE_INVOICING'],
);