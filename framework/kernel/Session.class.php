<?php
/**
* SESSION的操作类 
* @filename Session.class.php
* @touch date 2014-07-24 09:16:18
* @author Rain<563268276@qq.com>
* @copyright 2014 http://www.94cto.com/
* @license http://www.apache.org/licenses/LICENSE-2.0   LICENSE-2.0
* @package Rain PHP Frame(RPF)
*/

/**
* Session的操作处理类，提供各种session相关的操作处理
*/
class Session
{
	/**
	* 将值设置到session中
	* <code>
	* Session::set('s_key', '123');
	* </code>
	* @param string $key  key
	* @param string  $val value
	* @return void 
	*/
	public static function set($key, $val)
	{
		$_SESSION[$key] = $val;
	}

	/**
	* 获取session中的值
	* <code>
	* Session::get('s_key');
	* </code>
	* @param string $key  key
	* @return  string|bool 如果存在返回值，否则返回false
	*/
	public static function get($key)
	{
		return isset($_SESSION[$key]) ? $_SESSION[$key] : false;
	}

	/**
	* 删除session中的值
	* <code>
	* Session::rm('s_key');
	* </code>
	* @param string $key  key
	* @return  void
	*/
	public static function rm($key)
	{
		unset($_SESSION[$key]);
	}

	/**
	* 判断session中的值是否设置
	* <code>
	* Session::exist('s_key');
	* </code>
	* @param string $key  key
	* @return bool 成功返回true，失败返回false
	*/
	public static function exist($key)
	{
		return isset($_SESSION[$key]) ? true : false;
	}

	/**
	* 清除所有的session
	* <code>
	* Session::clean();
	* </code>
	* @return bool 成功返回true，失败返回false
	*/
	public static function clean()
	{
		return session_destroy();
	}

	/**
	* 设置或读取session的有效期，默认值180分钟
	* <code>
	* Session::expire();
	* </code>
	* @param int $min  设置session有效期，单位：分钟
	* @return int 设置或读取的session的有效期
	*/
	public static function expire($min = null)
	{
		return is_null($min) ? session_cache_expire() : session_cache_expire($min);
	}

	/**
	* 设置或读取session id
	* <code>
	* Session::sid();
	* </code>
	* @param string $id  设置session id
	* @return string 设置或读取的session的id
	*/
	public static function sid($id = null)
	{
		return is_null($id) ? session_id() : session_id($id);
	}
	
	/**
	* 设置或读取session name
	* <code>
	* Session::name();
	* </code>
	* @param string $name  设置session name
	* @return string 设置或读取的session的name
	*/
	public static function name($name = null)
	{
		return is_null($name) ? session_name() : session_name($name);
	}

	/**
	* 设置或读取session 保存路径
	* <code>
	* Session::path();
	* </code>
	* @param string $path  设置session path 
	* @return string 设置或读取的session的路径
	*/
	public static function path($path = null)
	{
		return is_null($path) ? session_save_path() : session_save_path($path);
	}

	/**
	* 获取session当前的状态
	* <code>
	* Session::status();
	* </code>
	* @return int 返回当前session状态的int值
	*/
	public static function status()
	{
		return session_status();
	}
}
