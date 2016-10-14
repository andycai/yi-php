<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 自动模板控制器基类
 * 
 * @author Andy Cai (huayicai@gmail.com)
 * 
 */
class Template_Controller extends Controller
{
	protected $load;
	
	public $template = 'tempalte';
	public $autoRender = TRUE;

	public function before()
	{
		parent::before();
		
		if ($this->autoRender === TRUE) {
			$classname = ucfirst(Yi::$template);
			$this->template = call_user_func(array($classname, 'factory'), $this->template);
		}
	}

	public function after()
	{
		if ($this->autoRender === TRUE)
		{
			$this->response($this->template->render());
		}

		parent::after();
	}
} // End Template_Controller