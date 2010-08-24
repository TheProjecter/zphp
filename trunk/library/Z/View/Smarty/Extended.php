<?php 

class Z_View_Smarty_Extended extends Smarty
{
	
	public function __construct()
	{
		$this->error_reporting = E_ALL;		
		parent::__construct();
		$this->error_reporting = E_ALL;
		
		
		$this->register_compiler_function('publish', array($this, 'compiler_publishAsset'));
	}
	
	function compiler_publishAsset($tag_attrs, &$compiler)
	{
		$_params = $compiler->_parse_attrs($tag_attrs); 

	    if ((!isset($_params['this'])) && (!isset($_params['assets']))) 
	    { 
	        $compiler->_syntax_error("publish: missing 'this' or 'assets' parameter", E_USER_WARNING);
	         
	        return; 
	    } 
	
	    if (!isset($_params['this']))
	    {
	    	$_params['this'] = '""';
	    }
	    
	    if (!isset($_params['assets']))
	    {
	    	$_params['assets'] = '""';
	    }
	    
	   if (!isset($_params['ttl']))
	   {
	   		$_params['ttl'] = 0;
	   }
	   
	   $template_dir = $compiler->template_dir; 
	   $template_file = $compiler->_current_file; 
	
	
	   if (stripos(PHP_OS, 'win') === 0) { 
	      if (substr($template_file, 1,1)==':' 
	         || substr($template_file, 0,1)=='/' 
	         || substr($template_file, 0,1)=='\\') { 
	         $file_absolute = true; 
	      } 
	   } 
	   else if (substr( $template_file, 0, 1) == '/') { 
	      $file_absolute = true; 
	   } 
	
	   if (isset($file_absolute) && $file_absolute) { 
	      $path = $template_file; 
	   } 
	   else { 
	      $path = $template_dir.'/'.$template_file; 
	   } 
	
	   $dir = dirname(realpath($path)); 
	
	   $dir = str_replace("'", "\\'", $dir); 
	  
	    if (version_compare(phpversion(), "5.3.0", "<"))	
	   		return "echo Z_Asset::getInstance()->publish({$_params['this']}, null, {$_params['ttl']}, <<<EOT
{$dir}\
EOT
, {$_params['assets']} );";
		else
		{return "echo Z_Asset::getInstance()->publish({$_params['this']}, null, {$_params['ttl']}, <<<'EOT'
{$dir}\
EOT
, {$_params['assets']} );";			
		}
	}
	
	function getCurrentFile()
	{
		
	}
}