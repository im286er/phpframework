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

class Lang
{
	/*
	 * 功能：将值设置到语言包内存中，不写入文件，只是临时存储
	 * 参数
	 * $key 名称key
	 * $val 语言包值
	 * 返回: void
	 */
	public static function set($key, $val)
	{
		Kernel::$_lang[$key] = $val;
	}

	/*
	 * 功能：获取语言包中的值
	 * 参数
	 * $key 语言包的名称key
	 * 返回: 成功返回值，失败返回false
	 */
	public static function get($key)
	{
		return isset(Kernel::$_lang[$key]) ? Kernel::$_lang[$key] : false;
	}

	/*
	 * 功能：删除语言包中的值
	 * 参数
	 * $key 语言包的名称key
	 * 返回: void
	 */
	public static function rm($key)
	{
		unset(Kernel::$_lang[$key]);
	}

	/*
	 * 功能：判断语言包中的值是否设置
	 * 参数
	 * $key 语言包的名称key
	 * 返回: 存在返回true，否则返回false
	 */
	public static function exist($key)
	{
		return isset(Kernel::$_lang[$key]) ? true : false;
	}
}
