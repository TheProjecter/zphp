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
		if (strpos($header, ':') === false)
			$this->_headers[] = $header;	
		else
		{
			$headerSection = explode(':', $header, 2); $headerSection = $headerSection[0];
				
			foreach ($this->_headers as $key => $value) 
			{
				if (strpos($value, ':') === false)
					continue;
					
				$section = explode(':', $value, 2); $section = $section[0];
				
				if ($section == $headerSection)
				{
					$this->_headers[$key] = $header;
					
					continue; /* @todo break? */
				}				
			}
		}
	}
	
	/**
	 * Sets required JSON header(s)
	 * 
	 * @param $forceNoCache If true it will also set required headers to disable caching
	 */
	public function forceJSON($forceNoCache = true)
	{
		if ($forceNoCache)
		{
			$this->header('Cache-Control: no-cache, must-revalidate');
			$this->header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		}
		
		$this->header('Content-type: application/json');
	}
	
	public function write($data)
	{
		if ($data instanceof Z_Response_Writer)
		{
			$data = $data->processData();
		}
		
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
