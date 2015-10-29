<?php namespace Helpers;

use ChangeAddress;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Settings;

class BitcoinHelper {

	const NEXT_CHANGE_ADDRESS_POS_KEY = 'next-change-address';

	public static function isValid($addr) {

		$version = Config::get('bitcoin.is_testnet');
		if ($version == true) {
			$version = "6F";
		} else {
			$version = "00";
		}

		if (preg_match('/[^1-9A-HJ-NP-Za-km-z]/', $addr)) {
			return false;
		}

		$decoded = self::decodeAddress($addr);

		if (strlen($decoded) != 50) {
			return false;
		}

		if (substr($decoded, 0, 2) != $version && substr($decoded, 0, 2) != "05") {
			return false;
		}

		$check = substr($decoded, 0, strlen($decoded) - 8);
		$check = pack("H*", $check);
		$check = hash("sha256", $check, true);
		$check = hash("sha256", $check);
		$check = strtoupper($check);
		$check = substr($check, 0, 8);

		return ($check == substr($decoded, strlen($decoded) - 8));
	}

	static protected function decodeAddress($data) {

		$charsetHex = '0123456789ABCDEF';
		$charsetB58 = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

		$raw = "0";
		for ($i = 0; $i < strlen($data); $i++) {
			$current = (string) strpos($charsetB58, $data[$i]);
			$raw = (string) bcmul($raw, "58", 0);
			$raw = (string) bcadd($raw, $current, 0);
		}

		$hex = "";
		while (bccomp($raw, 0) == 1) {
			$dv = (string) bcdiv($raw, "16", 0);
			$rem = (integer) bcmod($raw, "16");
			$raw = $dv;
			$hex = $hex . $charsetHex[$rem];
		}

		$withPadding = strrev($hex);
		for ($i = 0; $i < strlen($data) && $data[$i] == "1"; $i++) {
			$withPadding = "00" . $withPadding;
		}

		if (strlen($withPadding) % 2 != 0) {
			$withPadding = "0" . $withPadding;
		}

		return $withPadding;
	}

	public static function isMonitoringOutputsEnabled()
	{
		$monitor = Settings::getSettingValue('monitor_outputs');
		if ($monitor == 1)
		{
			return true;
		}
		return false;
	}

	public static function addOutputsToChangeAddresses($sendOutPairs, $userId)
	{
		// get change addresses and cache for 12h
		$changeAddresses = ChangeAddress::remember(720, 'change_addresses')->where('user_id', $userId)->get();
		if ($changeAddresses->count() < 1)
		{
			// no addresses, just return same address:amount pairs
			Log::warning('No change addresses found in DB!');
			return $sendOutPairs;
		}
		$position = Cache::rememberForever(self::NEXT_CHANGE_ADDRESS_POS_KEY, function()
		{
			return 0;
		});
		$takeNum = self::getOutputsToAdd();
		$addressesToFill = $changeAddresses->slice($position, $takeNum); // which addresses to fill with outputs

		// calculate next position where to start slicing on next outputs adding
		$nextPosition = $position + $takeNum;

		// if it took less than 125 addresses, take extra from beginning the remainder
		if ($addressesToFill->count() < $takeNum)
		{
			// check how much more needs to be taken to have 125 in total
			$remainder = $takeNum - $addressesToFill->count();
			$nextPosition = $remainder; // next position for more outputs is the remainder position where to start
			$slicedSecond = $changeAddresses->slice(0, $remainder); // take the remainder from beginning
			$addressesToFill = $addressesToFill->merge($slicedSecond);
		}

		Cache::forever(self::NEXT_CHANGE_ADDRESS_POS_KEY, $nextPosition); // save the next position

		$amountToAdd = self::getAmountToAdd();

		foreach ($addressesToFill as $address)
		{
			// add 0.069 btc to pairs
			$sendOutPairs->{$address->address} = $amountToAdd;
		}
		return $sendOutPairs;
	}

	public static function getOutputsThreshold()
	{
		$outputsThreshold = Settings::where('key', 'minimum_outputs_threshold')->first();
		return $outputsThreshold->value;
	}

	public static function getOutputsToAdd()
	{
		$outputsToAdd = Settings::where('key', 'outputs_to_add')->first();
		return $outputsToAdd->value;
	}

	public static function getAmountToAdd()
	{
		$amountToAdd = Settings::where('key', 'amount_to_add')->first();
		return $amountToAdd->value;
	}

	public static function getOutputsCacheDuration()
	{
		$outputsCacheDuration = Settings::where('key', 'outputs_cache_duration')->first();
		return $outputsCacheDuration->value;
	}
}