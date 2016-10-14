<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 框架的帮助类
 * 
 * @author Andy Cai (huayicai@gmail.com)
 * 
 */
class Yi
{
	const VERSION  = '1.2.0';
	const CODENAME = 'Dongshanju';
	
	const PRODUCTION  = 10;
	const STAGING     = 20;
	const TESTING     = 30;
	const DEVELOPMENT = 40;
	
	public static $lang = 'zh-cn';
	public static $charset = 'utf-8';
	public static $environment = Yi::DEVELOPMENT;
	public static $isCli = FALSE;
	public static $isWindows = FALSE;
	public static $magicQuotes = FALSE;
	public static $safeMode = FALSE;
	public static $baseUrl = '/';
	public static $theme = 'default';
	public static $indexFile = 'index.php';
	public static $errors = FALSE;
	public static $shutdownErrors = array(E_PARSE, E_ERROR, E_USER_ERROR, E_COMPILE_ERROR);
	public static $cache_dir;
	public static $logs_dir;
	public static $cache_life = 60; // 秒
	public static $caching = FALSE; // 是否为Yi::find_file用内部缓存
	public static $template = 'View';
	public static $config;

	protected static $_modules = array();
	protected static $_paths = array(APPPATH, SYSPATH);
	protected static $_files = array();
	protected static $_files_changed = FALSE;
	protected static $_init;
	
	public static function init(array $settings = NULL)
	{
		if (self::$_init)
		{
			// Do not allow execution twice
			return;
		}

		// Yi is now initialized
		self::$_init = TRUE;

		// Start an output buffer
		ob_start();

		if (isset($settings['errors']))
		{
			// Enable error handling
			self::$errors = (bool) $settings['errors'];
		}

		if (isset($settings['theme']))
		{
			self::$theme = (string) $settings['theme'];
		}

		if (isset($settings['template']))
		{
			self::$template = (string) $settings['template'];
		}

		if (self::$errors === TRUE)
		{
			// Enable exception handling, adds stack traces and error source.
			set_exception_handler(array('Yi', 'handleException'));

			// Enable error handling, converts all PHP errors to exceptions.
			set_error_handler(array('Yi', 'handleError'));
		}

		// Enable the Yi shutdown handler, which catches E_FATAL errors.
		register_shutdown_function(array('Yi', 'handleShutdown'));

		if (ini_get('register_globals'))
		{
			// Reverse the effects of register_globals
			self::globals();
		}

		// Determine if we are running in a command line environment
		self::$isCli = (PHP_SAPI === 'cli');

		// Determine if we are running in a Windows environment
		self::$isWindows = (DIRECTORY_SEPARATOR === '\\');

		// Determine if we are running in safe mode
		self::$safeMode = (bool) ini_get('safe_mode');

		if (isset($settings['charset']))
		{
			// Set the system character set
			self::$charset = strtolower($settings['charset']);
		}
		
		if (isset($settings['lang']))
		{
			self::$lang = strtolower(str_replace(array(' ', '_'), '-', $settings['lang']));
			I18n::lang(self::$lang);
		}

		if (function_exists('mb_internal_encoding'))
		{
			// Set the MB extension encoding to the same character set
			mb_internal_encoding(self::$charset);
		}
		
		if (isset($settings['cache_dir']))
		{
			if ( ! is_dir($settings['cache_dir']))
			{
				try
				{
					// Create the cache directory
					mkdir($settings['cache_dir'], 0755, TRUE);

					// Set permissions (must be manually set to fix umask issues)
					chmod($settings['cache_dir'], 0755);
				}
				catch (Exception $e)
				{
					throw new Yi_Exception('Could not create cache directory :dir',
						array(':dir' => Debug::path($settings['cache_dir'])));
				}
			}

			// Set the cache directory path
			Yi::$cache_dir = realpath($settings['cache_dir']);
		}
		else
		{
			// Use the default cache directory
			Yi::$cache_dir = APPPATH.'cache';
		}

		if ( ! is_writable(Yi::$cache_dir))
		{
			throw new Yi_Exception('Directory :dir must be writable',
				array(':dir' => Debug::path(Yi::$cache_dir)));
		}

		if (isset($settings['logs_dir']))
		{
			if ( ! is_dir($settings['logs_dir']))
			{
				try
				{
					mkdir($settings['logs_dir'], 0755, TRUE);
					chmod($settings['logs_dir'], 0755);
				}
				catch (Exception $e)
				{
					throw new Yi_Exception('Could not create logs directory :dir',
						array(':dir' => Debug::path($settings['logs_dir'])));
				}
			}
			Yi::$logs_dir = realpath($settings['logs_dir']);
		}
		else
		{
			Yi::$logs_dir = APPPATH.'logs';
		}

		if ( ! is_writable(Yi::$logs_dir))
		{
			throw new Yi_Exception('Directory :dir must be writable',
				array(':dir' => Debug::path(Yi::$logs_dir)));
		}

		if (isset($settings['cache_life']))
		{
			// Set the default cache lifetime
			Yi::$cache_life = (int) $settings['cache_life'];
		}

		if (isset($settings['caching']))
		{
			// Enable or disable internal caching
			Yi::$caching = (bool) $settings['caching'];
		}

		if (Yi::$caching === TRUE)
		{
			// Load the file path cache
			Yi::$_files = Yi::cache('Yi::find_file()');
		}

		if (isset($settings['baseUrl'])) 
		{
			// Set the base URL
			self::$baseUrl = rtrim($settings['baseUrl'], '/').'/';
		}

		if (isset($settings['indexFile']))
		{
			// Set the index file
			self::$indexFile = trim($settings['indexFile'], '/');
		}

		// Determine if the extremely evil magic quotes are enabled
		self::$magicQuotes = (bool) get_magic_quotes_gpc();

		// Sanitize all request variables
		$_GET    = self::sanitize($_GET);
		$_POST   = self::sanitize($_POST);
		$_COOKIE = self::sanitize($_COOKIE);

		if ( ! Yi::$config instanceof Config)
		{
			Yi::$config = new Config;
		}
	}

