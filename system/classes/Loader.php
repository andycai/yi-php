<?php defined('SYSPATH') or die('No direct access');
/**
 * Loader 类
 * 
 * @author Andy Cai (huayicai@gmail.com)
 *
 */

class Loader
{
	protected static $_instance;
	protected static $_dbs = array();

	protected $_hashMap = array();

	public static function getInstance()
	{
		if (self::$_instance == NULL) self::$_instance = new Loader();
		return self::$_instance;
	}

	private function parse_name($value)
	{
		$arr = explode('.', $value);
		foreach ($arr as $key => $value) {
			$arr[$key] = ucfirst($value);
		}
		$name = join('_', $arr);

		return $name;
	}

	public function model($className, $args=NULL)
	{
		if (empty($className)) return NULL;
		
		$className = 'Model_' . $this->parse_name($className);
		
		return $this->_getObject($className, $args);
	}

	public function service($className, $args=NULL)
	{
		if (empty($className)) return NULL;
		
		$className = 'Service_' . $this->parse_name($className);
		
		return $this->_getObject($className, $args);
	}

	public function controller($className, $args=NULL)
	{
		if (empty($className)) return NULL;

		$className = 'Controller_' . $this->parse_name($className);
		if ( !class_exists($className))
		{
			throw new Http_Exception('404', 'No page found');
			return;
		}

		return $this->_getObject($className, $args);
	}

	public function helper($file)
	{
		include(SYSPATH.'helper'.DS.ucfirst($file).'Helper'.EXT);
	}

	public function database($group_name = 'default')
	{
		$conf = Yi::$config->load('database');

		if ( !isset(self::$_dbs[$group_name]) || empty(self::$_dbs[$group_name]))
		{
			if ( !isset($conf[$group_name]) or empty($conf[$group_name]))
			{
				throw new Yi_Exception('database config group is empty: :var',
					array(':var' => $group_name));
			}
			else
			{
				$db = new medoo($conf[$group_name]);
				self::$_dbs[$group_name] = $db;
			}
		}

		return self::$_dbs[$group_name];
	}

	public function lib($file)
	{
		include_once(LIBPATH.$file);
	}

	/**
	 * 私有方法，路过请绕道
	 */
	
	private function _getObject($className, $args=NULL, $cached=TRUE)
	{
		if (TRUE === $cached)
		{
			if (is_array($this->_hashMap) && key_exists($className, $this->_hashMap))
			{
				return $this->_hashMap[$className];
			}
		}
		
		$object = NULL;
		if (empty($args))
		{
			try {
				$object = new $className();                                                                                                                                                          
			}
			catch (Exception $e)
			{
				throw new Yi_Exception($e->getMessage());
			}
		}
		else
		{
			$ref = new ReflectionClass($className);
			$object = $ref->newInstanceArgs($args); 	// php > 5.1.3
		}
	
		$this->_hashMap[$className] = $object;
	
		return $object;
	}
} // End Loader