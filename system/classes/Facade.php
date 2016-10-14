<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 框架的总控制器，单态模式
 * 
 * @author Andy Cai (huayicai@gmail.com)
 * 
 */
class Facade
{
	protected static $_instance;
	protected $_body;

	protected function __construct()
	{
	}
	
	public static function getInstance()
	{
		if (self::$_instance == NULL) self::$_instance = new Facade();
		return self::$_instance;
	}
	
	/**
	 * web应用程序的入口，接口预留，重构后使用
	 * - web应用程序初始化
	 * - 路由
	 * - 控制器action执行
	 * - 试图内容输出
	 */
	public function runApp()
	{
		Loader::getInstance()->helper('yi');

		// 响应输出
		$this->request();
	}
	
	/**
	 * Gets or sets 页面响应内容
	 * 
	 * @param string $content 内容
	 * @return mixed
	 */
	public function response($content = NULL)
	{
		if ($content === NULL)
		{
			return $this->_body;
		}
		
		$this->_body = (string) $content;
		return $this;
	}
	
	/**
	 * 私有方法，路过请绕道
	 */
	
	/**
	 * 请求解析
	 * <br> 处理URL中的请求
	 * <br> 路由到具体的Controller的具体方法(Action)
	 */
	private function request()
	{
		$route_ = Router::getInstance();
		$segments_ = $route_->get();

		$controller = 'home';
		if ( !empty($segments_[1]))
		{
			$controller = (string) $segments_[1];
		}

		$action = 'index';
		if ( !empty($segments_[2]))
		{
			$action = (string) $segments_[2];
		}
		
		$c = Loader::getInstance()->controller($controller);
		$c->execute($action, $segments_);

		echo $this->_body;		
	}
} // End Facade
