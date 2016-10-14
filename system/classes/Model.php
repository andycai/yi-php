<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 数据模型类
 * 
 * @author Andy Cai (huayicai@gmail.com)
 *
 */
class Model
{
	protected $db;
	protected $load;
	
	public function __construct()
	{
		$this->load = Loader::getInstance();
		$this->db = $this->load->database();
	}

	public function db($group_name = NULL)
	{
		return $this->load->database($group_name);
	}
} // End Model