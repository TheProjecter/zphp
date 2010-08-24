<?php

class Z_Router
{	
	protected $_routes = array();
	
	public function __construct($routes = array())
	{
		$this->_routes = $routes;		
	}
	
	public function processError($errorcode, $request, $response, &$bProcessed)
	{		
		$calls = array();
		
		switch ($errorcode)
		{
			case Z::$ROUTER_NO_SUCH_ACTION:
				$calls[] = array('class' => 'Z_Router_Error', 'action' => 'nosuchaction');
				$bProcessed = true;
				break;
				
			case Z::$ROUTER_NO_SUCH_CONTROLLER:		
				$calls[] = array('class' => 'Z_Router_Error', 'action' => 'nosuchcontroller');
				$bProcessed = true;
				break;	
		}		
		
		return $calls;		
	}
	
	public function processRequest($request, $response, &$bProcessed)
	{
		$request_uri = 	$_SERVER['REQUEST_URI'];
		$script_name = $_SERVER['SCRIPT_NAME'];
		
		$tmp = explode('?', $request_uri, 2);
		$request_uri = $tmp[0];
		
		if (substr($request_uri, -1) == '/')
			$request_uri = substr($request_uri, 0, strlen($request_uri) - 1);
			
		$parts = explode('/', $request_uri);			
		
		$cntx = Z_String::countOccurances($script_name, '/');
		
		for ($i=0;$i < $cntx;$i++)
		{
			array_shift($parts);					
		}
				
		if (substr($request_uri,0, strlen($script_name)) == $script_name)
			return false;
			
		foreach ($this->_routes as $route)
		{
			$break = false;
			
			$mvc = array();			
			
			if (is_array($route))
			{
				$mvc = $route[1];	
				$route = $route[0];			
			}
			
			if (is_string($route))
			{
				$route_parts = explode('/', $route);
				array_shift($route_parts);
				$match = false;
				$cnt = 0;
				$varNames = array();
				$varValues = array();	
				
				if ($route == '')
					$match = true;
					
				if (count($route_parts) > count($parts))
					$match  = false;
				else{
					foreach ($route_parts as $part)
					{						
						if (((substr($part, 0, 1) == ':') && (substr($part, -1) == ':')) && (Z_String::countOccurances($part, ':') == 2)) 	
						{
							$match = true;
							$varNames[] = substr($part, 1, strlen($part) -2);
							$varValues[] = $parts[$cnt];						
						}elseif ($part ==  $parts[$cnt])
						{
							$match = true;	
						}else{
							$match = false;	
						}
						
						if (!$match)
						{
							$rpart = $part;
							$varStart = count($varValues);
							$tmp = explode(':', $part);
							$skip = true;
							foreach ($tmp as $px)
							{
								if ($skip)
								{
									$skip = false;
									continue;	
								}else{
									$skip = true;	
								}
								$varNames[] = $px;
								$rpart = str_replace(":$px:", "((?:[a-z0-9][a-z0-9]*))", $rpart);
							}
							
							// See if it is preg match
							if ($c=preg_match_all ("/".$rpart."/is", $parts[$cnt], $matches))
							{
								for ($i=0; $i < count($matches);$i++)
								{
									if ($i == 0)
										continue;
										
									$varValues[$varStart + $i - 1] = $matches[$i][0];
								}	
								$match = true;
								$cnt++;
								continue;
								
							}	
							break;
						}
						$cnt++;					
					}
				}
				if ($match)
				{
					for ($x = 0;$x < count($varNames);$x++)
					{
						$varName = str_replace(':', '', $varNames[$x]);
						$varValue = $varValues[$x];
						
						
						switch ($varName)
						{
							case 'action':
								if ($varValue == '')
									continue;
								$request->SetAction($varValue);
								break;
							case 'controller':
								if ($varValue == '')
									continue;
								$request->SetController($varValue);							
								break;
							case 'module':
								if ($varValue == '')
									continue;
								$request->SetModule($varValue);				
								break;
							default:
								$request->$varName = $varValue;
								break;	
						}	
						
						
					}	
					
					foreach ($mvc as $varName => $varValue)
					{
						switch ($varName)
						{
							case 'action':
								if ($varValue == '')
									continue;
								$request->SetAction($varValue);
								break;
							case 'controller':
								if ($varValue == '')
									continue;
								$request->SetController($varValue);							
								break;
							case 'module':
								if ($varValue == '')
									continue;
								$request->SetModule($varValue);				
								break;
						}						
					}
				}	
			}						
		}
		
		return array();		
	}	
	
	
	public function addRoute($match, $extra)
	{
		$this->_routes[] = array($match, $extra);
	}
	
