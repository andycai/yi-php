<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'default' => array(
		'database_type' => 'mysql',
		'database_name' => 'gamecenter',
		'server' => '127.0.0.1',
		'username' => 'gamecenter',
		'password' => 'gamecenter',
		'charset' => 'utf8',
	 
		// [optional]
		'port' => 3306,
	 
		// [optional] Table prefix
		'prefix' => 'tbl_',
	 
		// driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
		'option' => array(
			PDO::ATTR_CASE => PDO::CASE_NATURAL
		)
	),

	// redis
	'redis_master' => array(
		'hostname' => '127.0.0.1',
		'port' => 6379
	),
	
	'redis_slave' => array(
		'hostname' => '127.0.0.1',
		'port' => 6380
	),

	'memcache' => array(
		'hostname' => '127.0.0.1',
		'port' => 11211,
		'timeout' => 10,
		// 'compress' => MEMCACHE_COMPRESSED,
		'expire' => 300
	),
);
