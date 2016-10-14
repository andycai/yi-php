<?php defined('SYSPATH') or die('No direct script access.');

//session_start ();

if ( ! defined('YI_START_TIME'))
{
	define('YI_START_TIME', microtime(TRUE));
}

if ( ! defined('YI_START_MEMORY'))
{
	define('YI_START_MEMORY', memory_get_usage());
}

require(SYSPATH.'classes/Yi'.EXT);

date_default_timezone_set('Asia/Shanghai');

spl_autoload_register(array('Yi', 'auto_load'));
ini_set('unserialize_callback_func', 'spl_autoload_call');

Yi::init(array(
	'errors' => TRUE,
	'baseUrl' => '/',
	'indexFile' => FALSE,
	'caching' => Yi::$environment === Yi::PRODUCTION,
	'lang' => 'zh_cn',
	// 'profile' => Yi::$environment !== Yi::PRODUCTION,
	// 'theme' => 'default',
	// 'template' => 'View', // View, View_Smarty
	// 'cache_dir' => '/data/game/cache',
));

Yi::modules(array(
	'database'      => MODPATH.'database',      // database
	// 'cache'      => MODPATH.'cache',      // Caching with multiple backends
	// 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
));

Yi::app()->runApp();