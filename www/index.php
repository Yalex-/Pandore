<?php
/**
 * @file index.php.
 */

// Set the value of a configuration option.
ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);

// Defines header.
header('content-type: text/html; charset=utf-8');

// Set error handler.
set_error_handler(function($errno, $errstr, $errfile, $errline){ throw new \ErrorException($errstr, $errno, 0, $errfile, $errline); });

// Creates project name.
$projectName = $_SERVER['REQUEST_URI'];
$projectName = strpos($_SERVER['REQUEST_URI'], '/www') !== false ? substr( $_SERVER['SCRIPT_NAME'], 0, -13) : $projectName;

// Defines root path.
defined('ROOT_PATH') || define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'].$projectName);

// Includes and registers loader.
require_once(ROOT_PATH.'Kernel/Core/Loader.class.php');
spl_autoload_register(array('Kernel\Core\Loader', 'autoload'));

// Runs bootstrap.
$bootstrap = new Kernel\Core\Bootstrap();
$bootstrap->init();
$bootstrap->run();

?>