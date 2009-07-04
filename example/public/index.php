<?php 

// Setup the environment
error_reporting(E_ALL | E_STRICT);

// Setup the paths
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('APPLICATION_PATH', ROOT_PATH . '/application');

// In this example, our library is not in the usual place
set_include_path('.' . PATH_SEPARATOR . dirname(ROOT_PATH) . '/library');

// Bootstrap the application
require_once 'Zend/Application.php';  
$application = new Zend_Application('development', array(
    'bootstrap' => array(
        'path' => APPLICATION_PATH . '/Bootstrap.php',
    ),
    'autoloadernamespaces' => array(
        'Zend', 
        'Noginn',
    ),
    'resources' => array(
        'frontController' => array(
            'controllerDirectory' => APPLICATION_PATH . '/controllers',
            'throwExceptions' => true,
        ),
        'view' => array(),
    ),
));

$application->bootstrap();
$application->run();
