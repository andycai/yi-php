<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Smarty 视图类
 * 
 * @author Andy Cai (huayicai@gmail.com)
 * 
 */
class View_Smarty
{
	const VIEW_EXT = '.tpl';

	protected $_smarty;
	protected static $_globalData = array();

	public static function factory($file = NULL, array $data = NULL)
	{
		return new View_Smarty($file, $data);
	}

	public static function set_global($key, $value = NULL)
	{
		if (is_array($key))
		{
			foreach ($key as $key2 => $value)
			{
				self::$_globalData[$key2] = $value;
			}
		}
		else
		{
			self::$_globalData[$key] = $value;
		}
	}
	public static function bind_global($key, & $value)
	{
		self::$_globalData[$key] =& $value;
	}
	
	// End 静态方法

	protected $_file;
	protected $_filename;
	protected $_data = array();

	public function __construct($file = NULL, array $data = NULL)
	{
		require_once(LIBPATH . 'smarty/Smarty.class.php');
		$this->_smarty = new Smarty();
		$this->_smarty->template_dir = Yi::themePath() . Config::get('smarty', 'template');
		$this->_smarty->compile_dir = Yi::themePath() . Config::get('smarty', 'template_c');
		$this->_smarty->compile_check = Config::get('smarty', 'complile_check'); 
		$this->_smarty->force_compile = Config::get('smarty', 'force_complile');
		$this->_smarty->left_delimiter = Config::get('smarty', 'left_delimiter');
		$this->_smarty->right_delimiter = Config::get('smarty', 'right_delimiter');
		$this->_smarty->debugging = Config::get('smarty', 'debugging');

		// $this->_smarty->setTemplateDir('/web/www.example.com/guestbook/templates/');
		// $this->_smarty->setCompileDir('/web/www.example.com/guestbook/templates_c/');
		// $this->_smarty->setConfigDir('/web/www.example.com/guestbook/configs/');
		// $this->_smarty->setCacheDir('/web/www.example.com/guestbook/cache/');
		// $this->_smarty->caching = true;
		// $this->_smarty->cache_lifetime = 120;

		if ($file !== NULL)
		{
			$this->setFilename($file);
		}

		if ($data !== NULL)
		{
			// Add the values to the current data
			$this->_data = $data + $this->_data;
		}
	}

	public function & __get($key)
	{
		if (array_key_exists($key, $this->_data))
		{
			return $this->_data[$key];
		}
		elseif (array_key_exists($key, self::$_globalData))
		{
			return self::$_globalData[$key];
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
		return (isset($this->_data[$key]) OR isset(self::$_globalData[$key]));
	}

	public function __unset($key)
	{
		unset($this->_data[$key], self::$_globalData[$key]);
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

	public function setFilename($file)
	{
		// $path = $this->_smarty->template_dir . DS . $file . self::VIEW_EXT;
		$path = $this->_smarty->getTemplateDir(0) . DS . $file . self::VIEW_EXT;
		
		if ( ! is_file($path) )
		{
			throw new Yi_Exception("The requested view $path could not be found");
		}

		// Store the file path locally
		$this->_filename = $file;
		$this->_file = $path;

		return $this;
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

	public function render($file = NULL)
	{
		if ($file !== NULL)
		{
			$this->setFilename($file);
		}

		if (empty($this->_file))
		{
			throw new Yi_Exception('You must set the file to use within your view before rendering');
		}


		// Combine local and global data and capture the output

		$this->_smarty->assign(self::$_globalData);
		$this->_smarty->assign($this->_data);

		return $this->_smarty->fetch($this->_filename . self::VIEW_EXT);
	}

} // End View_Smarty