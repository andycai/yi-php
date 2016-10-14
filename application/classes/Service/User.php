<?php defined('SYSPATH') or die('No direct script access.');

// 用户服务接口
class Service_User extends Service
{
	public function __construct()
	{
		parent::__construct();

		$this->userModel = $this->load->model('user');
	}

	public function auth($value='')
	{
		$param = array();
		$account = $value['account'];
		$password = $value['password'];
		$platform = $value['platform'];

		$param['retCode'] = App_Status::SUCCESS;

		if (empty($account) || empty($password))
		{
			$param['retCode'] = App_Status::PARAMETER_ERROR; // 参数错误
			return $param;
		}

		if ( !$this->check_platform($platform))
		{
			$param['retCode'] = App_Status::PLATFORM_ERROR;
			return $param;
		}

		if ($platform != 1)
		{
			$account = sprintf("%s-%s", $platform, $account);
		}

		if ( !$this->userModel->has($account))
		{
			$param['retCode'] = App_Status::ACCOUNT_NOT_FOUND;
			return $param;
		}

		$user = $this->userModel->get($account);
		if ($user['password'] != md5($password))
		{
			$param['retCode'] = App_Status::PASSWORD_IS_WRONG;
			return $param;
		}

		$info_ = $this->get_default_server($account);

		$param['created'] = $info_['created'];
		$param['server'] = $info_['server'];
		$param['vip'] = $info_['vip'];
		$param['token'] = $this->encode_token($account);

		return $param;
	}

	public function auth_register($value='')
	{
		$param = array();
		$account = $value['account'];
		$password = $value['password'];
		$platform = $value['platform'];

		$value['password'] = md5($value['password']);

		$param['retCode'] = App_Status::SUCCESS;

		if (empty($account) || empty($password))
		{
			$param['retCode'] = App_Status::PARAMETER_ERROR; // 参数错误
			return $param;
		}

		if ( !$this->check_platform($platform))
		{
			$param['retCode'] = App_Status::PLATFORM_ERROR;
			return $param;
		}

		//检查密码和帐号合法性
		if( !preg_match("/^\w{4,16}$/", $account))
		{
			$param['retCode'] = App_Status::ACCOUNT_LENGTH_ERROR;
			return $param;
		}
		
		if( !preg_match("/^.{6,16}$/", $password))
		{
			$param['retCode'] = App_Status::PASSWORD_LENGTH_ERROR;
			return $param;
		}

		$accountNew = $account;
		if ($platform != 1)
		{
			$account = sprintf("%s-%s", $platform, $account);
			$accountNew = $account;
		}
		$value['account'] = $account;
		$value['accountNew'] = $accountNew;

		if ($this->userModel->has($account))
		{
			$param['retCode'] = App_Status::ACCOUNT_EXISTS;
			return $param;
		}

		$lastId = $this->userModel->add($value);

		$param['token'] = $this->encode_token($account);

		return $param;
	}

} // End Service_User