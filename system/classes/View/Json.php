<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Json 视图类
 * 
 * @author Andy Cai (huayicai@gmail.com)
 * 
 */
class View_Json
{
	private $_data;

	public static function factory($file = NULL, array $data = NULL)
	{
		return new View_Json($data);
	}

	public function __construct($file = NULL, array $data = NULL)
	{
		if ($data !== NULL)
		{
			$this->_data = $data;
		}
	}

	public function & __get($key)
	{
		if (array_key_exists($key, $this->_data))
		{
			return $this->_data[$key];
		}
		else
		{
			throw new Yi_Exception('View variable is not set: :var',
				array(':var' => $key));
		}
	}

	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	public function __isset($key)
	{
		return isset($this->_data[$key]);
	}

	public function __unset($key)
	{
		unset($this->_data[$key]);
	}

	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch (Exception $e)
		{
			// Display the exception message
			Yi_Exception::handler($e);

			return '';
		}
	}

	public function set($key, $value = NULL)
	{
		if (is_array($key))
		{
			foreach ($key as $name => $value)
			{
				$this->_data[$name] = $value;
			}
		}
		else
		{
			$this->_data[$key] = $value;
		}

		return $this;
	}

	public function bind($key, & $value)
	{
		$this->_data[$key] =& $value;

		return $this;
	}

	public function render($options = JSON_UNESCAPED_UNICODE)
	{
		return json_encode($this->_data, $options);
	}

} // End View_Json