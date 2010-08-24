<?php 

/**
 * config.inc.php - Application specific settings
 * 
 * Must return an array or object (stdclass) so Z can pick it up
 * 
 * All options are staticly stored on their 'logical' classes
 * 
 * Example 'Application' in Z_Application, ...
 */

define('DEMO_APPLICATION', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR ));

// We'll show 'both' as an example
$config = array();

// For application options
$config['Z_Application'] = array();
$config['Z_Application']['page']['path'] = realpath(dirname(__FILE__) . Z_DS . 'pages');
$config['Z_Application']['page']['class_prefix'] = 'Z_Page_';

// For default view options
$config['Z_View'] = array();
$config['Z_View']['buffer_response'] = true; // if true, it'll buffer the output
$config['Z_View']['template_dir'] = realpath(dirname(__FILE__) . '/views');

// For Smarty view' options
$config['Z_View_Smarty'] = array();
$config['Z_View_Smarty']['template_dir'] = realpath(dirname(__FILE__) . '/views');
$config['Z_View_Smarty']['compile_dir'] = realpath(dirname(__FILE__) . '/runtime/views');
$config['Z_View_Smarty']['cache_dir'] = realpath(dirname(__FILE__) . '/runtime/views/cached');

/**
 * Configuration is done - preboot Z (you could do this usually before configuring though)
 */

/**
 * Register the auto loader
 */
Z::registerAutoloader();

/**
 * Register 'namespaces' (to basicly make it easier to include files 
 */
Z::registerNamespace('Library', DEMO_LIBRARY);

/**
 * Register 'namespaces' (to basicly make it easier to include files 
 */
Z::registerNamespace('Smarty', '../../library/smarty/');


// Now we show that it does accept stdclass objects
return Z_Array::ToObject($config);