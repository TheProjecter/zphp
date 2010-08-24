<?php

class Z_Asset
{
	protected $_publishPath = '';
	protected $_sourcePath = '';
	protected $_options = 0;
	protected $_mode = 0777;
	protected $_webroot = '';
	
	public static $BIT_NONE = 0;
	public static $BIT_CHECK_SIZE = 1;
	public static $BIT_CHECK_DATETIME = 2;	
	
	protected static $_instance = null;
			
	/**
	 * 
	 * @param $publishPath 	Must include trailing slash if its a folder
	 * @param $sourcePath 	Must include trailing slash if its a folder
	 * @param $optionBits
	 * @param $mode			
	 * @param $setInstance	If true, it'll set the instance so you can reuse it using getInstance()
	 */
	public function __construct($publishPath, $sourcePath = '', $optionBits = 0, $mode = 0777, $webroot = '', $setInstance = true)
	{
		$this->_publishPath = $publishPath;
		$this->_sourcePath = $sourcePath;
		$this->_options = $optionBits;	
		$this->_mode = $mode;
		$this->_webroot = $webroot;
		
		self::$_instance = $this;
	}
	
	
	public static function getInstance($publishPath = '', $sourcePath = '', $optionBits = 0, $mode = 0777, $webroot = '')
	{
		if (self::$_instance == null)
		{
			self::$_instance = new Z_Asset($publishPath, $sourcePath, $optionBits, $mode, $webroot);
		}
		
		return self::$_instance;
	}
	
	public static function clearInstance()
	{
		self::$_instance = null;
	}
	
	protected function _publishThis($filename, $assetName, $ttl, $sourcePath)
	{
		if ($sourcePath == null)
			$sourcePath = $this->_sourcePath;
			
		if ($filename == null)
			throw new Z_Asset_Exception();
			
		// First construct the path		
		$filepath = realpath($sourcePath . $filename);
		
		// See if it exists
		if (!file_exists($filepath) && (!file_exists($sourcePath . $filename)))
		{
			throw new Z_Asset_Exception();
		}
		
		if ($ttl > 0)
		{
			throw new Z_Asset_Exception("Z_Asset::_publishThis()\r\n\t\$ttl is not yet implemented!");
		}
		
		// Generate the target path
		$targetpath = $this->_publishPath;
		
		// See if we need to generate an assetName (MD5 of file path)
		if ($assetName == null)
		{
			$assetName = md5($filepath) . '.' . Z_IO::getFileExtension($filepath);
		}
		
		// See if the target asset exists
		$targetpath .= $assetName;
		
		do if (@file_exists($targetpath))
		{
				// Check options
			
				// If asset already is published, and the option is set, recheck the timestamp
				if (self::$BIT_CHECK_DATETIME == ($this->_options & self::$BIT_CHECK_DATETIME))
				{
					if (filectime($targetpath) != filectime($filepath))
					{
						@unlink($targetpath);
						break;	
					}				
				}		
			
				// If asset already is published, and the option is set, recheck the file size
				if (self::$BIT_CHECK_SIZE == ($this->_options & self::$BIT_CHECK_SIZE))
				{
					if (filesize($targetpath) != filesize($filepath) )
					{
						@unlink($targetpath);
						break;
					}
				}	
				
			return $this->_webroot . $assetName;
		} while (false);
		
		// If we get here we need to copy the file
		
		// see if the folder exists
		$folderPath = @dirname($targetpath);
		
		// See if the folderPath exists or is a dir
		if (!file_exists($folderPath) || !is_dir($folderPath) )
		{
			// not exist - create
			$ok = @mkdir($folderPath, $this->_mode, true);
			
			if (!$ok)
				throw new Z_Asset_Exception();				
		}
		
		// then copy the file
		$ok = copy($filepath, $targetpath) && touch($targetpath, filemtime($filepath));
				
		if (!$ok)
			throw new Z_Asset_Exception();
			
		return $this->_webroot . $assetName;		
	}
	
