<?php

class Settings extends Eloquent {

	protected $table = 'settings';

	protected $fillable = array('key', 'value');

	public static function getSettingValue($key)
	{
		$object = self::getSettingObject($key);
		if ($object)
		{
			return $object->value;
		}
		return null;
	}

	public static function updateVal($key, $value)
	{
		return self::where('key', $key)->update(array('value' => $value));
	}

	public static function getSettingObject($key)
	{
		return self::where('key', $key)->first();
	}

	public static function createSetting($key_name, $value)
	{
		return self::create([
			'key'   => $key_name,
			'value' => $value,
		]);
	}

}