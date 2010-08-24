<?php

class Z_Asset_Exception extends Z_Exception
{
	public function __construct ($message = '', $code = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}	
}