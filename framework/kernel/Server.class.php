<?php
/**
* 全局变量，$_SERVER的操作类
* @filename Server.class.php
* @touch date 2014-07-28 10:39:12
* @author Rain<563268276@qq.com>
* @copyright 2014 http://www.94cto.com/
* @license http://www.apache.org/licenses/LICENSE-2.0   LICENSE-2.0
* @package Rain PHP Frame(RPF)
*/

/**
* 全局变量，$_SERVER的操作类
*/
class Server
{
	/**
	* 将值设置到$_SERVER中
	* <code>Server::set('s_key', '123');</code>
	* @param string $key  key
	* @param string  $val value
	* @return void 
	*/
	public static function set($key, $val)
	{
		$_SERVER[$key] = $val;
	}

	/**
	* 获取$_SERVER中的值
	* <code>Server::get('s_key');</code>
	* @param string $key  key
	* @return  string|bool 如果存在返回值，否则返回false
	*/
	public static function get($key)
	{
		return isset($_SERVER[$key]) ? $_SERVER[$key] : false;
	}

	/**
	* 删除$_SERVER中的值
	* <code>Server::rm('s_key');</code>
	* @param string $key  key
	* @return  void
	*/
	public static function rm($key)
	{
		unset($_SERVER[$key]);
	}

	/**
	* 判断$_SERVER中的值是否设置
	* <code>Server::exist('s_key');</code>
	* @param string $key  key
	* @return bool 成功返回true，失败返回false
	*/
	public static function exist($key)
	{
		return isset($_SERVER[$key]) ? true : false;
	}
}
