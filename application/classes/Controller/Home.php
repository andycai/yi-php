<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Home extends Template_Controller
{
	public $template = 'index';

	public function action_index($name='Coolsite', $year='2011')
	{
		// print_r($_SERVER);die();

		// die(microtime(TRUE));
		$this->template->setFilename('home');

		$userInfo = array('name'=>$name, 'year'=>$year);
		$content = print_r($userInfo, true);

		$data = array('title'=>__('Cool site'), 'content'=>$content, 'copyright'=>__($name));

		$this->template->head = View::factory('head', $data);
		$this->template->foot = View::factory('foot', $data);
		
		$this->template->set($data);

//		$this->redirect('/home/index/id/1/');
	}
} // End Controller_Home