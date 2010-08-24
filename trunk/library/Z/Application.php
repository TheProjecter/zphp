<?php 

class Z_Application
{
	protected $_config = null;
	
	public $request = null;
	public $response = null;
	public $routers = array();
	public $view = null;
	
	private static $__optionsInfo 
		= array('page.path' => 'The path to the folder containing the pages (controllers)',
				'page.class_prefix' => 'The prefix of the controller class'
		);
	
	public static function getOptionsInfo()
	{
		return self::$__optionsInfo;
	}
	
	public function addRouter($router)
	{
		$this->routers[] = $router;
	}
	
	public function __construct($config = null)
	{		
		$this->_config = Z::getConfig(__CLASS__);
		
		Z::setGlobal('@Z.Application', $this);	
		Z::using('Z:*');

		$this->init();
	}
	
	public function getConfig()
	{
		return $this->_config;
	}
	
	protected function init()
	{		
		$this->request = new Z_Request();
		$this->response = new Z_Response($this->request);
		$this->view = new Z_View();		
	}
/*
	public function option($key, $value = null)
	{
		if ($value == null)
		{
			return $this->_config[$key];
		}else{
			$this->_config[$key] = $value;
			
			return $value;
		}
	}	
*/
	/**
	 * @return Z_Application
	 */
	public static function getInstance()
	{
		if (Z::getGlobal('@Z.Application'))
			return Z::getGlobal('@Z.Application');
			
		Z::setGlobal('@Z.Application', Z::createApplication(NULL));
		
		return Z::getGlobal('@Z.Application');		
	}
	
	protected function _getPageInfo($controller, $module)
	{
		$path = $this->_config->page->path;
		$class = $this->_config->page->class_prefix;
			
		if ($module != 'default')
		{	
			$class .= $module . '_' . $controller;
			$module = str_replace('_', Z_DS, $module);	
			$path .= Z_DS . $module . Z_DS . $controller;
		}
		else
		{			
			$class .= $controller;
			$path .= Z_DS . ucfirst( $controller );
		}
		
		$path .= '.php';
		
		return array('class' => $class, 'path' => $path);
	}
	
	public function run()
	{
		// First see if any routes are triggered
		$bProcessed = false;
		
		//$calls = $this->GetRouter()->ProcessRequest($this->GetRequest(), $bProcessed);
		$calls = array();			
		$calls = $this->_processRequest($bProcessed);
		
		if ((count($calls) == 0) || (!$bProcessed))
		{
			// No routes found, generate them manually based on controller/action and optionally, module		
			$action = $this->request->getAction();
			$controller = $this->request->getController();
			
			/*$className = 'ctl' . ucfirst($controller);
			
			if ($this->request->GetModule() != 'default')
				$className = $this->request->GetModule() . '_' . $className;
			*/
			$info = $this->_getPageInfo($controller, $this->request->getModule());
			
			//require_once $info['path'];
			
			$calls[] = array('class' => $info['class'], 'action' => $action, 'path' => $info['path']);						
		}
		
		$error = false;
		
		for	($i=0;$i < count($calls);$i++)
		{
			if (!$this->request->isHandled())
			{
				$call = $calls[$i];
				
				$class = $call['class'];
				$action = $call['action'];
				
				$actionMethod = $action . "Action";
				
				if (isset($call['path']))
				{
					if (file_exists($call['path']))
					{
						require_once $call['path'];						
					}
					else
					{
						$error = Z::$ROUTER_NO_SUCH_CONTROLLER;
						break;							
					}
				}
				
				if (!class_exists($class, true))
				{
					$error = Z::$ROUTER_NO_SUCH_CONTROLLER;
					break;	
				}		
				
				$obj = new $class($this->request, $this->response, $this->view);
				if (!method_exists($obj, $actionMethod))
				{
					$error = Z::$ROUTER_NO_SUCH_ACTION;
					break;
				}
				
				if (!$this->request->isHandled())
				{
					$result = $obj->$actionMethod();
					
					if (isset($result) && ($result instanceof Z_Response_Writer))
					{
						$this->response->write($result->processData());
						$this->request->handled();
					}
				}
			}
		}
		
		if ($error)
		{
			$bProcessed = false;
		
			$calls = $this->_processError($error, $bProcessed);
			
			if (count($calls) == 0)
				die('Unhandled error: ' . $error);
			
			for	($i=0;$i < count($calls);$i++)
			{
				if (!$this->request->IsHandled())
				{
					$call = $calls[$i];
					
					$class = $call['class'];
					$action = $call['action'];
					
					$actionMethod = $action . "Action";
					
					$obj = new $class($this->request, $this->response, $this->view);
					if (!$this->request->isHandled())
						$obj->$actionMethod();
				}
			}
		}
		
		$this->response->output();
	}

	/**
	 * @return cvRouter 
	 */
	public function getRouters()
	{
		return $this->routers;	
	}
	
	/**
	 * @return zRequest 
	 */
	public function getRequest()
	{
		return @$this->request;	
	}
	
	public function setRequest($request)
	{
		$this->request = $request;	
		$this->response->setRequest($request);
		
	}
	public function &getResponse()
	{
		return $this->response;	
	}

	protected function _processRequest(&$bProcessed)
	{
		$calls = array();
		
		foreach ($this->routers as $router)
		{
			$tmp = array();
			
			$calls_new = $router->processRequest($this->request, $this->response, $bProcessed);	
			
			if ((is_array($calls_new))  && (count($calls_new) > 0))
			{				
				//$tmp[] = $calls_new;
				$calls = array_merge($calls_new, $calls);
			}
			if ($bProcessed)
				return $calls;
				
			//if ((count($calls) > 0) || ($bProcessed))
			//	return $calls;			
		}
		
		return $calls;
	}
	
	public function getWebRoot($add = '')
	{
		$sn = $_SERVER['SCRIPT_NAME'];
		
		$parts = explode('/', $sn);
		array_pop($parts);
		$sn = implode('/', $parts);
		
		return $sn . $add;
	}
	
	public function generateUrl($action = 'index', $controller = 'index', $module = 'default', $data = array())
	{		
		
		foreach ($this->routers as $router)
		{
			$url = $router->generateUrl($action , $controller, $module , $data);
			
			if ($url)
				return $url;		
		}
		
		$request_uri = 	$_SERVER['REQUEST_URI'];
		
		$tmp = explode('?', $request_uri, 2);
		$request_uri = $tmp[0];
		
		
		$main = $request_uri;
		
		//if (substr($main, -1) != '/')
		//	$main .= '
		if (substr($main, 0, 1) != '/')
			$main = "/$main";
			
		if ($action != 'index')
			$data[Z_Global::getSubkey('@Z.Config', 'action_var', 'a')]= $action;	
		
		if ($module != 'default')
			$data[Z_Global::getSubkey('@Z.Config', 'module_var', 'm')] = $module;
			
		if ($controller != 'index')
			$data[Z_Global::getSubkey('@Z.Config', 'controller_var', 'c')] = $controller;
		
		$cnt = 0;	
		foreach ($data as $key => $value)
		{
			if ($cnt == 0)
				$main .= '?';
			else
				$main .= '&';	
				
			$main .= urlencode($key). '=' . urlencode($value);
			
			$cnt++;
		}
		return $main;
	}
	
	protected function _processError($errormsg, &$bProcessed)
	{
		$calls = array();
		
		foreach ($this->routers as $router)
		{
			$calls = $router->processError($errormsg, $this->request, $this->response, $bProcessed);	
			
			if ((count($calls) > 0) || ($bProcessed))
				return $calls;			
		}
		
		return $calls;
	}
}