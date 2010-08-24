<?php

class Z_View
{
	protected $_viewData;	
	protected $_response;
	protected $_request;
	protected $_config = null;
	
	public function __construct($viewData = array())
	{		
		// Get class-specific options
		$this->_config = Z::getConfig(__CLASS__);
		
		$this->_viewData = $viewData;
		
		$core = Z_Application::getInstance();
		
		$this->_response = &$core->getResponse();
		$this->_request = &$core->getRequest();
	}
	
	public function output($template = '')
	{
		$Data = $this->_viewData;
		
		if (!$this->_config->buffer_response)
		{
			$this->_response->output(); // flushes output buffer and sends headers!
			
			include $this->_config->template_dir . Z_DS . $template . '.tpl.php';	
			
			return $this->_request;
		}
		
		// buffer the response
		ob_start();
		include $this->_config->template_dir . Z_DS . $template . '.tpl.php';
		$out = ob_get_clean();
		
		$this->_response->write($out);
		
		return $this->_request;	
	}
}