	public function generateUrl($action = 'index', $controller = 'index', $module = 'default', $data = array())
	{		
		if ($data == array())
		{
			$data[''] = '';	
		}
		
		$application = Z_Application::getInstance();
		
		$request = $application->GetRequest();
		
		if ($action == null)
			$action = $request->GetAction();
			
		if ($controller == null)
			$controller = $request->GetController();
			
		if ($module == null)
			$module = $request->GetModule();
			
		$url = false;	
		$application = array('action', 'controller', 'module');
		$coredata = array($action, $controller, $module);

		foreach ($this->_routes as $route)
		{
			$url = false;
			
			if (is_array($route))
			{
				$mvc = $route[1];
				$route = $route[0];	
			}else{
				$mvc = array();
				$mvc['module'] = 'default';
			}
			if (!isset($mvc['module']))
				$mvc['module'] = 'default';
			
			$data = array_merge($mvc, $data);
			
			$troute = $route;
			$cnt = 0;
			foreach ($application as $cpart)
			{
				$route = $troute;
				
				$dat = $coredata[$cnt];
				$cnt++;
				$troute = str_replace(":$cpart:" , $dat, $troute);
				if	(($troute == $route) && ((!isset($mvc[$cpart])) || ($mvc[$cpart] != $dat)))
				{
					$urlx = false;
					break;			
				}	
				
				$urlx = $troute;				
			}
			
			if ($urlx == false)
				continue;
				
			$index = 0;
			foreach ($data as $k => $v)
			{
				$index++;
				$route = $troute;
				//if (in_array($k,array('controller', 'action', 'module')))
				//	continue;
				$routeOld = $troute;
				$troute = str_replace(":$k:" , $v, $troute);
				
				if (count($mvc) != 3)
				{
					if	((($troute == $route) && (!isset($mvc[$k]))) || ((isset($mvc[$k]) && ($mvc[$k] != $v))))
					{	
						$url = $troute;	
						break;		
					}
				}else{
					
					if ($routeOld == $troute)
						continue;
						
						
					if (($troute == $route) && (!isset($mvc[$k])))
					{
						$url = $troute;	
						unset($data[$k]);
						break;					
					}
					
					if ($index == count($data))
					{
						$url = $troute;
						unset($data[$k]);
						break;
					}
				}
				/*if ($index == count($data))
				{
					$url = $troute;
					die($troute);
				break;
				}*/
				/*if	((($troute == $route) && (!isset($mvc[$k]))) || ((isset($mvc[$k]) && ($mvc[$k] != $v))))
				{		
				}*/
					
				$url = false;
				
				
			}
				
			if ($url)
			{
				$pref = $_SERVER['SCRIPT_NAME'];				
				$pref = dirname($pref);
				$pref = str_replace('\\', '/', $pref);
				if ($pref == '/')
					$pref = '';
					
				$url = $pref . $url;
			}
			
			$url .= '?'	;
			foreach ($data as $k => $v)
			{	
				if (in_array($k, array('controller', 'action', 'module')))
					continue;
				$url .= "$k=" . urlencode($v)  .'&';
			}
			
			if (substr($url, -1) == '&')
			{
				$url = substr($url, 0, strlen($url) -1);	
			}
			return $url;		
		}
		
		$pref = $_SERVER['SCRIPT_NAME'];
		
		$pref = dirname($pref);
		
		if ($url)
			$url = $pref . $url;
		
		$url .= '?'	;
		foreach ($data as $k => $v)
		{	
			if (in_array($k, array('controller', 'action', 'module')))
				continue;
				
			$url .= "$k=" . urlencode($v)  .'&';
		}
		
		if (substr($url, -1) == '&')
		{
			$url = substr($url, 0, strlen($url) -1);	
		}		
		
		return $url;
	}
}