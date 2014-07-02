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

class Kernel
{
	//配置信息
	static $_conf = array();
	//语言包信息
	static $_lang = array();
	//初始化MVCA模式的值
	static $_controller = null;
	static $_action = null;

	public static function start()
	{
		//加载配置信息，用户自定义的配置会覆盖系统的配置
		self::loadConf();

		//加载语言包
		self::loadLang();

		//自动加载用户自定义的函数库
		self::loadfunc();

		//自动的解析URL分发
		self::parseurl();

		//自动加载类库
		spl_autoload_register('Kernel::autoload');

		//目录检测和自动生成,为了效率此函数仅仅执行一次
		self::mkdirs();

		$con_name = self::$_controller.self::$_conf['C_NAME'];
		$act_name = self::$_controller.self::$_action.self::$_conf['A_NAME'];

		if (!class_exists($con_name))
		{
			if (DEBUG)
			  die(self::$_lang['_SYS_LANG_CLASS_NOT_FIND'].' : '.$con_name);
			else
			  die(self::$_lang['_SYS_LANG_CLASS_NOT_FIND']);
		}

		if (!class_exists($act_name))
		{
			if (DEBUG)
			  die(self::$_lang['_SYS_LANG_CLASS_NOT_FIND'].' : '.$act_name);
			else
			  die(self::$_lang['_SYS_LANG_CLASS_NOT_FIND']);
		}

		if (!method_exists($act_name, self::$_action))
		{
			if (DEBUG)
			  die(self::$_lang['_SYS_LANG_METHOD_NOT_FIND'].' : '.$act_name.' ---> '.self::$_action);
			else
			  die(self::$_lang['_SYS_LANG_METHOD_NOT_FIND']);
		}
		$con_obj = new $con_name();
		$act_obj = new $act_name();

		$con_boj->init();
		$act_boj->init();
		$act_obj->run();
	}

	/*
	  * 功能  ： 实现目录检测和自动创建目录
	  * 参数  ： void
	  * 返回  ： void
	*/
	private static function mkdirs()
	{
		$lockfile = TEMP_PATH.'build_dir.lock';
		if (is_file($lockfile))
		  return;
		$dirArr = array(
					APP_CONF,
					APP_LANG,
					APP_FUNC,
					HTML_PATH,
					TEMP_PATH,
					DATA_PATH,
					CACHE_PATH,
					APP_M,
					APP_V,
					APP_C,
					APP_A,
					APP_P,
		);
		mkdirs($dirArr);
		touch($lockfile);
	}

	/*
	  * 功能  ： 该方法实现了自动加载类库的功能，在使用类似new操作时候，将自动调用此方法
	  * 参数  ： $cls  类库class名称
	  * 返回  ： 加载类库成功返回import函数的返回值 ， 加载类库失败程序停止执行
	  * 说明  ： 在debug模式下，类库加载失败，程序会报错，并且停止运行， 非debug模式，程序仅仅提示错误，但不显示详细错误内容
	*/
	public static function autoload($cls)
	{
		$sysClassArr = self::sysClassCache();
		if (isset($sysClassArr[$cls]))
			return import($sysClassArr[$cls]);

		if (!self::loadUserClass($cls))
		{
			if (DEBUG)
			  die(self::$_lang['_SYS_LANG_CLASS_NOT_FIND'].' : '.$cls);
			else
			  die(self::$_lang['_SYS_LANG_CLASS_NOT_FIND']);
		}
	}

	/*
	  * 功能  ： 实现URL的解析和分发
	  * 参数  ： void
	  * 返回  ： void
	*/
	private static function parseurl()
	{
		switch (self::$_conf['URL_MODEL'])
		{
			case URL_COMMON:              //普通URL模式
				self::url_common();
				break;
			case URL_PATHINFO:            //PATHINFO模式
				self::url_pathinfo();
				break;
			case URL_REWRITE:             //REWRITE模式
				self::url_rewrite();
				break;
			default:                      //默认使用兼容模式URL_COMPAT
				self::url_compat();
		}
	}

