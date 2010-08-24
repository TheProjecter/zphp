<?php 

class Z_Model
{
	public function __set($varName, $varValue)
	{
		$varName = strtolower("set" . $varName);
		$methods = Z::getPublicMethods($this);
		for($i=0;$i < count($methods);$i++)
		{
			$methods[$i] = strtolower($methods[$i]);	
		}
		
		if (in_array($varName, $methods))
		{
			return call_user_func(array($this, $varName), $varValue);
		}
		
		// see if there is a 'private' method
		if (method_exists($this, $varName))
		{			
			// See if we have full access
			$stack = debug_backtrace(true);
			if (count($stack) < 3)
				return null;
			
			$callerObj = $stack[2]['object'];
			
			if ($this == $callerObj)
			{
				return call_user_func(array($this, $varName), $varValue);
			}
		}
		
		return null;
	}
	
	public function &__get($var)
	{
		$varName = "get" . $var;
		$methods = Z::getPublicMethods($this);
		for($i=0;$i < count($methods);$i++)
		{
			$methods[$i] = strtolower($methods[$i]);	
		}
		
		// First check global access (public methods)
		if (in_array($varName, $methods))
		{
			return call_user_func(array($this, $varName));
		}
		
		// see if there is a 'private' method
		if (method_exists($this, $varName))
		{			
			// See if we have full access
			$stack = debug_backtrace(true);
			if (count($stack) < 3)
				return null;
			
			$callerObj = $stack[2];
			if ($this == $callerObj)
			{
				return call_user_func(array($this, $varName));
			}
		}
		
		$bla = array();
		
		return $bla;
	}
}