<?php
// GenerateUrl($action = 'index', $controller = 'index', $module = 'default', $data = array())
function smarty_function_url($params, &$smarty)
{
	$zcore = Z_Application::getInstance();
	
	if (isset($params['controller']))
		$c = $params['controller'];
	else
		$c = 'index';
	
	if (isset($params['action']))
		$a = $params['action'];
	else
		$a = 'index';	
	
	if (isset($params['module']))
		$m = $params['module'];
	else
		$m = 'default';
		
	if (isset($params['data']))
		$data = $params['data'];
	else
		$data = array();
	
	$assign = empty($params['assign']);
	
	
	unset($params['assign']);
	unset($params['module']);
	unset($params['action']);
	unset($params['data']);
	unset($params['controller']);
	$url = $zcore->generateUrl($a, $c, $m, $params);
	

	if (!$assign) 
	{
		$smarty->assign($params['assign'], $url);
	} else {
		return $url;
	}
}
