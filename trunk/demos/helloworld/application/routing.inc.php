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

$application->routers[] = $router;
