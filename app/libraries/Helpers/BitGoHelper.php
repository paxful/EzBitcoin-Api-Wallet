<?php namespace Helpers;

class BitGoHelper {

	public static function unlockWallet($bitcoinCore)
	{
		return $bitcoinCore->walletpassphrase(
			getenv('BITGO_PASSPHRASE'),
			300
		);
	}

	public static function lockWallet($bitcoinCore)
	{
		return $bitcoinCore->walletlock();
	}
}