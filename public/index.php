<?php

define('ENVIROMENT', 'production');

switch (ENVIROMENT)
{
	case 'development':
		error_reporting(E_ALL | E_STRICT); // development enviroment
		ini_set('display_errors', 1);
	break;

	case 'testing':
	case 'production':
		ini_set('display_errors', 0);
		if (version_compare(PHP_VERSION, '5.3', '>='))
		{
				error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
		}
		else
		{
				error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
		}
	break;

	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'The application environment is not set correctly.';
		exit(1); // EXIT_ERROR
}

define('DS', DIRECTORY_SEPARATOR);
define('EXT', '.php');

$application = 'application';
$modules = 'modules';
$system = 'system';
$lib = 'lib';
$theme = 'theme';

define('DOCROOT', '/data//web/');
define('APPPATH', DOCROOT.$application.DS);
define('MODPATH', DOCROOT.$modules.DS);
define('SYSPATH', DOCROOT.$system.DS);
define('THEMEPATH', DOCROOT.$theme.DS);
define('LIBPATH', DOCROOT.$lib.DS);

unset($application, $modules, $system, $lib, $theme);

if (file_exists('install'.EXT))
{
	// Load the installation check
	return include 'install'.EXT;
}

require APPPATH.'bootstrap'.EXT;