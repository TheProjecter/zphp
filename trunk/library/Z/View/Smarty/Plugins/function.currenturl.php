<?php
// GenerateUrl($action = 'index', $controller = 'index', $module = 'default', $data = array())
function smarty_function_currenturl($params, &$smarty)
{
	$zcore = Z_Application::getInstance();

	$url = $zcore->generateUrl(null, null, null, $_GET);
	
	$assign = empty($params['assign']);
	
	if (!$assign) 
	{
		$smarty->assign($params['assign'], $url);
	} 
	else 
	{
		return $url;
	}
}
