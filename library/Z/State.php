<?php

class Z_State
{
	protected $_StateName;
	protected $_StateId;
	protected static $_States = array();
	protected static $_HandlerClass = 'Z_State';
	
	public static function GetInstance($stateName, $stateId='')
	{
		if (isset(self::$_States[$stateName . '@' . $stateId]))
			return self::$_States[$stateName . '@' . $stateId];
		
		$class = self::$_HandlerClass;	
		self::$_States[$stateName . '@' . $stateId] = new $class($stateName, $stateId);
		
		return self::$_States[$stateName . '@' . $stateId];
	}
	
	public static function SetHandlerClass($handlerClass)
	{
		self::$_HandlerClass = $handlerClass;	
	}
	public function __construct($stateName, $stateId)
	{
		$this->_StateName = $stateName;		
		$this->_StateId = $stateId;		
	}
	
	public function Reset()
	{
		Z_Session::Set('zStates_' . $this->_StateName . '@' . $this->_StateId, null);
	}
	
	public function Change($stateName, $stateId = null)
	{
		$this->_StateName = $stateName;
		$this->_StateId = $stateId;
	}
	
	public function __set($key, $value)
	{
		$data = Z_Session::get('zStates_' . $this->_StateName . '@' . $this->_StateId, array());
		if ($value === null)
		{
			if (isset($data[$key]))
				unset($data[$key]);	
				
			return;
		}else{
			$data[$key] = $value;
		}
		
		Z_Session::set('zStates_' . $this->_StateName . '@' . $this->_StateId, $data);
	}
	
	public function __get($key)
	{
		
		$data = Z_Session::get('zStates_' . $this->_StateName . '@' . $this->_StateId, array());
		
		if (isset($data[$key]))
			return $data[$key];
			
		return null;
	}
}