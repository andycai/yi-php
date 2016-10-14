<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Session 帮助类
 *
 * @author Andy Cai (huayicai@gmail.com)
 * 
 */
class Session
{
	public static $salt = NULL;
	public static $expiration = 0;
	public static $path = '/';
	public static $domain = NULL;
	public static $secure = FALSE;
	public static $httponly = FALSE;
	
	public static function get($key, $default = NULL)
	{
		if ( ! isset($_SESSION[$key]))
		{
			// The session does not exist
			return $default;
		}

		// Get the session value
		$session = $_SESSION[$key];

		return $session;
	}

	public static function set($name, $val = NULL)
	{
		if (is_array($name))
		{
			foreach ($name as $key => $value) {
				$_SESSION[$key] = $value;
			}
		}
		else
		{
			$_SESSION[$name] = $val; 
		}
	}

	public static function destroy()
	{
		session_destroy();
	}
} // End Session