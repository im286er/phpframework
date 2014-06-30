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

	public static function start()
	{
		//加载配置信息
		self::loadConf();
		//自动加载类库
		spl_autoload_register('Kernel::autoload');
		//自动加载用户自定义的函数库
		self::loadfunc();
	}

	/*
	  * 功能  ： 该方法实现了自动加载类库的功能，在使用类似new操作时候，将自动调用此方法
	  * 参数  ： $cls  类库class名称
	  * 返回  ： 加载类库成功返回import函数的返回值 ， 加载类库失败程序停止执行
	  * 说明  ： 在debug模式下，类库加载失败，程序会报错，并且停止运行， 非debug模式，程序仅仅写日志，并且跳转到公用的错误提示页面
	*/
	public static function autoload($cls)
	{
		$sysClassArr = self::sysClassCache();
		if (isset($sysClassArr[$cls]))
			return import($sysClassArr[$cls]);

		if (!self::loadUserClass($cls))
		{
		}
	}

	/*
	  * 功能  ： 实现URL的解析和分发
	  * 参数  ： void
	  * 返回  ： void
	*/
	private static function parseurl()
	{
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
	  * 功能  ： 自动加载用户自定义的类库文件
	  * 参数  ： $cls 需要加载的类名称
	  * 返回  ： 成功返回true,失败返回false
	  * 说明  ： 所有类定义扩展名必须符合相关类常量扩展名的定义
	*/
	private static function loadUserClass($cls)
	{
		static $extArr = array(
		);
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
