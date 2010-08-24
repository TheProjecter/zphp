<?php 

class Z_Response_Writer
{
	protected $_data = null;
	
	public function __construct($data)
	{
		$this->_data = $data;	
	}
	
	public function processData()
	{
		return $this->_data;
	}
}