	protected function _publishAssets($filename, $ttl, $sourcePath, $assetsPath)
	{
		if ($sourcePath == null)
			$sourcePath = $this->_sourcePath;
			
		if ($filename == null)
			throw new Z_Asset_Exception();
			
		// Get the assets path
		$filepath = realpath($sourcePath . $assetsPath);
					
		// Construct the p	
		// $filepath = realpath($sourcePath . $assetsPath . $filename);
		
		// See if it exists
		if (!file_exists($filepath) && (!file_exists($sourcePath . $assetsPath)))
		{
			throw new Z_Asset_Exception();
		}
		
		if ($ttl > 0)
		{
			throw new Z_Asset_Exception("Z_Asset::_publishAssets()\r\n\t\$ttl is not yet implemented!");
		}
		
		$targetpath = $this->_publishPath;
		
		// See if the target asset exists
		$targetpath .= $assetsPath;
		
		if (!file_exists($targetpath) || !is_dir($targetpath))
		{
			if (!@mkdir($targetpath))
				throw new Z_Asset_Exception();
		}
		$this->_copyFolder($sourcePath . $assetsPath,  $targetpath);
		
		return $this->_webroot . $assetsPath . $filename;		
	}
	
	protected function _copyFolder($source, $target)
	{
		$dirHandle=opendir($source); 
		
		if (!$dirHandle)
			throw new Z_Asset_Exception();
			
		while($file=@readdir($dirHandle)) 
        { 
        	if($file!="." && $file!="..")
        	{
        	if (@is_dir($source . Z_DS . $file))
        		{
        			if ((!@file_exists($target . Z_DS . $file)) || (!is_dir($target . Z_DS . $file)))
        			{
        				if (!@mkdir($target . Z_DS . $file))
        				{
							throw new Z_Asset_Exception("Z_Asset::_copyFolder() Could not create folder: " . $target . Z_DS . $file);        					
        				}
        			}
        			$this->_copyFolder($source . Z_DS . $file , $target . Z_DS . $file );
        			
        		}else if (@is_file($source . Z_DS . $file))
        		{
        			if ((!@file_exists($target . Z_DS . $file)) || (!is_file($target . Z_DS . $file)))
        			{
        				if (!@copy($source . Z_DS . $file, $target . Z_DS . $file))
        				{
							throw new Z_Asset_Exception("Z_Asset::_copyFolder() Could not create folder: " . $target . Z_DS . $file);        					
        				}
        			}else{
        				do if (@file_exists($target . Z_DS . $file))
						{
								// Check options
							
								// If asset already is published, and the option is set, recheck the timestamp
								if (self::$BIT_CHECK_DATETIME == ($this->_options & self::$BIT_CHECK_DATETIME))
								{
									if (filectime($target . Z_DS . $file) != filectime($source . Z_DS . $file))
									{
										@unlink($target . Z_DS . $file);										
										@copy($source . Z_DS . $file, $target . Z_DS . $file);
										
										break;	
									}				
								}		
							
								// If asset already is published, and the option is set, recheck the file size
								if (self::$BIT_CHECK_SIZE == ($this->_options & self::$BIT_CHECK_SIZE))
								{
									if (filesize($target . Z_DS . $file) != filesize($source . Z_DS . $file) )
									{
										@unlink($target . Z_DS . $file);
										@copy($source . Z_DS . $file, $target . Z_DS . $file);
										
										break;
									}
								}	
								
						} while (false);
        			}
        		}
        	}
        }
        
        @closedir($dirHandle);
	}
	/**
	 * 
	 * @param unknown_type $filename
	 * @param unknown_type $assetName The name to give to the asset, create folders for / entries
	 * @param unknown_type $ttl
	 */
	public function publish($filename, $assetName = null, $ttl = 0, $sourcePath = null, $assetsPath = null)
	{			
		if ($assetsPath == null)
			return $this->_publishThis($filename, $assetName, $ttl, $sourcePath);
		else
			return $this->_publishAssets($filename, $ttl, $sourcePath, $assetsPath);		
	}
	
	public function cleanUp()
	{
		throw new Z_Asset_Exception("Z_Asset::cleanUp() is not implemented yet!");
	}
}