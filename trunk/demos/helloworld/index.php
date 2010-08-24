<?php

/**
 * The Z framework
 * 
 * Hello World demo
 * 
 * index.php - Point Of Entry
 */

/**
 * Dev only obviously
 */
@error_reporting(E_ALL);
@ini_set('display_errors', 'on');

/**
 * Some application-specific defines
 */
define('DEMO_LIBRARY', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'library'));

/**
 * Require the Z framework. Make sure it is on the include path!
 * Otherwise unpack the Z framework in the library folder, and uncomment this
 */
@set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR));
 
/**
 * Include the Z core class (being simply 'Z')
 */
require_once '../../library/Z/Z.php';

/**
 * Create a new application instance, feeding in our configuration
 */
$application = Z::createApplication('application/config.inc.php');

/** 
 * Load our 'routing table' configuration and other misc options
 */
require_once 'application/misc.inc.php';

/**
 * Run it (Error handling not yet correctly done :/)
 */
try
{
	$application->run();
}
catch(Exception $ex)
{
	ob_end_clean();
	echo "An exception occured: " . $ex->getMessage() . "\r\nFile: " . $ex->getFile() . "\r\nLine: " . $ex->getLine() . "\r\nStacktrace: \r\n\r\n";
	echo $ex->getTraceAsString();
}