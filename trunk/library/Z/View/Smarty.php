<?php

// Uses smarty
Z::using('Smarty:*');

class Z_View_Smarty extends Z_View
{
	protected $_smarty = null;

	private static $__optionsInfo 
		= array('template_dir' => 'The path to the folder containing the views (templates)',
				'compile_dir' => 'Usually a folder within the runtime folder',
				'cache_dir' => 'Usually a folder within the runtime folder',
		);
	
	public static function getOptionsInfo()
	{
		return self::$__optionsInfo;
	}
	
	public function __construct($viewData = array())
	{		
		parent::__construct($viewData);		
		
		// Get class-specific options
		$config = Z::getConfig(__CLASS__);
				
		$this->_smarty = new Z_View_Smarty_Extended();
		$this->_smarty->plugins_dir[] =  realpath(dirname(__FILE__). Z_DS . 'Smarty' . Z_DS . 'Plugins' . Z_DS);
		$this->_smarty->template_dir = $config->template_dir;
		$this->_smarty->compile_dir = $config->compile_dir;
		$this->_smarty->cache_dir = $config->cache_dir;
		
		// Expose assigned data as ViewData
		$this->_smarty->append_by_ref('ViewData', $this->_viewData);			
	}

	public function getOutput($template)
	{
		$template .= '.tpl';
		
		return $this->_smarty->fetch($template);
	}
	
	public function output($template)
	{
		$template .= '.tpl';
		
		$this->_response->write($this->_smarty->fetch($template));
		
		return $this->_request;
	}
	
	public function __set($key, $value)
	{
		$this->_smarty->clear_assign($key);
		$this->_smarty->assign($key, $value);	
	}
	
	public function __get($key)
	{
		return $this->_smarty->get_template_vars($key);
	}
}