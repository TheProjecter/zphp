<?php 

class Z_String
{
	public static function countOccurances($data, $countThis)
	{
		return strlen($data) - strlen(str_replace($countThis, substr($countThis, 0, strlen($countThis) - 1), $data));	
	}
}