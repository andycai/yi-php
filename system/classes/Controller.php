<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 控制器基类
 * 
 * @author Andy Cai (huayicai@gmail.com)
 * 
 */
class Controller
{
	public function __construct()
	{
		$this->load = Loader::getInstance();
	}

	public function redirect($value)
	{
		$value = trim($value, "\/");

		$arr = explode('/', $value);
		$controller = 'home';
		$action = 'index';
		$len = count($arr);
		if ($len > 0)
		{
			$controller = array_shift($arr);
		}
		if ($len > 1)
		{
			$action = array_shift($arr);
		}
		
		$url = sprintf('?c=%s&m=%s', $controller, $action);
		
		while (count($arr) > 1)
		{
			$key = array_shift($arr);
			$value = array_shift($arr);
			$url .= sprintf('&%s=%s', $key, $value);
		}
		Http::redirect($url);
	}

	public function execute($action, $segments)
	{
		$this->before();

		$action = 'action_'.$action;

		if ( ! method_exists($this, $action))
		{
			throw new Http_Exception('404', 'No page found');
			return;
		}
		call_user_func_array(array($this, $action), array_slice($segments, 2));

		$this->after();
	}

	public function response($body)
	{
		Facade::getInstance()->response($body);
	}

	public function input($key, $default = NULL)
	{
		return Http::input($key, $default);
	}

	public function get($key, $default = NULL)
	{
		return Http::get($key, $default);
	}

	public function post($key, $default = NULL)
	{
		return Http::post($key, $default);
	}

	public function input_arr()
	{
		return Http::input_arr();
	}

	public function before()
	{
		// Nothing by default
	}

	public function after()
	{
		// Nothing by default
	}
	
} // End Controller
