<?php
/**
 * Required defines
 */
define('Z_DS', DIRECTORY_SEPARATOR, false);
define('Z_PATH', realpath(dirname(__FILE__)). Z_DS);
define('Z_CONFIG', '@Z.Config');

require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Exception.php');
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Global.php');

class Z
{	
	public static $Autoloaders = array();
		
	public static $ROUTER_NO_SUCH_ACTION = 'ROUTER:NO SUCH ACTION';
	public static $ROUTER_NO_SUCH_CONTROLLER = 'ROUTER:NO SUCH CONTROLLER';
	
	public function __construct()
	{
		throw new Z_Exception('You cannot instantiate the Z class');
	}
	
	// @todo Remove Zend Framework requirement
	public static function registerAutoloader()
	{	
		/**
		 * Include the ezComponents bootstrap (for database, ...)
		 */
		require_once 'ezcomponents/Base/src/ezc_bootstrap.php';				

		/**
		 * Register our own autoloader
		 */
		spl_autoload_register('Z::__autoload');	
		
		/**
		 * Register the ezComponents autoloader
		 */
		spl_autoload_register('ezcBase::autoload');
				
		/**
		 * Register the Zend framework autoloader
		 */
		require_once 'Zend/Loader/Autoloader.php';
		
		$autoLoader = Zend_Loader_Autoloader::getInstance();
		$autoLoader->setFallbackAutoloader(true);			
	}
	
	public static function registerNamespace($namespace, $path)
	{	
		if (substr($path, -1) != '/')
			$path .= '/';
		
		$usingArray = Z_Global::get('@Z.USING_NAMESPACES', array());
		$usingArray[$namespace] = $path;	
		Z_Global::set('@Z.USING_NAMESPACES', $usingArray);			
	}

	public static function setGlobal($key, $value)
	{
		Z_Global::set($key, $value);		
	}
	

	public static function getGlobal($key)
	{
		return Z_Global::get($key);
	}
		
	public static function __autoload($class_name)
	{
		$class_name = str_replace('.', '', $class_name);
	
		$usingArray = Z_Global::get('@Z.USING', array());
		$ns = Z_Global::get('@Z.USING_NAMESPACES', array());
		
		$root = self::getRoot();
		
		foreach ($usingArray as $namespace => $name)
		{
			$space2 = $namespace;
			if (strpos($namespace, ':') !== false)
			{
				$namespace = explode(':', $namespace, 2);
				$space = $namespace[0];
				$space2 = $namespace[1];
				$namespace = implode(':', $namespace);	
				
				if (isset($ns[$space]))
				{
					$root = $ns[$space];
					$ospace = $space2;
				}		
			}		
			
			$path = $namespace;		
			$path = "$root/$space2/";
			
			$path = str_replace('\\', '/', $path);
			$path = str_replace('//', '/', $path);
			if ($name == '')
			{				
				if (file_exists($path.$class_name.".php"))
				{
					include_once $path.$class_name.".php";	
					return;
				}elseif (file_exists($path.strtolower($class_name).".php"))
				{
					include_once $path.strtolower($class_name).".php";	
					return;
				}elseif (file_exists($path.$class_name.".inc.php"))
				{
					include_once $path.$class_name.".inc.php";	
					return;
				}elseif (file_exists($path.strtolower($class_name).".inc.php"))
				{
					include_once $path.strtolower($class_name).".inc.php";	
					return;
				}elseif (file_exists($path.$class_name.".class.php"))
				{
					include_once $path.$class_name.".class.php";	
					return;
				}elseif (file_exists($path.strtolower($class_name).".class.php"))
				{
					include_once $path.strtolower($class_name).".class.php";	
					return;
				}elseif (file_exists($path.'class.'.$class_name.".php"))
				{
					include_once $path.'class.'.$class_name.".php";	
					return;
				}elseif (file_exists($path.'class.'.strtolower($class_name).".php"))
				{
					include_once $path.'class.'.strtolower($class_name).".php";	
					return;
				}
			}else{
				if (strtolower($name) == strtolower($class_name))
				{
					include_once $path;
					return;
				}	
			}	
		}
		
		$autoloaders = self::$Autoloaders;
		
		foreach ($autoloaders as $autoloader)
		{
			if (function_exists($autoloader))
			{
				$res = call_user_func($autoloader, $class_name);	
				if ($res)
					return;
			}	
		}		

		// If we get here, just try to include it right away.. (this will most likely fail without other autoloaders...)
		@include_once $class_name. '.php';
	}
	
	public static function getRoot()
	{
		return dirname(__FILE__);
	}
	
	public static function cloneObject($object)
	{
		return unserialize(serialize($object));	
	}
	