	/*
	  * 功能  ： 解析URL为rewrite模式
	  * 参数  ： void
	  * 返回  ： void
	  * 说明  ： 由于未找到合适的判断rewrite模块是否支持函数，所以仅仅对使用apache的服务器做了rewrite检测，使用nginx/iis等服务器的请自己测试
	*/
	private static function url_rewrite()
	{
		//apache_get_modules
		if ($_SERVER['REQUEST_URI'] != '/' && $_SERVER['REQUEST_URI'] != $_SERVER['PHP_SELF'] && (function_exists('apache_get_modules') && !in_array('mod_rewrite', apache_get_modules())))
		  die(self::$_lang['_SYS_LANG_URL_PARAMETER_ERROR']);

		self::$_controller = 'Index';
		self::$_action = 'index';

		if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != '/' && $_SERVER['REQUEST_URI'] != $_SERVER['PHP_SELF'])
		{
			$tmpArr = array_values(array_filter(explode('/', trim($_SERVER['REQUEST_URI']))));
			if (count($tmpArr) < 2)
			  die(self::$_lang['_SYS_LANG_URL_PARAMETER_ERROR']);

			self::$_controller = ucfirst(trim($tmpArr[0]));
			self::$_action = trim($tmpArr[1]);
			unset($tmpArr[0], $tmpArr[1]);
			if (!empty($tmpArr) && count($tmpArr) > 0)
			{
				$tmpArr = array_values($tmpArr);
				foreach ($tmpArr as $k => $v)
				{
					if ($k % 2 == 0)
					{
						//判断key的部分
						if (!isword($v))
						  die(self::$_lang['_SYS_LANG_URL_PARAMETER_VALUE_INVALID'].': '.$v);
						if (!isset($_GET[$v]))
						{
							if (isset($tmpArr[$k + 1]))
								$_GET[$v] = $tmpArr[$k + 1];
							else
								$_GET[$v] = null;

							if (!isset($_REQUEST[$v]))
								$_REQUEST[$v] = $_GET[$v];
						}
					}
				}
			}
			unset($tmpArr);
		}
	}

	/*
	  * 功能  ： 解析URL为兼容模式
	  * 参数  ： void
	  * 返回  ： void
	*/
	private static function url_compat()
	{
		if ($_SERVER['REQUEST_URI'] != '/' && $_SERVER['REQUEST_URI'] != $_SERVER['PHP_SELF'] && !isset($_GET['s']))
		  die(self::$_lang['_SYS_LANG_URL_PARAMETER_ERROR']);

		self::$_controller = 'Index';
		self::$_action = 'index';

		if (isset($_GET['s']))
		{
			$tmpArr = array_values(array_filter(explode('/', trim($_GET['s']))));
			if (count($tmpArr) < 2)
			  die(self::$_lang['_SYS_LANG_URL_PARAMETER_ERROR']);

			self::$_controller = ucfirst(trim($tmpArr[0]));
			self::$_action = trim($tmpArr[1]);
			unset($tmpArr[0], $tmpArr[1]);
			if (!empty($tmpArr) && count($tmpArr) > 0)
			{
				$tmpArr = array_values($tmpArr);
				foreach ($tmpArr as $k => $v)
				{
					if ($k % 2 == 0)
					{
						//判断key的部分
						if (!isword($v))
						  die(self::$_lang['_SYS_LANG_URL_PARAMETER_VALUE_INVALID'].': '.$v);
						if (!isset($_GET[$v]))
						{
							if (isset($tmpArr[$k + 1]))
								$_GET[$v] = $tmpArr[$k + 1];
							else
								$_GET[$v] = null;

							if (!isset($_REQUEST[$v]))
								$_REQUEST[$v] = $_GET[$v];
						}
					}
				}
			}
			unset($tmpArr, $_GET['s']);
		}
	}

	/*
	  * 功能  ： 解析URL为普通模式
	  * 参数  ： void
	  * 返回  ： void
	*/
	private static function url_common()
	{
		if ($_SERVER['REQUEST_URI'] != '/' && $_SERVER['REQUEST_URI'] != $_SERVER['PHP_SELF'] && (!isset($_GET['act']) || !isset($_GET['con'])))
		  die(self::$_lang['_SYS_LANG_URL_PARAMETER_ERROR']);
		self::$_controller = 'Index';
		self::$_action = 'index';

		if (isset($_GET['con']))
		{
			self::$_controller = trim($_GET['con']);
			if (!isword(self::$_controller))
			  die(self::$_lang['_SYS_LANG_URL_PARAMETER_VALUE_INVALID'].': '.self::$_controller);
		}

		if (isset($_GET['act']))
		{
			self::$_action = trim($_GET['act']);
			if (!isword(self::$_action))
			  die(self::$_lang['_SYS_LANG_URL_PARAMETER_VALUE_INVALID'].': '.self::$_action);
		}
	}

	/*
	  * 功能  ： 解析URL为pathinfo模式，如果服务器不支持pathinfo模式，停止执行程序
	  * 参数  ： void
	  * 返回  ： void
	*/
	private static function url_pathinfo()
	{
		if ($_SERVER['REQUEST_URI'] != '/' && $_SERVER['REQUEST_URI'] != $_SERVER['PHP_SELF'] && !isset($_SERVER['PATH_INFO']))
		  die(self::$_lang['_SYS_LANG_NOT_SUPPORT_PATHINFO']);

		self::$_controller = 'Index';
		self::$_action = 'index';
		if (isset($_SERVER['PATH_INFO']))
		{
			$tmpArr = array_values(array_filter(explode('/', trim($_SERVER['PATH_INFO']))));
			if (count($tmpArr) < 2)
			  die(self::$_lang['_SYS_LANG_URL_PARAMETER_ERROR']);

			self::$_controller = ucfirst(trim($tmpArr[0]));
			self::$_action = trim($tmpArr[1]);
			unset($tmpArr[0], $tmpArr[1]);
			if (!empty($tmpArr) && count($tmpArr) > 0)
			{
				$tmpArr = array_values($tmpArr);
				foreach ($tmpArr as $k => $v)
				{
					if ($k % 2 == 0)
					{
						//判断key的部分
						if (!isword($v))
						  die(self::$_lang['_SYS_LANG_URL_PARAMETER_VALUE_INVALID'].': '.$v);
						if (!isset($_GET[$v]))
						{
							if (isset($tmpArr[$k + 1]))
								$_GET[$v] = $tmpArr[$k + 1];
							else
								$_GET[$v] = null;

							if (!isset($_REQUEST[$v]))
								$_REQUEST[$v] = $_GET[$v];
						}
					}
				}
			}
			unset($tmpArr);
		}
	}

	/*
	  * 功能  ： 自动加载配置项
	  * 参数  ： void
	  * 返回  ： void
	*/
	private static function loadConf()
	{
		if (is_array(self::$_conf) && !empty(self::$_conf))
		  return;

		static $confPath = array(
					SYS_CONF,    // 系统的默认配置的目录
					APP_CONF,    // 应用程序的默认配置的目录
		);
		foreach ($confPath as $cp)
		{
			$dirArr = read_dir($cp);
			if (is_array($dirArr) && !empty($dirArr))
			{
				foreach ($dirArr as $file)
				{
					if (strstr($file, CONF_EXT) != CONF_EXT)
					  continue;
					self::$_conf = array_merge(self::$_conf, import($file));
				}
			}
			unset($dirArr);
		}
	}

	/*
	  * 功能  ： 自动加载语言包
	  * 参数  ： void
	  * 返回  ： void
	*/
	private static function loadLang()
	{
		if (is_array(self::$_lang) && !empty(self::$_lang))
		  return;

		static $confPath = array(
					SYS_LANG,    // 系统的默认语言包目录
					APP_LANG,    // 应用程序的默认语言包目录
		);
		foreach ($confPath as $cp)
		{
			$dirArr = read_dir($cp);
			if (is_array($dirArr) && !empty($dirArr))
			{
				foreach ($dirArr as $file)
				{
					if (strstr($file, self::$_conf['LANG'].CONF_EXT) != self::$_conf['LANG'].CONF_EXT)
					  continue;
					self::$_lang = array_merge(self::$_lang, import($file));
				}
			}
			unset($dirArr);
		}
	}

	/*
	  * 功能  ： 自动加载用户自定义的类库文件
	  * 参数  ： $cls 需要加载的类名称
	  * 返回  ： 成功返回true,失败返回false
	  * 说明  ： 所有类定义扩展名必须符合相关类常量扩展名的定义
	*/
	private static function loadUserClass($cls)
	{
		//判断是否model
		if (strpos($cls, self::$_conf['M_NAME']) !== false)
		{
			$file = APP_M.str_replace(self::$_conf['M_NAME'], '', $cls).CLS_M_EXT;
			if (!is_file($file))
			  return false;
			import($file);
			return true;
		}

		//判断是否controller
		if (strpos($cls, self::$_conf['C_NAME']) !== false)
		{
			$file = APP_C.str_replace(self::$_conf['C_NAME'], '', $cls).CLS_C_EXT;
			if (!is_file($file))
			  return false;
			import($file);
			return true;
		}

		//判断是否action
		if (strpos($cls, self::$_conf['A_NAME']) !== false)
		{
			$file = APP_A.self::$_controller.'/'.str_replace(self::$_conf['A_NAME'], '', $cls).CLS_A_EXT;
			if (!is_file($file))
			  return false;
			import($file);
			return true;
		}
		return false;
	}

	/*
	  * 功能  ： 自动加载用户自定义的函数库文件
	  * 返回  ： void
	  * 说明  ： 所有函数名称不应该重复，其次必须符合FUNC_EXT常量的扩展名定义
	*/
	private static function loadfunc()
	{
		$dirArr = read_dir(APP_FUNC);
		if (is_array($dirArr) && !empty($dirArr))
		{
			foreach ($dirArr as $file)
			{
				if (strstr($file, FUNC_EXT) != FUNC_EXT)
				  continue;
				import($file);
			}
		}
		unset($dirArr);
	}

	/*
	  * 功能  ： 该方法实现了自动缓存系统类库代码路径到内存
	  * 返回  ： 成功返回缓存类库路径array ， 失败返回 false
	  * 说明  ： 系统类库下所有类名称不允许重复，否则会导致类覆盖
	*/
	private static function sysClassCache()
	{
		static $classArr = array();
		static $sys_dir = array(
			SYS_KERNEL,       // 加载系统核心代码目录SYS_KERNEL下的所有class文件，支持多级目录递归,区分大小写
			SYS_LIB,          // 加载类库代码SYS_LIB
			SYS_CORE,         // 核心类库代码SYS_CORE
			SYS_VENDOR,       // 框架引入的第三方类库代码SYS_VENDOR
		);

		if (!DEBUG && !empty($classArr))
		  return $classArr;
		
		foreach ($sys_dir as $sd)
		{
			if (is_dir($sd))
			{
				$dirArr = read_dir($sd);
				if (is_array($dirArr) && !empty($dirArr))
				{
					foreach ($dirArr as $file)
					{
						if (strstr($file, CLS_EXT) != CLS_EXT)
						  continue;
						$classArr[basename($file, CLS_EXT)] = $file;
					}
				}
				unset($dirArr);
			}
		}
		return $classArr;
	}
}
