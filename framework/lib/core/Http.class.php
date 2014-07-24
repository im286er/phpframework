<?php
/**
* HTTP的操作响应类库，主要用来发送HTTP请求必须支持CURL，希望使用socket模式请用，第三方类库包HttpClient类
* @filename Http.class.php
* @touch date 2014-07-24 13:57:25
* @author Rain<563268276@qq.com>
* @copyright 2014 http://www.94cto.com/
* @license http://www.apache.org/licenses/LICENSE-2.0   LICENSE-2.0
* @package Rain PHP Frame(RPF)
*/

defined('RPF_PATH') or exit();

/**
* HTTP的操作响应类库，主要用来发送HTTP请求必须支持CURL，希望使用socket模式，请用第三方类库包HttpClient类
*/
class Http
{
	/**
	* 使用HTTP POST方式发送请求包
	* @param string $url  接受POST请求的URL地址
	* @param array $data  POST的数据,可选参数，默认null
	* @param array $conf  配置信息,向含相应的固定KEY的一维数组，固定key说明如下：array('head' => array(http请求头信息), 'model' => 'curl', 'return' => 'all', 'timeout' => 60) ,head对应http请求发送的额外数据，参数值必须所数组，model是代表发送的方式未指定优先选择curl，return设置为all或者未设置则返回http的响应的head和body两个部分，只要body设置return的值为body，只要head部分，设置return的值为head，不想要返回设置为none，timeout设置超时时间,单位是秒，默认60秒,可选参数，默认null
	* @return bool|string 成功返回结果，失败返回false
	*/
	public static function post($url, $data = null, $conf = null)
	{
		if (empty($data))
		  $data = array();

		$conf = self::init($conf);
		if ($conf['model'] == 'curl')
		{
			$ch = curl_init($url);
			if (false === $ch)
			  return $ch;
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_TIMEOUT, $conf['timeout']);
			if (!empty($conf['head']))
				curl_setopt($ch, CURLOPT_HTTPHEADER, $conf['head']);
			if ($conf['return'] == 'none' || $conf['return'] == 'body')
				curl_setopt ($ch, CURLOPT_HEADER, false);
			else
				curl_setopt ($ch, CURLOPT_HEADER, true);
			if ($conf['return'] == 'none')
				curl_setopt($ch,  CURLOPT_RETURNTRANSFER, false);
			else
				curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);
			$ret = curl_exec($ch);
			curl_close($ch);
			unset($ch);
			if ($conf['return'] != 'none')
			  return $ret;
		}
	}

	/**
	* 使用HTTP GET方式发送请求包
	* @param string $url  接受GET请求的URL地址
	* @param array $data  GET的数据,可选参数，默认null
	* @param array $conf  配置信息,向含相应的固定KEY的一维数组，固定key说明如下：array('head' => array(http请求头信息), 'model' => 'curl', 'return' => 'all', 'timeout' => 60) ,head对应http请求发送的额外数据，参数值必须所数组，model是代表发送的方式未指定优先选择curl，return设置为all或者未设置则返回http的响应的head和body两个部分，只要body设置return的值为body，只要head部分，设置return的值为head，不想要返回设置为none，timeout设置超时时间,单位是秒，默认60秒,可选参数，默认null
	* @return bool|string 成功返回结果，失败返回false
	*/
	public static function get($url, $data = null, $conf = null)
	{
		if (empty($data))
		  $data = array();

		$conf = self::init($conf);
		if (!empty($data))
		{
			if (strpos($url, '?') === false)
			  $url .= '?';
			else
			  $url .= '&';
			foreach ($data as $k => $v)
			{
				$k = urldecode($k);
				$v = urldecode($v);
				$url .= urlencode($k).'='.urlencode($v).'&';
			}
			$url = substr($url, 0, -1);
		}
		if ($conf['model'] == 'curl')
		{
			$ch = curl_init($url);
			if (false === $ch)
			  return $ch;
			curl_setopt($ch, CURLOPT_POST, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, $conf['timeout']);
			if (!empty($conf['head']))
				curl_setopt($ch, CURLOPT_HTTPHEADER, $conf['head']);
			if ($conf['return'] == 'none' || $conf['return'] == 'body')
				curl_setopt ($ch, CURLOPT_HEADER, false);
			else
				curl_setopt ($ch, CURLOPT_HEADER, true);
			if ($conf['return'] == 'none')
				curl_setopt($ch,  CURLOPT_RETURNTRANSFER, false);
			else
				curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);
			$ret = curl_exec($ch);
			curl_close($ch);
			unset($ch);
			if ($conf['return'] != 'none')
			  return $ret;
		}
	}

	/**
	* 初始化配置信息，包括head头，请求发送模式model，返回方式return,该方法仅供内部调用
	* @param  array $conf 配置信息
	* @return void
	*/
	private static function init($conf)
	{
		$conf['head'] = isset($conf['head']) ? $conf['head'] : array();
		$conf['timeout'] = isset($conf['timeout']) ? intval($conf['timeout']) : 60;
		$conf['model'] = (isset($conf['model']) && in_array($conf['model'], array('curl'))) ? $conf['model'] : 'curl';
		$conf['return'] = (isset($conf['return']) && in_array($conf['return'], array('none', 'all', 'body', 'head'))) ? $conf['return'] : 'all';
		if ($conf['model'] == 'curl' && function_exists('curl_init'))
		{
			$conf['model'] = 'curl';
		}
		elseif ($conf['model'] == 'socket' && function_exists('fsockopen'))
		{
			$conf['model'] = 'socket';
		}
		elseif (function_exists('curl_init'))
		{
			$conf['model'] = 'curl';
		}
		elseif (function_exists('fsockopen'))
		{
			$conf['model'] = 'socket';
		}
		else
		{
			die(Lang::get('_SYS_LANG_EXT_NOT_FIND').' socket and curl not support');
		}
		return $conf;
	}
}
