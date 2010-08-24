<?php

class Z_Global
{
	protected static $_dictionary = array();
	
	public function __set($key, $value)
	{
		self::set($key, $value);	
	}
	
	public function __get($key)
	{
		return self::Get($key);		
	}
	
	public static function set($key, $value)
	{
		self::$_dictionary[$key] = $value;	
	}
	
	public static function clear()
	{
		self::$_dictionary = array();	
	}
	
	public static function get($key, $default = null)
	{
		if (isset(self::$_dictionary[$key]))
			return self::$_dictionary[$key];
		else
			return $default;
	}		
	
	public static function getSubkey($key, $subKey, $default = null)
	{
		if ((isset(self::$_dictionary[$key])) && (isset(self::$_dictionary[$key][$subKey])))
			return self::$_dictionary[$key][$subKey];
		else
			return $default;
	}	
}
