<?php 

class Z_Object
{
	public static function ToArray($object) 
	{
		if (is_object($object)) 
		{
			// Gets the properties of the given object
			// with get_object_vars function
			$object = get_object_vars($object);
		}
 
		if (is_array($object)) 
		{
			return array_map(array('Z_Object', 'ToArray'), $object);
		}
		else 
		{
			return $object;
		}
	}
 
}