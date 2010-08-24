<?php 
/**
 * Get an instance of our application
 */
$application = Z_Application::getInstance();

/**
 * Basic router
 */
$router = new Z_Router();

$router->addRoute( '/:controller:', array());
$router->addRoute( '/:controller:/:action:', array());
$router->addRoute( '/:module:/:controller:/:action:', array());

$application->addRouter( $router );

/**
 * Configure Z_Asset (for usage of the publish smarty plugin)
 */
Z_Asset::getInstance(realpath(dirname(__FILE__) . '/../assets/') . Z_DS, realpath(dirname(__FILE__) . '/views/') . Z_DS, Z_Asset::$BIT_CHECK_DATETIME | Z_Asset::$BIT_CHECK_SIZE, 0755, $application->getWebRoot('/assets/') );

/**
 * Hack for GoDaddy PHP hosting
 */
// $_SERVER["REQUEST_URI"] = str_replace($_SERVER["SCRIPT_NAME"], "", $_SERVER["REQUEST_URI"]);