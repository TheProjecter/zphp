<?php 

class Z_JSON
{
	public static function encode($data, $options = 0)
	{
		return json_encode($data, $options);
	}	

	public static function decode($data, $assoc = false, $depth = 512, $options = 0)
	{
		return json_decode($data, $assoc, $depth, $options);
	}
	
	public static function lastError()
	{
		return json_last_error();
	}
}