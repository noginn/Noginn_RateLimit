<?php

// Start output buffering
ob_start();

// Set the error reporting
error_reporting(E_ALL | E_STRICT);

// Set up the include paths
$rootPath = dirname(dirname(__FILE__));
$libraryPath = $rootPath . '/library';
$testsPath = $rootPath . '/tests';
set_include_path(implode(PATH_SEPARATOR, array($libraryPath, $testsPath, get_include_path())));

// Setup the autoloader
require_once $libraryPath . '/Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('Noginn_');

// Include the test configuration file
require_once $testsPath . '/TestConfiguration.php';

unset($rootPath, $libraryPath, $testsPath);