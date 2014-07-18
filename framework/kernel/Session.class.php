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

class Session
{
	/*
	 * 功能：将值设置到session中
	 * 参数
	 * $key session的名称key
	 * $val session的值
	 * 返回: void
	 */
	public static function set($key, $val)
	{
		$_SESSION[$key] = $val;
	}

	/*
	 * 功能：获取session中的值
	 * 参数
	 * $key session的名称key
	 * 返回: 成功返回值，失败返回false
	 */
	public static function get($key)
	{
		return isset($_SESSION[$key]) ? $_SESSION[$key] : false;
	}

	/*
	 * 功能：删除session中的值
	 * 参数
	 * $key session的名称key
	 * 返回: void
	 */
	public static function rm($key)
	{
		unset($_SESSION[$key]);
	}

	/*
	 * 功能：判断session中的值是否设置
	 * 参数
	 * $key session的名称key
	 * 返回: 存在返回true，否则返回false
	 */
	public static function exist($key)
	{
		return isset($_SESSION[$key]) ? true : false;
	}

	/*
	 * 功能：清除所有的session
	 * 参数：void
	 * 返回:成功返回true，失败返回false
	 */
	public static function clean()
	{
		return session_destroy();
	}

	/*
	 * 功能：设置或读取session的有效期，默认值180分钟
	 * 参数：$min 设置新的有效期，单位：分钟，默认null
	 * 返回:设置或读取的分钟值
	 */
	public static function expire($min = null)
	{
		return is_null($min) ? session_cache_expire() : session_cache_expire($min);
	}

	/*
	 * 功能：设置或读取session id
	 * 参数：$id 设置新的session id
	 * 返回:设置或读取的session id
	 */
	public static function sid($id = null)
	{
		return is_null($id) ? session_id() : session_id($id);
	}

	/*
	 * 功能：设置或读取session name
	 * 参数：$name 设置新的session name
	 * 返回:设置或读取的session name
	 */
	public static function name($name = null)
	{
		return is_null($name) ? session_name() : session_name($name);
	}

	/*
	 * 功能：设置或读取session 保存路径
	 * 参数：$path 设置新的session路径 
	 * 返回:设置或读取的session path
	 */
	public static function path($path = null)
	{
		return is_null($path) ? session_save_path() : session_save_path($path);
	}

	/*
	 * 功能：获取session当前的状态
	 * 参数： void
	 * 返回:返回当前session状态的int值
	 */
	public static function status()
	{
		return session_status();
	}
}
