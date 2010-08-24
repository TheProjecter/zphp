<?php

class Z_Response
{
	protected $_redirect = false;
	protected $_output = '';
	protected $_request = null;
	protected $_headers = array();
	
	public function __construct($request)
	{
		$this->_request = $request;
	}
	
	public function redirect($url)
	{
		$this->_redirect = $url;	
		
		return $this;
	}
	
	public function header($header)
	{
		$this->_headers[] = $header;	
	}
	
	public function write($data)
	{
		$this->_output .= $data;
		
		return $this;
	}
	
	public function getBuffer()
	{
		return $this->_output;	
	}
	
	public function setRequest($request)
	{
		$this->_request = $request;	
	}
	
	public function clearBuffer()
	{
		$this->_output = '';
			
		return $this;
	}	
	
	public function output()
	{
		foreach ($this->_headers as $header)
		{
			@header($header);	
		}
		
		if ($this->_redirect)
		{
			@header('Location: ' . $this->_redirect);	
			
			return $this->_request;	
		}
		
		echo $this->_output;	
		
		return $this->_request;	
	}
}
