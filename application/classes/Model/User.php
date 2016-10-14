<?php defined('SYSPATH') or die('No direct script access.');

// 用户数据处理
class Model_User extends Model
{
	public function add($value)
	{
		$lastId = $this->db->insert('user_list', array(
			'account' => $value['account'],
			'password' => $value['password'],
			'platform' => $value['platform'],
			'device' => $value['device'],
			'create_time' => date('Y-m-d H:i:s'),
			'update_time' => date('Y-m-d H:i:s')
		));

		return $lastId;
	}

	public function update($value)
	{
		return $this->db->update('user_list', array(
			'update_time' => date('Y-m-d H:i:s')
		), array(
			'account' => $value
		));
	}

	public function has($value='')
	{
		return $this->db->has('user_list', array(
				'account' => $value
			));
	}

	public function get($value)
	{
		$user = $this->db->get('user_list', array(
			'account',
			'password',
			'platform',
			'device',
		), array(
			'account' => $value
		));

		return $user;
	}

} // End Model_User