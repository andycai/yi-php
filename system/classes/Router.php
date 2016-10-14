<?php defined('SYSPATH') or die('No direct script access.');

class Router
{
	protected static $_instance;
	private $_rules = array();
	private $_uri = '';
	private $_segments = array();

	protected function __construct()
	{
	}

	public static function getInstance()
	{
		if (self::$_instance == NULL) self::$_instance = new Router();
		return self::$_instance;
	}

	private function parse_()
	{
		$this->_segments[0] = NULL;
		foreach (explode("/", trim($this->_uri, "/")) as $val)
		{
			$val = trim($val);
			if ($val !== "")
			{
				$this->_segments[] = $val;
			}
		}
		unset($this->_segments[0]);
	}

	public function add($route, $action)
	{
		$this->_rules[$route] = $action;
	}

	public function get()
	{
		$uri = "";
		// todo:实现正则支持
		if ( !empty($this->_rules[$this->_uri]))
		{
			$this->_uri = $this->_rules[$this->_uri];
		}
		else
		{
			if (isset($_SERVER['PATH_INFO']))
			{
				$this->_uri = $_SERVER['PATH_INFO'];
			}
			else
			{
				if (isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SCRIPT_NAME']))
				{
					$query_string = $_SERVER['QUERY_STRING'];
					$script_name = $_SERVER['SCRIPT_NAME'];
					$request_uri = $_SERVER['REQUEST_URI'];
					$uri = str_replace($script_name, '', $request_uri);
					$uri = str_replace($query_string, '', $uri);
					$uri = trim($uri, '?');
					$this->_uri = $uri;
				}
			}
		}
		$this->parse_();

		return $this->_segments;
	}
} // End Router
