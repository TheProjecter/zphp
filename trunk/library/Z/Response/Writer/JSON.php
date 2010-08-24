<?php 

class Z_Response_Writer_JSON extends Z_Response_Writer
{
	public function __construct($data)
	{
		parent::__construct($data);
	}
	
	public function processData()
	{
		return Z_JSON::encode($this->_data);
	}
}