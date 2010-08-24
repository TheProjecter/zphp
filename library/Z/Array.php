<?php

class Z_Array implements ArrayAccess, Iterator 
{
	protected $__ZCoreArrayData = array();
	protected $__ZCoreArrayPosition = 0;

	
	public static function ToObject($array)
	{
		if(!is_array($array)) 
		{
			return $array;
		}
		
		$object = new stdClass();
		
		if (is_array($array) && count($array) > 0) 
		{
			foreach ($array as $name=>$value) 
			{
				$name = trim($name);
     			if (!empty($name)) 
     			{
        			$object->$name = self::ToObject($value);
     			}
  			}
	      	return $object;
		}
	    else 
	    {
	      return false;
	    }
	}
	
	function rewind() 
	{
		$this->__ZCoreArrayPosition = 0;
	}
	
	function current() 
	{ 
		return $this->__ZCoreArrayData[$this->__ZCoreArrayPosition];
	}
	
	function key() 
	{
		return $this->__ZCoreArrayPosition;
	}
	
	function next() 
	{
		++$this->__ZCoreArrayPosition;
	}
	
	function valid() 
	{
		return isset($this->__ZCoreArrayData[$this->__ZCoreArrayPosition]);
	}
	
	public function __construct($array = array())
	{
		$this->__ZCoreArrayData = $array;
	}		
	
	protected function setArrayData($data)
	{
		$this->__ZCoreArrayData = $data;
		$this->__ZCoreArrayPosition = 0;
	}
	
	public function offsetSet($varName, $varValue) 
	{		
		return $this->__ZCoreArrayData[$varName] = $varValue;
	}
	
	public function offsetExists($varName) {
		if (isset($this->__ZCoreArrayData[$varName]))
			return true;
			
		return false;		
	}
	
	public function offsetUnset($varName) {
		if (isset($this->__ZCoreArrayData[$varName]))
			unset($this->__Data[$varName]);
	}
	
	public function offsetGet($offset) 
	{
		if (!isset($this->__ZCoreArrayData[$offset]))
			return null;
			
		return $this->__ZCoreArrayData[$offset];
	}	
}