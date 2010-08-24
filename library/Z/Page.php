<?php

class Z_Page
{
	public $request;
	public $response;
	public $view;
	
	public static $option_path = 'pages';
	
	public function __construct($request, $response, $view)
	{
		$this->request = $request;
		$this->response = $response;
		$this->view = $view;
		
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