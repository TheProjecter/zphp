<?php 

class Z_Page_Db extends Z_Page
{
	protected $_db;
	
	public function preInit()
	{
		$this->_db = Z_Database::getInstance();
		
		parent::preInit();
	}
	/**
	 * @return Z_Database 
	 */
	public function getDb()
	{
		return $this->_db;		
	}
}
