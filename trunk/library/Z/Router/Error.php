<?php

class Z_Router_Error extends Z_Router
{		
	public function __construct($routes = array())
	{
		parent::__construct($routes);
	}
	
	public function processError($errorcode, $request, $response, &$bProcessed)
	{		
		if (isset($this->_routes[$errorcode]))
			return array($this->_routes[$errorcode]);			
		
		return array();		
	}
	
	public function processRequest($request, $response, &$bProcessed)
	{		
		return array();		
	}		
	
	public function addRoute($errortype, $targetclass, $targetaction)
	{
		$this->_routes[$errortype]= array('class' => $targetclass, 'action' => $targetaction);
	}
	
	public function generateUrl($action = 'index', $controller = 'index', $module = 'default', $data = array())
	{		
		return false;
	}
	
	public function nosuchactionAction()
	{
		die('[Z] No such action');
	}
	public function nosuchcontrollerAction()
	{		
		die('[Z] No such controller');
	}
}