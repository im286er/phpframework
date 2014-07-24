<?php
/**
* Action类，所有Action都应该继承此类
* @filename Action.class.php
* @touch date 2014-07-23 16:16:16
* @author Rain<563268276@qq.com>
* @copyright 2014 http://www.94cto.com/
* @license http://www.apache.org/licenses/LICENSE-2.0   LICENSE-2.0
* @package Rain PHP Frame(RPF)
*/

defined('RPF_PATH') or exit();

/**
* Action类，所有的action都应该继承此类
*/
class Action
{
	/**
	* 存储action设置的模板变量
	*/
	private $valArr = array();

	/**
	* 是否开启对于form表单的token校验
	*/
	protected $open_token = true;

	/**
	* action 初始化调用
	*/
	protected function init()
	{
	}

	/**
	* action 真正执行的方法，所有子类必须重写这个方法
	*/
	protected function run()
	{
	}

	/**
	* 自动帮助子类校验表单token的方法
	*/
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

   /**
	* 设置action中的变量到模板的方法
	* <code>
	* $this->set('val', 'abc');
	* </code>
	* @param string $key 变量名称
	* @param string  $val 变量值
	* @return void 
	*/
	protected function set($key, $val)
	{
		$this->valArr[$key] = $val;
	}

	/**
	* 显示模板文件的方法
	* <code>
	* $this->display();
	* </code>
	* @param string $tpl 模板名称，默认同action名称
	* @return void 
	*/
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
			  die(Kernel::$_lang['_SYS_LANG_TEMPLATE_NOT_FIND'].' : '.$file);
			else
			  die(Kernel::$_lang['_SYS_LANG_TEMPLATE_NOT_FIND']);
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
