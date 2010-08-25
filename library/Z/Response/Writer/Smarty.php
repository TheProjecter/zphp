<?php 

class Z_Response_Writer_Smarty extends Z_Response_Writer
{
	public function __construct($template)
	{
		parent::__construct($template);
	}
	
	public function processData()
	{
		$view = new Z_View_Smarty();						
		
		return $view->getOutput($this->_data);
	}
}