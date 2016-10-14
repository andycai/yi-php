<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 逻辑服务提供类
 * 
 * @author Andy Cai (huayicai@gmail.com)
 *
 */
class Service
{
	protected $load;
	
	public function __construct()
	{
		$this->load = Loader::getInstance();
	}
} // End Service