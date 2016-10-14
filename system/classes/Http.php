<?php defined('SYSPATH') or die('No direct access');
/**
 * Http 请求类
 * 
 * @author Andy Cai (huayicai@gmail.com)
 *
 */
class Http
{
	private static $error = '';
	private static $connect_timeout = 5;
	private static $request_timeout = 25;

	public static function curl_post($url, $data=array())
	{
		self::$error = '';
		if (is_array($data))
		{
			$data = http_build_query($data);
		}
		$ch = curl_init($url) ;
		curl_setopt($ch, CURLOPT_POST, 1) ;
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ;
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$connect_timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, self::$request_timeout);
		$result = @curl_exec($ch) ;
		self::$error = curl_error($ch);
		curl_close($ch);

		return $result;
	}

	public static function curl_get($url, $data = NULL)
	{
		self::$error = '';
		if (is_array($data))
		{
			$data = http_build_query($data);
			if(strpos($url, '?') === FALSE)
			{
				$url .= '?' . $data;
			}
			else
			{
				$url .= '&' . $data;
			}
		}
		$ch = curl_init($url) ;
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ;
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$connect_timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, self::$request_timeout);
		$result = @curl_exec($ch) ;
		self::$error = curl_error($ch);
		curl_close($ch);

		return $result;
	}

	public static function is_post()
	{
		return (strtolower ( $_SERVER ['REQUEST_METHOD'] ) == 'post');
	}

	public static function is_get()
	{
		return (strtolower ( $_SERVER ['REQUEST_METHOD'] ) == 'get');
	}

	public static function get($key, $default = NULL)
	{
		if (isset($_GET[$key]))
		{
			return trim($_GET[$key]);
		}
		return $default;
	}

	public static function post($key, $default = NULL)
	{
		if (isset($_POST[$key]))
		{
			return trim($_POST[$key]);
		}
		return $default;
	}

	public static function input($key, $default = NULL)
	{
		if (isset($_GET[$key]))
		{
			return trim($_GET[$key]);
		}
		else if (isset($_POST[$key]))
		{
			return trim($_POST[$key]);
		}
		return $default;
	}

	public static function input_arr()
	{
		return empty($_GET) ? $_POST : $_GET;
	}

	public static function redirect($uri = '', $method = 'location', $http_response_code = 302)
	{
		if ( ! preg_match('#^https?://#i', $uri))
		{
			$uri = Yi::baseUrl().$uri;
		}

		switch($method)
		{
			case 'refresh'	: header("Refresh:0;url=".$uri);
				break;
			default			: header("Location: ".$uri, TRUE, $http_response_code);
				break;
		}
		exit;
	}
} // End Http