	public static function modules(array $modules = NULL)
	{
		if ($modules === NULL)
		{
			return Yi::$_modules;
		}

		$paths = array(APPPATH);

		foreach ($modules as $name => $path)
		{
			if (is_dir($path))
			{
				$paths[] = $modules[$name] = realpath($path).DS;
			}
			else
			{
				throw new Yi_Exception('Attempted to load an invalid or missing module \':module\' at \':path\'', array(
					':module' => $name,
					':path'   => Debug::path($path),
				));
			}
		}

		$paths[] = SYSPATH;

		Yi::$_paths = $paths;
		Yi::$_modules = $modules;

		return Yi::$_modules;
	}
	
	public static function app()
	{
		return Facade::getInstance();
	}
	
	public static function auto_load($className, $directory = 'classes')
	{
		$file = str_replace('_', DS, $className);
		$path = $directory.DS.$file.EXT;

		if ($path = Yi::find_file($directory, $file))
		{
			require $path;

			return TRUE;
		}
		
		return FALSE;
	}
	
	public static function globals()
	{
		if (isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS']))
		{
			// Prevent malicious GLOBALS overload attack
			echo "Global variable overload attack detected! Request aborted.\n";

			// Exit with an error status
			exit(1);
		}

		// Get the variable names of all globals
		$global_variables = array_keys($GLOBALS);

		// Remove the standard global variables from the list
		$global_variables = array_diff($global_variables, array(
			'_COOKIE',
			'_ENV',
			'_GET',
			'_FILES',
			'_POST',
			'_REQUEST',
			'_SERVER',
			'_SESSION',
			'GLOBALS',
		));

		foreach ($global_variables as $name)
		{
			// Unset the global variable, effectively disabling register_globals
			unset($GLOBALS[$name]);
		}
	}
	
	/**
	 * 递归清理输入变量：
	 *
	 * - magic quotes 开启，删除斜线
	 * - 规范所有换行为 LF
	 *
	 * @param   mixed  any variable
	 * @return  mixed  sanitized variable
	 */
	public static function sanitize($value)
	{
		$magic_quotes = (bool) get_magic_quotes_gpc();
		
		if (is_array($value) OR is_object($value))
		{
			foreach ($value as $key => $val)
			{
				// Recursively clean each value
				$value[$key] = self::sanitize($val);
			}
		}
		elseif (is_string($value))
		{
			if ($magic_quotes === TRUE)
			{
				// Remove slashes added by magic quotes
				$value = stripslashes($value);
			}

			if (strpos($value, "\r") !== FALSE)
			{
				// Standardize newlines
				$value = str_replace(array("\r\n", "\r"), "\n", $value);
			}
		}

		return $value;
	}

	public static function load($file)
	{
		return include $file;
	}

	public static function find_file($dir, $file, $ext = NULL, $array = FALSE)
	{
		if ($ext === NULL)
		{
			// Use the default extension
			$ext = EXT;
		}
		elseif ($ext)
		{
			// Prefix the extension with a period
			$ext = ".{$ext}";
		}
		else
		{
			// Use no extension
			$ext = '';
		}

		// Create a partial path of the filename
		$path = $dir.DIRECTORY_SEPARATOR.$file.$ext;

		if (Yi::$caching === TRUE AND isset(Yi::$_files[$path.($array ? '_array' : '_path')]))
		{
			// This path has been cached
			return Yi::$_files[$path.($array ? '_array' : '_path')];
		}

		if ($array OR $dir === 'config' OR $dir === 'i18n' OR $dir === 'messages')
		{
			// Include paths must be searched in reverse
			$paths = array_reverse(Yi::$_paths);

			// Array of files that have been found
			$found = array();

			foreach ($paths as $dir)
			{
				if (is_file($dir.$path))
				{
					// This path has a file, add it to the list
					$found[] = $dir.$path;
				}
			}
		}
		else
		{
			// The file has not been found yet
			$found = FALSE;

			foreach (Yi::$_paths as $dir)
			{
				if (is_file($dir.$path))
				{
					// A path has been found
					$found = $dir.$path;

					// Stop searching
					break;
				}
			}
		}

		if (Yi::$caching === TRUE)
		{
			// Add the path to the cache
			Yi::$_files[$path.($array ? '_array' : '_path')] = $found;

			// Files have been changed
			Yi::$_files_changed = TRUE;
		}

		return $found;
	}

	public static function list_files($directory = NULL, array $paths = NULL)
	{
		if ($directory !== NULL)
		{
			// Add the directory separator
			$directory .= DIRECTORY_SEPARATOR;
		}

		if ($paths === NULL)
		{
			// Use the default paths
			$paths = Yi::$_paths;
		}

		// Create an array for the files
		$found = array();

		foreach ($paths as $path)
		{
			if (is_dir($path.$directory))
			{
				// Create a new directory iterator
				$dir = new DirectoryIterator($path.$directory);

				foreach ($dir as $file)
				{
					// Get the file name
					$filename = $file->getFilename();

					if ($filename[0] === '.' OR $filename[strlen($filename)-1] === '~')
					{
						// Skip all hidden files and UNIX backup files
						continue;
					}

					// Relative filename is the array key
					$key = $directory.$filename;

					if ($file->isDir())
					{
						if ($sub_dir = Yi::list_files($key, $paths))
						{
							if (isset($found[$key]))
							{
								// Append the sub-directory list
								$found[$key] += $sub_dir;
							}
							else
							{
								// Create a new sub-directory list
								$found[$key] = $sub_dir;
							}
						}
					}
					else
					{
						if ( ! isset($found[$key]))
						{
							// Add new files to the list
							$found[$key] = realpath($file->getPathName());
						}
					}
				}
			}
		}

		// Sort the results alphabetically
		ksort($found);

		return $found;
	}
	
	/**
	 * 缓存
	 * 
	 * @param mixed $name 缓存名称
	 * @param mixed $data 缓存内容
	 * @param mixed $lifetime 过期（秒）
	 */
	public static function cache($name, $data = NULL, $lifetime = NULL)
	{
		// Cache file is a hash of the name
		$file = sha1($name).'.txt';

		// Cache directories are split by keys to prevent filesystem overload
		$dir = Yi::$cache_dir.DIRECTORY_SEPARATOR.$file[0].$file[1].DIRECTORY_SEPARATOR;

		if ($lifetime === NULL)
		{
			// Use the default lifetime
			$lifetime = Yi::$cache_life;
		}

		if ($data === NULL)
		{
			if (is_file($dir.$file))
			{
				if ((time() - filemtime($dir.$file)) < $lifetime)
				{
					// Return the cache
					try
					{
						return unserialize(file_get_contents($dir.$file));
					}
					catch (Exception $e)
					{
						// Cache is corrupt, let return happen normally.
					}
				}
				else
				{
					try
					{
						// Cache has expired
						unlink($dir.$file);
					}
					catch (Exception $e)
					{
						// Cache has mostly likely already been deleted,
						// let return happen normally.
					}
				}
			}

			// Cache not found
			return NULL;
		}

		if ( ! is_dir($dir))
		{
			// Create the cache directory
			mkdir($dir, 0777, TRUE);

			// Set permissions (must be manually set to fix umask issues)
			chmod($dir, 0777);
		}

		// Force the data to be a string
		$data = serialize($data);

		try
		{
			// Write the cache
			return (bool) file_put_contents($dir.$file, $data, LOCK_EX);
		}
		catch (Exception $e)
		{
			// Failed to write cache
			return FALSE;
		}
	}
	
	/**
	 * 主题模版路径
	 */
	public static function themePath()
	{
		return THEMEPATH . self::$theme . DS;
	}

	public static function baseUrl()
	{
		return self::$baseUrl . self::$indexFile;
	}
	
	/**
	 * 异常接管
	 * 
	 * @param mixed $exception
	 * @return void
	 */
	public static function handleException($exception)
	{
		// disable error capturing to avoid recursive errors
		restore_error_handler();
		restore_exception_handler();
		
		if ($exception instanceof Http_Exception)
		{
			ob_start();
			$view_file = SYSPATH . 'views' . DS . 'error' . $exception->statusCode . EXT;
			if ( is_file($view_file) )
			{
				include $view_file;
			}
			echo ob_get_clean();
			exit(1);
		}
		else
		{
			Yi_Exception::handler($exception);
		}
	}
	
	/**
	 * 错误接管
	 * 
	 * @param mixed $code
	 * @param mixed $error
	 * @param mixed $file
	 * @param mixed $line
	 * @return void
	 */
	public static function handleError($code, $error, $file = NULL, $line = NULL)
	{
		restore_error_handler();
		restore_exception_handler();
		
		if (error_reporting() & $code)
		{
			// This error is not suppressed by current error reporting settings
			// Convert the error into an ErrorException
			throw new ErrorException($error, $code, 0, $file, $line);
		}

		// Do not execute the PHP error handler
		return TRUE;
	}

	/**
	 * 捕捉那些不能被错误处理捕捉的错误，例如 E_PARSE
	 */
	public static function handleShutdown()
	{
		if ( ! Yi::$_init)
		{
			// Do not execute when not active
			return;
		}

		try
		{
			if (Yi::$caching === TRUE AND Yi::$_files_changed === TRUE)
			{
				// Write the file path cache
				Yi::cache('Yi::find_file()', Yi::$_files);
			}
		}
		catch (Exception $e)
		{
			// Pass the exception to the handler
			Yi_Exception::handler($e);
		}

		if (self::$errors AND $error = error_get_last()) // AND in_array($error['type'], self::$shutdownErrors))
		{
			// Clean the output buffer
			ob_get_level() and ob_clean();

			// Fake an exception for nice debugging
			// self::handleException(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
			Yi_Exception::handler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));

			// Shutdown now to avoid a "death loop"
			exit(1);
		}
	}
	
} // End Yi