<?php defined('SYSPATH') or die('No direct access');
/**
 * Config 处理类
 * 
 * @author Andy Cai (huayicai@gmail.com)
 *
 */

class Config
{
	protected $_directory = '';

	public function __construct($directory = 'config')
	{
		$this->_directory = trim($directory, '/');
	}

	public function load($group)
	{
		$config = array();

		if ($files = Yi::find_file($this->_directory, $group, NULL, TRUE))
		{
			foreach ($files as $file)
			{
				// Merge each file to the configuration array
				$config = Arr::merge($config, Yi::load($file));
			}
		}

		return $config;
	}

} // End Config