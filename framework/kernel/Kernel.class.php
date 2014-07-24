<?php
/**
* 核心类Kernel类
* @filename Kernel.class.php
* @touch date 2014-07-23 16:47:33
* @author Rain<563268276@qq.com>
* @copyright 2014 http://www.94cto.com/
* @license http://www.apache.org/licenses/LICENSE-2.0   LICENSE-2.0
* @package Rain PHP Frame(RPF)
*/
defined('RPF_PATH') or exit();

/**
* 核心类Kernel类
*/
class Kernel
{
	/**
	* 配置信息存储变量
	*/
	static $_conf = array();

	/**
	* 语言包信息存储变量
	*/
	static $_lang = array();

	/**
	* controller名称存储变量
	*/
	static $_controller = null;

	/**
	* action名称存储变量
	*/
	static $_action = null;

	/**
	* app名称存储变量
	*/
	static $_app = null;

	/**
	* 获取当前的action名称
	*/
	public static function getAction()
	{
		return self::$_action;
	}

	/**
	* 获取当前的controller名称
	*/
	public static function getController()
	{
		return self::$_controller;
	}

	/**
	* 获取当前的app名称
	*/
	public static function getApp()
	{
		return self::$_app;
	}

	/**
	* Kernel类的核心启动框架的方法
	*/
	public static function start()
	{
		//加载配置信息，用户自定义的配置会覆盖系统的配置
		self::loadConf();

		//加载语言包
		self::loadLang();

		//自动加载类库
		spl_autoload_register('Kernel::autoload');

		//自动加载用户自定义的函数库
		self::loadfunc();

		//初始化session
		self::session();

		//自动的解析URL分发
		self::parseurl();


		//脚本退出注册函数
		register_shutdown_function('shutdown');

		//目录检测和自动生成,为了效率此函数仅仅执行一次
		self::mkdirs();

		//是否创建demo例子程序
		$lockfile = TEMP_PATH.'build_demo.lock';
		if (C_DEMO && !is_file($lockfile))
		{
			Demo::cdemo();
			touch($lockfile);
		}

		$con_name = self::$_controller.self::$_conf['C_NAME'];
		$act_name = self::$_controller.'_'.self::$_action.self::$_conf['A_NAME'];

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

		$con_obj = new $con_name();
		$act_obj = new $act_name();

		$con_obj->init();
		$act_obj->init();
		$act_obj->run();
	}

	/**
	* 初始化session
	*/
	private static function session()
	{
		//init session save type
		if (extension_loaded('memcache') && self::$_conf['SESSION_SAVE_TYPE'] == 'm')
		{
			ini_set('session.save_handler', 'memcache');
			ini_set('session.save_path', 'tcp://'.self::$_conf['MEM_HOST'].':'.self::$_conf['MEM_PORT']);
		}
		Session::sid(self::$_conf['S_ID']);
		Session::name(self::$_conf['S_NAME']);
		Session::expire(self::$_conf['S_EXPIRE']);
		session_start();
	}

