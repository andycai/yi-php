<?php defined('SYSPATH') or die('No direct script access.');

if (FALSE === function_exists('valid_ip'))
{
	/**
	 * Validate IP Address
	 *
	 * @param	string	$ip	IP address
	 * @param	string	$which	IP protocol: 'ipv4' or 'ipv6'
	 * @return	bool
	 */
	function valid_ip($ip, $which = '')
	{
		switch (strtolower($which))
		{
			case 'ipv4':
				$which = FILTER_FLAG_IPV4;
				break;
			case 'ipv6':
				$which = FILTER_FLAG_IPV6;
				break;
			default:
				$which = NULL;
				break;
		}

		return (bool) filter_var($ip, FILTER_VALIDATE_IP, $which);
	}
}

// 获取客户端IP
if (FALSE === function_exists('get_ip'))
{
	function get_ip()
	{
		if (! empty ( $_SERVER ["HTTP_CLIENT_IP"] ))
			$cip = $_SERVER ["HTTP_CLIENT_IP"];
		else if (! empty ( $_SERVER ["HTTP_X_FORWARDED_FOR"] ))
			$cip = $_SERVER ["HTTP_X_FORWARDED_FOR"];
		else if (! empty ( $_SERVER ["REMOTE_ADDR"] ))
			$cip = $_SERVER ["REMOTE_ADDR"];
		else
			$cip = "";
		return $cip;
	}
}

/**
 * 抛出错误异常
 * 
 * @param string $msg 错误信息
 * @param boolean $throw 是否抛出异常
 */
if (FALSE === function_exists('throwException'))
{
	function throwException($msg, $throw = TRUE )
	{
		if ($throw)
		{
			throw new ErrorException($msg);
		} else {
			echo $msg . "\n";
		}
	}
}

/**
 * 返回当前的时间
 * 
 * @param string $format
 */
if (FALSE === function_exists('now'))
{
	function now($format='Y-m-d h:i:s')
	{
		return date($format);
	}
}

/**
 * 获取对象的实例方法
 * $class_methods = get_class_methods('myclass');
 * or
 * $class_methods = get_class_methods(new myclass());
 * 
 * @param $className object || string
 */
if (FALSE === function_exists('getInstanceMethods'))
{
	function getInstanceMethods($className)
	{
		$returnArray = array();
	
		foreach (get_class_methods($className) as $method)
		{
			$reflect = new ReflectionMethod($className, $method);
			if ( !$reflect->isStatic() && !$reflect->isConstructor() )
			{
				array_push($returnArray,$method);
			}
		}
		return $returnArray;
	}
}

if (FALSE === function_exists('lcfirst'))
{
	/**
	 * Make a string's first character lowercase
	 *
	 * @param string $str
	 * @return string the resulting string.
	 */
	function lcfirst( $str )
	{
		$str[0] = strtolower($str[0]);
		return (string)$str;
	}
}

if (FALSE === function_exists('phpcache'))
{
	// $lifetime 秒
	// $data为 NULL 时获得 cache 内容
	function phpcache($name, $data = NULL, $lifetime = NULL)
	{
		return Yi::cache($name, $data, $lifetime);
	}
}

if (FALSE === function_exists('phplog'))
{
	function phplog($data, $name = NULL, $dir = NULL)
	{
		if( !empty( $data ) )
		{
			// $data = '[' . date( 'Y-m-d H:i:s' ) . ']{' .  posix_getpid() . '}' . $data . "\r\n";
			$data = '[' . date( 'Y-m-d H:i:s' ) . '] ' . $data . "\r\n";
			if (empty($name))
			{
				$name = date('Y-m-d');
			}
			
			$path = Yi::$logs_dir . DS . $name;
			
			if ( !empty($dir))
			{
				$path = $dir . DS . $name;
			}
			$file_handle = @fopen($path, 'ab');
			@fwrite($file_handle, $data);
			@fclose($file_handle);
		}
	}
}

if (FALSE == function_exists('build_url'))
{
	function build_url($host, $port=NULL, $path=NULL, $query=NULL, $scheme="http")
	{
		if ( !is_string($host) or $host == '') die('param host is empty');
		if ( !is_string($scheme) or $scheme == '') die('param scheme is empty');

		$url_ = $scheme . "://" . $host;
		if ( !empty($port))
			$url_ = $url_ . ':' . $port;

		if ( !empty($path))
			$url_ = $url_ . '/' . $path;

		if ( !empty($query))
			$url_ = $url_ . '?' . $query;

		return $url_;
	}
}