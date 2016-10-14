<?php defined('SYSPATH') or die('No direct script access.');

class App_Const
{
	// 服务器状态
	const STATUS_IDLE = 1;
	const STATUS_FULL = 2;
	const STATUS_CROWDED = 3;
	const STATUS_MAINTENANCE = 4;
	const STATUS_TESTING = 5;

} // End App_Const