	/**
	* 递归创建框架所需的目录
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

		//拷贝配置文件到应用程序的配置目录下
		copy(SYS_CONF.'main'.CONF_EXT, APP_CONF.'main'.CONF_EXT);
		touch($lockfile);
	}

	/**
	* 该方法实现了自动加载类库的功能，在使用类似new操作时候，将自动调用此方法
	* @param string $cls 类名称
	* @return bool 返回同import函数的返回值 ， 加载类库失败程序停止执行
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

	/**
	* 实现URL的解析和分发
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

	/**
	* 解析URL为rewrite模式,由于未找到合适的判断rewrite模块是否支持函数，所以仅仅对使用apache的服务器做了rewrite检测，使用nginx/iis等服务器的请自己测试
	*/
	private static function url_rewrite()
	{
		//apache_get_modules
		if ($_SERVER['REQUEST_URI'] != '/' && $_SERVER['REQUEST_URI'] != $_SERVER['PHP_SELF'] && (function_exists('apache_get_modules') && !in_array('mod_rewrite', apache_get_modules())))
		  die(self::$_lang['_SYS_LANG_URL_PARAMETER_ERROR']);

		self::$_controller = 'Index';
		self::$_action = 'index';
		self::$_app = 'index';

		if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != '/' && $_SERVER['REQUEST_URI'] != $_SERVER['PHP_SELF'])
		{
			$tmpArr = array_values(array_filter(explode('/', str_replace('=', '/', str_replace('&', '/', trim($_SERVER['REQUEST_URI']))))));
			if (count($tmpArr) < 3)
			  die(self::$_lang['_SYS_LANG_URL_PARAMETER_ERROR']);

			self::$_app = trim($tmpArr[0]);
			self::$_controller = ucfirst(trim($tmpArr[1]));
			self::$_action = trim($tmpArr[2]);
			unset($tmpArr[0], $tmpArr[1], $tmpArr[2]);

			if (!isword(self::$_app))
			  die(self::$_lang['_SYS_LANG_URL_PARAMETER_VALUE_INVALID'].': '.self::$_app);

			//判断入口文件是否存在
			if (!is_file(str_replace(APP_NAME.'/', '', APP_PATH).self::$_app.'.php'))
			{
				if (DEBUG)
				  die(self::$_lang['_SYS_LANG_FILE_NOT_FIND'].': '.str_replace(APP_NAME.'/', '', APP_PATH).self::$_app.'.php');
				else
				  die(self::$_lang['_SYS_LANG_FILE_NOT_FIND']);
			}
			//end

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

	/**
	* 解析URL为兼容模式
	*/
	private static function url_compat()
	{
		if ($_SERVER['REQUEST_URI'] != '/' && $_SERVER['REQUEST_URI'] != $_SERVER['PHP_SELF'] && !isset($_GET['s']))
		  die(self::$_lang['_SYS_LANG_URL_PARAMETER_ERROR']);

		self::$_controller = 'Index';
		self::$_action = 'index';
		self::$_app = 'index';

		if (isset($_GET['s']))
		{
			$tmpArr = array_values(array_filter(explode('/', trim($_GET['s']))));
			if (count($tmpArr) < 3)
			  die(self::$_lang['_SYS_LANG_URL_PARAMETER_ERROR']);

			self::$_controller = ucfirst(trim($tmpArr[1]));
			self::$_action = trim($tmpArr[2]);
			self::$_app = trim($tmpArr[0]);
			unset($tmpArr[0], $tmpArr[1], $tmpArr[2]);

			if (!isword(self::$_app))
			  die(self::$_lang['_SYS_LANG_URL_PARAMETER_VALUE_INVALID'].': '.self::$_app);

			//判断入口文件是否存在
			if (!is_file(str_replace(APP_NAME.'/', '', APP_PATH).self::$_app.'.php'))
			{
				if (DEBUG)
				  die(self::$_lang['_SYS_LANG_FILE_NOT_FIND'].': '.str_replace(APP_NAME.'/', '', APP_PATH).self::$_app.'.php');
				else
				  die(self::$_lang['_SYS_LANG_FILE_NOT_FIND']);
			}
			//end

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

	/**
	* 解析URL为普通模式
	*/
	private static function url_common()
	{
		if ($_SERVER['REQUEST_URI'] != '/' && $_SERVER['REQUEST_URI'] != $_SERVER['PHP_SELF'] && (!isset($_GET['act']) || !isset($_GET['app']) || !isset($_GET['con'])))
		  die(self::$_lang['_SYS_LANG_URL_PARAMETER_ERROR']);
		self::$_controller = 'Index';
		self::$_action = 'index';
		self::$_app = 'index';

		if (isset($_GET['con']))
		{
			self::$_controller = trim($_GET['con']);
			if (!isword(self::$_controller))
			  die(self::$_lang['_SYS_LANG_URL_PARAMETER_VALUE_INVALID'].': '.self::$_controller);
		}

		if (isset($_GET['app']))
		{
			self::$_app = trim($_GET['app']);
			if (!isword(self::$_app))
			  die(self::$_lang['_SYS_LANG_URL_PARAMETER_VALUE_INVALID'].': '.self::$_app);

			//判断入口文件是否存在
			if (!is_file(str_replace(APP_NAME.'/', '', APP_PATH).self::$_app.'.php'))
			{
				if (DEBUG)
				  die(self::$_lang['_SYS_LANG_FILE_NOT_FIND'].': '.str_replace(APP_NAME.'/', '', APP_PATH).self::$_app.'.php');
				else
				  die(self::$_lang['_SYS_LANG_FILE_NOT_FIND']);
			}
			//end
		}

		if (isset($_GET['act']))
		{
			self::$_action = trim($_GET['act']);
			if (!isword(self::$_action))
			  die(self::$_lang['_SYS_LANG_URL_PARAMETER_VALUE_INVALID'].': '.self::$_action);
		}
	}

	
	/**
	* 解析URL为pathinfo模式，如果服务器不支持pathinfo模式，停止执行程序
	*/
	private static function url_pathinfo()
	{
		if ($_SERVER['REQUEST_URI'] != '/' && $_SERVER['REQUEST_URI'] != $_SERVER['PHP_SELF'] && !isset($_SERVER['PATH_INFO']))
		  die(self::$_lang['_SYS_LANG_NOT_SUPPORT_PATHINFO']);

		self::$_controller = 'Index';
		self::$_action = 'index';
		self::$_app = 'index';

		if (isset($_SERVER['PATH_INFO']))
		{
			$tmpArr = array_values(array_filter(explode('/', str_replace('=', '/', str_replace('&', '/', trim($_SERVER['PATH_INFO']))))));
			if (count($tmpArr) < 3)
			  die(self::$_lang['_SYS_LANG_URL_PARAMETER_ERROR']);

			self::$_app = trim($tmpArr[0]);
			self::$_controller = ucfirst(trim($tmpArr[1]));
			self::$_action = trim($tmpArr[2]);
			unset($tmpArr[0], $tmpArr[1], $tmpArr[2]);

			if (!isword(self::$_app))
			  die(self::$_lang['_SYS_LANG_URL_PARAMETER_VALUE_INVALID'].': '.self::$_app);

			//判断入口文件是否存在
			if (!is_file(str_replace(APP_NAME.'/', '', APP_PATH).self::$_app.'.php'))
			{
				if (DEBUG)
				  die(self::$_lang['_SYS_LANG_FILE_NOT_FIND'].': '.str_replace(APP_NAME.'/', '', APP_PATH).self::$_app.'.php');
				else
				  die(self::$_lang['_SYS_LANG_FILE_NOT_FIND']);
			}
			//end

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

	/**
	* 自动加载配置项
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

	/**
	* 自动加载语言包
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


	/**
	* 自动加载用户自定义的类库文件
	* @param string $cls 类名称
	* @return bool  成功返回true,失败返回false
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

	
	/**
	* 自动加载用户自定义的函数库文件
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

	/**
	* 该方法实现了自动缓存系统类库代码路径到内存
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
