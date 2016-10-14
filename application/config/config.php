<?php defined('SYSPATH') or die('No direct script access.');

return array
(
	// smarty模板
	'smarty'  => array(
		'template' => 'template',
		'template_c' => 'template_c',
		'complile_check' => TRUE,
		'force_complile' => TRUE,
		'left_delimiter' => '<{',
		'right_delimiter' => '}>',
		'debugging' => TRUE,
	),

	'autoload' => array(
		'database'
	),

); // End config
