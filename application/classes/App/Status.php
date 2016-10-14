<?php defined('SYSPATH') or die('No direct script access.');

class App_Status
{
	const PASSWORD_LENGTH_ERROR				= -10;		
	const ACCOUNT_NOT_FOUND					= -9;		// 帐号不存在"
	const VERIFICATION_FAILURE				= -8;		// TOKEN验证失败
	const TOKEN_IS_EMPTY					= -7;		// 空TOKEN
	const ACCOUNT_EXISTS					= -6;		// 帐号已经存在
	const ACCOUNT_LENGTH_ERROR				= -5;		// 帐号长度应为4~16个字符
	const PASSWORD_IS_WRONG					= -4;		// 验证失败
	const ACCOUNT_IS_EMPTY					= -3;		// 账号为空
	const PLATFORM_ERROR 					= -2;		// 平台号错误
	const PARAMETER_ERROR					= -1;		// 参数错误
	const SUCCESS 							= 0;		// 成功
	const FAILURE							= 1;		// 错误
} // End App_Status