<?php
// +----------------------------------------------------------------------
// | RPF  [Rain PHP Framework ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.94cto.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Rain <563268276@qq.com>
// +----------------------------------------------------------------------

defined('RPF_PATH') or exit();

//所有的action都应该继承此类
class Action
{
	//存储action设置的模板变量
	private $valArr = array();
	//是否开启对于form表单的token校验
	protected $open_token = true;

	//action 初始化调用
	protected function init()
	{
	}

	protected function success($msg = '操作成功', $code = 200, $navTabId = '', $rel = '', $callbackType = '', $forwardUrl = '', $confirmMsg = '')
	{
		$data = array(
			'statusCode' => $code,
			'message' => $msg,
			'navTabId' => $navTabId,
			'rel' => $rel,
			'callbackType' => $callbackType,
			'forwardUrl' => $forwardUrl,
			'confirmMsg' => $confirmMsg,
		);
		exit(json_encode($data));
	}

	protected function error($msg = '操作失败', $code = 300, $navTabId = '', $rel = '', $callbackType = '', $forwardUrl = '', $confirmMsg = '')
	{
		$data = array(
			'statusCode' => $code,
			'message' => $msg,
			'navTabId' => $navTabId,
			'rel' => $rel,
			'callbackType' => $callbackType,
			'forwardUrl' => $forwardUrl,
			'confirmMsg' => $confirmMsg,
		);
		exit(json_encode($data));
	}

	protected function timeout($msg = '操作超时', $code = 301, $navTabId = '', $rel = '', $callbackType = '', $forwardUrl = '', $confirmMsg = '')
	{
		$data = array(
			'statusCode' => $code,
			'message' => $msg,
			'navTabId' => $navTabId,
			'rel' => $rel,
			'callbackType' => $callbackType,
			'forwardUrl' => $forwardUrl,
			'confirmMsg' => $confirmMsg,
		);
		exit(json_encode($data));
	}

	//action 真正执行的方法，所有子类必须重写这个方法
	protected function run()
	{
	}

	protected function checktoken()
	{
		if (count($_POST) && $this->open_token && isset($_SESSION[TOKEN_NAME]) && isset($_SESSION[$_SESSION[TOKEN_NAME]]))
		{
			if (!isset($_REQUEST[TOKEN_NAME]))
				return false;
			$val2 = trim($_REQUEST[TOKEN_NAME]);
			if ($val2 != $_SESSION[$_SESSION[TOKEN_NAME]])
			{
				unset($_SESSION[$_SESSION[TOKEN_NAME]]);
				unset($_SESSION[TOKEN_NAME]);
				return false;
			}
			unset($_SESSION[$_SESSION[TOKEN_NAME]]);
			unset($_SESSION[TOKEN_NAME]);
		}
		return true;
	}

	protected function set($key, $val)
	{
		$this->valArr[$key] = $val;
	}

	protected function display($tpl = null)
	{
		if (is_null($tpl))
		  $tpl = Kernel::$_action;
		$tpl .= Kernel::$_conf['V_NAME'];
		$file = APP_V.Kernel::$_controller.'/'.$tpl;
		if (!empty($this->valArr))
		{
			foreach ($this->valArr as $vk => $vv)
				$$vk = $vv;
		}
		unset($this->valArr);
		if (!is_file($file))
		{
			if (DEBUG)
			  die(self::$_lang['_SYS_LANG_TEMPLATE_NOT_FIND'].' : '.$file);
			else
			  die(self::$_lang['_SYS_LANG_TEMPLATE_NOT_FIND']);
		}
		$GLOBALS['_reqFile']++;
		if (OPEN_TOKEN && $this->open_token)
		{
			ob_start("callback");
			require_once($file);
			ob_end_flush();
		}
		else
			require_once($file);
	}
}
