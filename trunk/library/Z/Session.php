<?php

class Z_Session implements ArrayAccess
{
	public static $sessionHandler = null;	
	
	public static function get($key, $defaultValue = null)
	{
		if (self::$sessionHandler == null)
			self::$sessionHandler = new Z_Session();
			
		if (self::$sessionHandler->$key === null)
			return $defaultValue;	
		
		return self::$sessionHandler->$key ;
	}
	
	public static function set($key, $value)
	{
		if (self::$sessionHandler == null)
			self::$sessionHandler = new Z_Session();
			
		self::$sessionHandler->$key  = $value;
	}
	
	public static function setHandler($obj)
	{
		self::$sessionHandler = $obj;
	}
	
	public function __get($key)
	{
		if (!isset($_SESSION[$key]))
			return null;
			
		return $_SESSION[$key];	
	}
	
	public function __set($key, $value)
	{
		if ($value == null)
		{
			if (isset($_SESSION[$key]))
				unset($_SESSION[$key]);
				
			return;
		}
		
		$_SESSION[$key] = $value;	
	}
	
	public function offsetSet($varName, $varValue) 
	{		
		$this->$varName = $varValue;
	}
	
	public function offsetExists($varName) 
	{
		if ($this->$varName != null)
			return true;
		
		return false;		
	}
	
	public function offsetUnset($varName) 
	{
		$this->$varName = null;
	}
	
	public function offsetGet($offset) 
	{
		if ($this->$offset != null)
			return $this->$offset;
			
		return null;
	}	
	
	/***
	 * @return Z_Session
	 */
	public static function getInstance()
	{		
		if (self::$sessionHandler == null)
			self::$sessionHandler = new Z_Session();
			
		return self::$sessionHandler;	
	}
	
	public function start()
	{
		return session_start();
	}
	
	public function destroy()
	{
		$_SESSION = array();
		return session_destroy();
	}
	
	public function regenerate()
	{
		return session_regenerate_id(session_id());
	}
	
	public function GetSessionId()
	{
		return session_id();	
	}
}