	public static function getPublicMethods($className)
	{
		if (is_object($className))
		$className = get_class($className);
	
		/* Init the return array */
		$returnArray = array();
		
		/* Iterate through each method in the class */
		foreach (get_class_methods($className) as $method) {
			
			/* Get a reflection object for the class method */
			$reflect = new ReflectionMethod($className, $method);
			
			/* For private, use isPrivate().  For protected, use isProtected() */
			/* See the Reflection API documentation for more definitions */
			if($reflect->isPublic()) {
				/* The method is one we're looking for, push it onto the return array */
				array_push($returnArray, $method);
			}
		}
		
		/* return the array to the caller */
		return $returnArray;
	}
	
	public static function using($namespace, $loadNow = true)
	{
		$usingArray = Z_Global::get('@Z.USING', array());
		$ns = Z_Global::get('@Z.USING_NAMESPACES', array());
		
		$root = dirname(__FILE__) . '/Z';
		
		$ospace = $namespace;
		
		if (strpos($namespace, ':') !== false)
		{
			$namespace = explode(':', $namespace, 2);
			$space = $namespace[0];
			$space2 = $namespace[1];
			$namespace = implode(':', $namespace);	
			
			if (isset($ns[$space]))
			{
				$root = $ns[$space];
				$ospace = $space2;
			}		
		}		
		
		if (substr($namespace, -1) == '*')
		{
			$namespace = str_replace('.', DIRECTORY_SEPARATOR, $namespace);
			$namespace = str_replace('*', '', $namespace);
			$usingArray[$namespace] = '';	
		}else{	
			$namespace = $ospace;		
			$namespace = str_replace('.', DIRECTORY_SEPARATOR, $namespace);
			$tmp = explode(DIRECTORY_SEPARATOR, $namespace);
			$namespace .= '.php';	
			$usingArray[$namespace] = $tmp[count($tmp)-1];	
			
			if ($loadNow)
			{
				include_once $root . $namespace;
			}	
		}
		
		Z_Global::set('@Z.USING', $usingArray);
	}
	
	/**
	 * 
	 * @param unknown_type $config
	 * @param unknown_type $class
	 * @return Z_Application
	 */
	public static function createApplication($config = NULL, $init = NULL, $class = 'Z_Application')
	{
		if ($config == null)
		{
			$config = Z::getGlobal(Z_CONFIG);
		}
				
		/*
		 * If $config is a string, a path is supplied to a php file that should
		 * return nothing more than an array containing all Z-specific settings
		 */
		if (is_string($config))
		{
			$config = include $config;
		}
		
		if (is_array($config))
		{
			$config = Z_Array::ToObject($config);
		}
		
		$config = self::_applyDefaultConfig($config);
		
		
		
		if (!class_exists($class))
		{
			throw new Z_Exception('Application class not found: ' . $class);
		}
		
		
		Z::setGlobal(Z_CONFIG, $config);			
		
		$app = new $class($config);
		
		if ($init != null)
		{	
			global $application;
			
			$application = $app;	
				
			include $init;
			
			$application = null;
		}
		
		return $app;
	}
	
	public static function getConfig($class = '')
	{		
		if ($class == '')
			return Z::getGlobal(Z_CONFIG);
			
		$config = Z::getGlobal(Z_CONFIG);
		if (!isset($config->$class))
		{
			$config->$class = new stdClass();
		}
		
		return $config->$class;
	}
	
	protected static function _applyDefaultConfig($config)
	{	
		if (!isset($config->Z_Application))
			$config->Z_Application = new stdClass();
			
		// Page options
		if (!isset($config->Z_Application->page))
		{
			$config->Z_Application->page = new stdClass();
		}
	
		if (!isset($config->Z_Application->page->class_prefix))
		{
			$config->Z_Application->page->class_prefix = 'Z_Page_';
		}		
	
		if (!isset($config->Z_Application->page->path))
		{
			$config->Z_Application->page->path = 'pages';
		}
	
		if (!isset($config->Z_View))
		{
			$config->Z_View = new stdClass();
		}
	
		if (!isset($config->Z_View->templates_dir))
		{
			$config->Z_View->templates_dir = 'views';
		}	
		
		if (!isset($config->Z_View->buffer_response))
		{
			$config->Z_View->buffer_response = false;
		}	
		
		/*
		if ((!isset($config['Database'])) || (!isset($config['Database']['default'])))
		{
			$config['Database'] = array();
			$config['Database']['default'] = array();
			$config['Database']['default']['username'] = 'root';
			$config['Database']['default']['password'] = '';
			$config['Database']['default']['host'] = '127.0.0.1';
			$config['Database']['default']['port'] = '3306';
			$config['Database']['default']['db_name'] = 'test';
		}
		*/
		return $config;
	}
}