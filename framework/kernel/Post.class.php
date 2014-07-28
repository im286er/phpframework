<?php
/**
* 全局变量，$_POST的操作类
* @filename Post.class.php
* @touch date 2014-07-28 10:32:15
* @author Rain<563268276@qq.com>
* @copyright 2014 http://www.94cto.com/
* @license http://www.apache.org/licenses/LICENSE-2.0   LICENSE-2.0
* @package Rain PHP Frame(RPF)
*/

/**
* 全局变量，$_POST的操作类
*/
class Post
{
	/**
	* 将值设置到$_POST中
	* <code>Post::set('s_key', '123');</code>
	* @param string $key  key
	* @param string  $val value
	* @return void 
	*/
	public static function set($key, $val)
	{
		$_POST[$key] = $val;
	}

	/**
	* 获取$_POST中的值
	* <code>Post::get('s_key');</code>
	* @param string $key  key
	* @return  string|bool 如果存在返回值，否则返回false
	*/
	public static function get($key)
	{
		return isset($_POST[$key]) ? $_POST[$key] : false;
	}

	/**
	* 删除$_POST中的值
	* <code>Post::rm('s_key');</code>
	* @param string $key  key
	* @return  void
	*/
	public static function rm($key)
	{
		unset($_POST[$key]);
	}

	/**
	* 判断$_POST中的值是否设置
	* <code>Post::exist('s_key');</code>
	* @param string $key  key
	* @return bool 成功返回true，失败返回false
	*/
	public static function exist($key)
	{
		return isset($_POST[$key]) ? true : false;
	}
}
