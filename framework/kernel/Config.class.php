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

class Config
{
	/*
	 * 功能：将值设置到配置内存中，不写入文件，只是临时存储
	 * 参数
	 * $key 名称key
	 * $val 配置项的值
	 * 返回: void
	 */
	public static function set($key, $val)
	{
		Kernel::$_conf[$key] = $val;
	}

	/*
	 * 功能：获取配置中的值
	 * 参数
	 * $key 配置的名称key
	 * 返回: 成功返回值，失败返回false
	 */
	public static function get($key)
	{
		return isset(Kernel::$_conf[$key]) ? Kernel::$_conf[$key] : false;
	}

	/*
	 * 功能：删除配置中的值
	 * 参数
	 * $key 配置的名称key
	 * 返回: void
	 */
	public static function rm($key)
	{
		unset(Kernel::$_conf[$key]);
	}

	/*
	 * 功能：判断配置中的值是否设置
	 * 参数
	 * $key 配置的名称key
	 * 返回: 存在返回true，否则返回false
	 */
	public static function exist($key)
	{
		return isset(Kernel::$_conf[$key]) ? true : false;
	}
}
