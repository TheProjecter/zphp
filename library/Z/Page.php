<?php

class Z_Page
{
	protected $_request;
	protected $_response;
	protected $_view;
	
	public static $option_path = 'pages';
	
	public function __construct($request, $response, $view)
	{
		$this->_request = $request;
		$this->_response = $response;
		$this->_view = $view;
		
		$this->preInit();	
		$this->init();		
	}

	protected function init()
	{
		
	}
	
	/**
	 * Only override these for 'global' changes
	 */
	protected function preInit()
	{
		
	}
}