<?php

class Z_Request implements ArrayAccess
{
	protected $_data = array();
	protected $_requestMethod = 'GET';
	protected $_handled = false;
	protected $_action = 'index';
	protected $_controller = 'index';
	protected $_module = 'default';
	
	public function __construct()
	{
		$this->_requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
			
		switch ($this->_requestMethod)
		{				
			case 'POST':
				$this->_data = array_merge($_GET, $_POST);					
				break;	
			case 'GET':
			default:
				$this->_data = array_merge($_POST, $_GET);				
				break;			
		}	
		if (isset($_GET[Z_Global::getSubkey('@Z.CONFIG', 'action_var', 'a')]))
		{
			$this->_action = $_GET[Z_Global::getSubkey('@Z.CONFIG', 'action_var', 'a')];			
		}else{
			$this->_action = 'index';	
		}
		
		if (isset($_GET[Z_Global::getSubkey('@Z.CONFIG', 'controller_var', 'c')]))
		{
			$this->_controller = $_GET[Z_Global::getSubkey('@Z.CONFIG', 'controller_var', 'c')];			
		}else{
			$this->_controller = 'index';	
		}
		
		if (isset($_GET[Z_Global::getSubkey('@Z.CONFIG', 'module_var', 'm')]))
		{
			$this->_module = $_GET[Z_Global::getSubkey('@Z.CONFIG', 'module_var', 'm')];			
		}else{
			$this->_module = 'default';	
		}
	}
	
	public function __get($key)
	{
		if (isset($this->_data[$key]))
			return $this->_data[$key];
			
		return null;	
	}
	
	public function __set($key, $value)
	{
		$this->_data[$key] = $value;
		return true;
	}
	
	public function getMethod()
	{
		return $this->_Method;	
	}
	
	public function getInput($key, $default = null)
	{
		if (!isset($this->_data[$key]))
			return $default;
		
		return $this->_data[$key];
	}	
	
	public function getAction()
	{
		return $this->_action;	
	}
	
	public function getController()
	{
		return $this->_controller;
	}
	
	
	public function getModule()
	{
		return $this->_module;
	}
	
	public function setController($controller)
	{
		$this->_controller = $controller;	
	}
	
	public function setAction($action)
	{
		$this->_action = $action;	
	}
	
	public function setModule($module)
	{
		$this->_module = $module;	
	}
	
	public function isHandled()
	{
		return $this->_handled;
	}
	
	public function handled()
	{
		$this->_handled = true;
	}
	
	public function offsetSet($varName, $varValue) 
	{		
		return $this->_data[$varName] = $varValue;
	}
	
	public function offsetExists($varName) 
	{
		if (isset($this->_data[$varName]))
			return true;
		
		return false;		
	}
	
	public function offsetUnset($varName) 
	{
		if (isset($this->_data[$varName]))
			unset($this->_data[$varName]);
	}
	
	public function offsetGet($offset) 
	{
		if (!isset($this->_data[$offset]))
			return null;
		
		return $this->_data[$offset];
	}	
}
