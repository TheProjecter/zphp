<?php 

class Z_Model_Db extends Z_Model
{
	protected $_db;
	
	public function __construct()
	{
		$this->_db = Z_Database::getInstance();
	}
	
	/**
	 * @return Z_Database 
	 */
	public function getDb()
	{
		return $this->_db;		
	}
}
