<?php 

class Z_Page_Index extends Z_Page
{
	public function init()
	{			
		Z_Session::getInstance()->start();
	}
	
	public function indexAction()
	{		
		$this->view = new Z_View();		
			
		$this->view->output('index')->handled();
	}
	public function smartytestAction()
	{
		$this->view = new Z_View_Smarty();
						
		$this->view->output('test')->handled();		
	}
	
	public function jsonAction()
	{
		$data = array();
		$data[]['name'] = 'Tim';
		
		return new Z_Response_Writer_JSON($data);		
	}
}