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

//get client IP if $num is true return int number else return ip address by string
//if invalid ip address return unknown
//note: this function maybe get Agent IP
function getIp($num = false)
{
	if (!isset($_SERVER['REMOTE_ADDR']))
		return 'unknown';
	else
	{
		$ip = trim($_SERVER['REMOTE_ADDR']);
		if (!ip2long($ip))
			return 'unknown';
		else
		{
			if ($num)
				return printf( '%u', ip2long($ip));
			else
				return $ip;
		}
	}
}

//this function use for setting cookie to client
//if set cookie success return true else return false
//default expire time one day
function setc($name, $value, $expire = null, $path = '/', $domain = null, $secure = false, $httponly = true)
{
	if (is_null($expire))
		$expire = 86400;
	if (is_null($domain) && isset($_SERVER['HTTP_HOST']))
		$domain = trim(str_ireplace('www.', '', $_SERVER['HTTP_HOST']));
	return setcookie($name, $value, time() + $expire, $path, $domain, $secure, $httponly);
}

//this function use for getting cookie from client
//if get cookie success return the value of the cookie else return false
//note: this function will use  htmlspecialchars function
function getc($name)
{
	if (!isset($_COOKIE[$name]))
		return false;
	return htmlspecialchars($_COOKIE[$name]);
}

/**
 * 功能: 发送邮件的函数
 * 参数说明如下
 * $to 接受邮件的email地址
 * $subject 邮件的标题
 * $body    邮件的内容
 */
function sendmail($to, $subject = '', $body = '')
{
    $mail             = new PHPMailer();
    $mail->CharSet = EMAIL_CHARSET;
    $mail->IsSMTP();
    $mail->SMTPDebug  = EMAIL_DEBUG;
    $mail->SMTPAuth   = EMAIL_SMTP;                  // 启用 SMTP 验证功能
	if (EMAIL_SSL)
		$mail->SMTPSecure = "ssl";                 // 安全协议，可以注释掉
    $mail->Host       = EMAIL_HOST;      // SMTP 服务器
    $mail->Port       = EMAIL_PORT;                   // SMTP服务器的端口号
    $mail->Username   = EMAIL_USER;  // SMTP服务器用户名，PS：我乱打的
    $mail->Password   = EMAIL_PWD;            // SMTP服务器密码
    $mail->SetFrom(EMAIL_USER, EMAIL_NAME);
    $mail->Subject    = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($to, '');
    if (!$mail->Send())
	{
		if (EMAIL_DEBUG)
			echo 'Mailer Error: ' . $mail->ErrorInfo;
		return false;
    }
	return true;
}

//this function use for deleting cookie from client
//if delete cookie success return true else return false
function delc($name)
{
	return setcookie ($name, '', time() - 3600);
}

//get path if parameter is true return url path else return file real path default true
//if some errors find return false else return string
function getpath($path, $p = true)
{
	if ($p)
	{
		if (!is_dir($path) && !is_file($path))
			return false;
		return str_replace(str_replace(APP_NAME.'/', '', APP_PATH), SITE_URL, $path);
	}
	return str_replace(SITE_URL, str_replace(APP_NAME.'/', '', APP_PATH), $path);
}

//this function use for remove directories or files
//note: this function parameter need  the absolute address
function rm($dir, $deleteRootToo = false)
{
	$dir = str_replace("\\", '/', $dir);
	if (is_file($dir) && file_exists($dir))
		return @unlink($dir);
	if (is_dir($dir))
		return unlinkRecursive($dir, $deleteRootToo);
}

/**
  * Recursively delete a directory
  *
  * @param string $dir Directory name
  * @param boolean $deleteRootToo Delete specified top-level directory as well default value false
*/
function unlinkRecursive($dir, $deleteRootToo = false)
{
     if (!$dh = @opendir($dir))
         return false;
     while (false !== ($obj = readdir($dh)))
     {
        if($obj == '.' || $obj == '..') 
            continue;
        if (!@unlink($dir . '/' . $obj))
             unlinkRecursive($dir.'/'.$obj, $deleteRootToo);
     }
     closedir($dh);
     if ($deleteRootToo)
         return @rmdir($dir);
     return true;
}

function send_http_status($code)
{
    static $_status = array(
        // Success 2xx
        200 => 'OK',
        // Redirection 3xx
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ',  // 1.1
        // Client Error 4xx
        400 => 'Bad Request',
        403 => 'Forbidden',
        404 => 'Not Found',
        // Server Error 5xx
        500 => 'Internal Server Error',
        503 => 'Service Unavailable',
    );
	if (isset($_status[$code]))
	{
        header('HTTP/1.1 '.$code.' '.$_status[$code]);
        header('Status:'.$code.' '.$_status[$code]);
    }
}

//safe model filter variable from $_REQUEST / $_POST / $_GET / $_COOKIE / $_SERVER
//default open safe model
function safe()
{
	if (!SAFE_MODEL)
		return;

	if (is_array($_REQUEST) && !empty($_REQUEST))
	{
		foreach ($_REQUEST as $k => $v)
		{
			$is_get = isset($_GET[$k]) ? true : false;
			$is_post = isset($_POST[$k]) ? true : false;
			$v = trim($v);
			unset($_REQUEST[$k], $_GET[$k], $_POST[$k]);
			$k = trim($k);
			$k = urldecode($k);
			$v = urldecode($v);
			$k = html_entity_decode($k);
			$v = html_entity_decode($v);

			if ($k != addslashes($k) || $k != strip_tags($k) || htmlspecialchars($k) != $k || (strpos($k, '%') !== false) || (strpos($k, "\\") !== false))
				die('you are too young too simple, you ip:'.getIp());

			//make sure $v do not have any html or js or php code
			preg_match_all('/\[code\](.*?)\[\/code\]/i', $v, $match);
			if (isset($match[1]) && is_array($match[1]) && !empty($match[1]))
			{
				foreach ($match[1] as $m1)
				{
					$v = str_replace($m1, htmlspecialchars($m1), $v);
					$v = str_ireplace('[code]', '[code]', $v);
					$v = str_ireplace('[/code]', '[/code]', $v);
				}
			}
			$v = strip_tags($v);
			
			if ($is_get)
				$_GET[$k] = $v;
			if ($is_post)
				$_POST[$k] = $v;
			$_REQUEST[$k] = $v;
		}
	}

	foreach ($_SERVER as $k => $v)
	{
		if (!is_scalar($v))
			continue;
		$v = trim($v);
		$k = trim($k);

		if ($k != addslashes($k) || $k != strip_tags($k) || htmlspecialchars($k) != $k || (strpos($k, '%') !== false))
			die('you are too young too simple, you ip:'.getIp());
	}

	if (is_array($_COOKIE) && !empty($_COOKIE))
	{
		foreach ($_COOKIE as $k => $v)
		{
			$v = trim($v);
			unset($_COOKIE[$k]);
			$k = trim($k);
			$k = urldecode($k);
			$v = urldecode($v);

			$k = html_entity_decode($k);
			$v = html_entity_decode($v);

			if ($k != addslashes($k) || $k != strip_tags($k) || htmlspecialchars($k) != $k || (strpos($k, '%') !== false))
				die('you are too young too simple, you ip:'.getIp());

			//make sure $v do not have any html or js or php code
			$v = strip_tags($v);
			
			$_COOKIE[$k] = $v;
		}
	}
}

function echo_memory_usage($mem_usage)
{
	if ($mem_usage < 1024)
		 return $mem_usage." b";
	elseif ($mem_usage < 1048576)
		 return round($mem_usage/1024,2)." kb";
	else
	 return round($mem_usage/1048576,2)." mb";
}

//check ok return $str else return false
function get_word($str, $chinese = true)
{
	if ($chinese)
	{
		if (preg_match('/^[\x{4e00}-\x{9fa5}A-Za-z0-9_,\s]+$/u', $str))
			return $str;
		else
			return false;
	}
	else
	{
		if (preg_match('/^[A-Za-z0-9_,\s]+$/i', $str))
			return $str;
		else
			return false;
	}
}

//check ok return $str else return false
function get_link($str, $chinese = true)
{
	if ($chinese)
	{
		if (preg_match('/^[\x{4e00}-\x{9fa5}A-Za-z0-9_\-\:\.\%\#\@\!\&\*\+\?\,\/]+$/u', $str))
			return $str;
		else
			return false;
	}
	else
	{
		if (preg_match('/^[A-Za-z0-9_\-\:\.\%\#\@\!\&\*\+\?\,\/]+$/i', $str))
			return $str;
		else
			return false;
	}
}

//检查验证码
function check_code($name)
{
	if (!isset($_SESSION['code']))
		return false;
	$s_code = $_SESSION['code'];
	unset($_SESSION['code']);
	return (strtolower(trim($_REQUEST[$name])) == $s_code);
}

function check_data($data, $type = 'post')
{
	if ('post' == $type)
	{
		foreach ($data as $v)
		{
			if (!isset($_POST[$v]))
				return false;
		}
	}
	else
	{
		foreach ($data as $v)
		{
			if (!isset($_GET[$v]))
				return false;
		}
	}
	return true;
}

/**
   * 功能: 递归读取目录下所有文件(含目录全路径)自动过滤.和..
   * 参数: $dir 目录路径 类型: string
   * 返回: 成功返回目录一维数组，失败返回false
*/
function read_dir($dir, $clean = true)
{
	static $dirArr = array();
	if ($clean)
		$dirArr = array();

	$dir = trim($dir);

	if (!is_dir($dir))
	  return false;

	//补全后面的/
	if (substr($dir, -1) != '/')
	  $dir .= '/';

	if ($dh = opendir($dir))
	{
		while (($file = readdir($dh)) !== false)
		{
			if ('.' == $file || '..' == $file)
			  continue;
			if (is_file($dir.$file))
			  $dirArr[] = $dir.$file;
			else
			   read_dir($dir.$file.'/', false);
		}
		closedir($dh);
	}
	return $dirArr;
}

/**
   * 功能: 脚本退出时候执行
   * 参数: void
   * 返回: void
   * 说明: 对于基于jquery的ajax请求不输出调试信息
*/
function shutdown()
{
	debuginfo();
}

/**
   * 功能: 输出调试信息
   * 参数: void
   * 返回: void
*/
function debuginfo()
{
	if (!DEBUG)
	  return;
	if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
	  return;

	$html = '
			<pre>
						 use Memory:'.(echo_memory_usage(memory_get_usage() - $GLOBALS['_sMem'])).'
						 use Time:'.(microtime(true) - $GLOBALS['_bTime']).'
						 required files counts:'.$GLOBALS['_reqFile'].'
						 execute SQL counts:'.$GLOBALS['_sqlCount'].'
						 made by Rain, Rain php framework version: '.RPF_VERSION.'
						 web site: www.94cto.com/ www.itziy.com/ www.coder100.com
			</pre> ';
	echo $html;
}

/**
   * 功能: 包含文件，实现同require，只是做了包含文件个数统计
   * 参数: $file 需要包含的文件 类型: string
   * 返回: 成功返回同require，失败返回false
*/
function import($file)
{
	if (!is_file($file))
	  return false;
	$GLOBALS['_reqFile']++;
	return require_once($file);
}

/**
   * 功能: 判断是否所单词，含字母/数字/下划线
   * 参数: $str 需要测试的字符串 类型: string
   * 返回: 成功返回1，失败返回0
*/
function isword($str)
{
	return preg_match('/^(\w|\-)+$/', $str);
}

/**
   * 功能: 递归创建目录
   * 参数: $dir 需要递归创建的目录 类型: string / array
   * 返回: 成功返回true，失败返回false
*/
function mkdirs($dir)
{
	if (empty($dir))
	  return false;

	if (is_array($dir))
	{
		foreach ($dir as $d)
			mkdir2($d);
	}
	elseif (is_string($dir))
	{
		$dir = str_replace("\\", '/', $dir);
		mkdir2($dir);
	}
	return false;
}

//自动写入token到form表单
function callback($content)
{
	$content = str_replace("\r", '', $content);
	$content = str_replace("\n", '', $content);
	$token_key = substr(SITE_URL, 0, -1).$_SERVER['REQUEST_URI'];
	foreach ($_REQUEST as $k => $v)
	{
		if ($k == TOKEN_NAME)
			continue;
		$token_key .= $k;
	}
	$token_key = md5($token_key);

	if (!isset($_SESSION[$token_key]) || !isset($_SESSION[TOKEN_NAME]) || !isset($_SESSION[$_SESSION[TOKEN_NAME]]))
	{
		$val = md5(microtime().rand());
		if (!isset($_SESSION[TOKEN_NAME]) || !isset($_REQUEST[TOKEN_NAME]))
		{
			$_SESSION[TOKEN_NAME] = $token_key;
		}
		$_SESSION[$token_key] = $val;
	}
	$content = preg_replace('/<form(.*?)>(.*?)<\/form>/i', '<form$1><input type="hidden" value="'.$_SESSION[$_SESSION[TOKEN_NAME]].'" 		name="'.TOKEN_NAME.'"/>$2</form>', $content);
	chdir(dirname($_SERVER['SCRIPT_FILENAME']));
	if (!DEBUG)
	  $content = compress_html($content);
	return $content;
}

//if success return url else return false
/**
 * 功能: 自动根据URL路由模式，生成URL地址
 * 参数说明
 * $act 控制器和操作方法 例如 User/add
 * $param 传递参数的key=>value的键值对，可为空
 * $file  入口文件，不含.php扩展名，默认当前入口文件
 * $domain 网址，默认当前host网址
 */
function U($act, $param = null, $file = null, $domain = null)
{
	if (empty($domain))
		$domain = SITE_URL;
	if (stripos($domain, 'http') === false)
		$domain = 'http://'.$domain;
	if (substr($domain, -1) != '/')
		$domain .= '/';

	if (empty($file))
	{
		$file = str_ireplace('/', '', $_SERVER['SCRIPT_NAME']);
		$file = str_ireplace('.php', '',$file);
	}

	switch (Kernel::$_conf['URL_MODEL'])
	{
		case URL_COMMON:              //普通URL模式
			$actArr = explode('/', $act);
			$ret = $domain.$file.'.php?app='.$file.'&con='.ucfirst($actArr[0]).'&act='.strtolower($actArr[1]);
			if (is_array($param) && !empty($param))
			{
				$ret .= '&';
				foreach ($param as $k => $v)
					$ret .= urlencode($k).'='.urlencode($v).'&';
				$ret = substr($ret, 0, -1);
			}
			break;
		case URL_PATHINFO:            //PATHINFO模式
			$actArr = explode('/', $act);
			$ret = $domain.$file.'.php/'.$file.'/'.ucfirst($actArr[0]).'/'.strtolower($actArr[1]);
			if (is_array($param) && !empty($param))
			{
				$ret .= '/';
				foreach ($param as $k => $v)
					$ret .= urlencode($k).'/'.urlencode($v).'/';
				$ret = substr($ret, 0, -1);
			}
			break;
		case URL_REWRITE:             //REWRITE模式
			$actArr = explode('/', $act);
			$ret = $domain.$file.'/'.ucfirst($actArr[0]).'/'.strtolower($actArr[1]);
			if (is_array($param) && !empty($param))
			{
				$ret .= '/';
				foreach ($param as $k => $v)
					$ret .= urlencode($k).'/'.urlencode($v).'/';
				$ret = substr($ret, 0, -1);
			}
			break;
		default:                      //默认使用兼容模式URL_COMPAT
			$actArr = explode('/', $act);
			$ret = $domain.$file.'.php?s='.$file.'/'.ucfirst($actArr[0]).'/'.strtolower($actArr[1]);
			if (is_array($param) && !empty($param))
			{
				$ret .= '/';
				foreach ($param as $k => $v)
					$ret .= urlencode($k).'/'.urlencode($v).'/';
				$ret = substr($ret, 0, -1);
			}
	}

	return $ret;
}

//html代码压缩功能
function compress_html($string) {
    $string = str_replace("\r\n", '', $string);
    $string = str_replace("\n", '', $string);
    $string = str_replace("\r", '', $string);
    $string = str_replace("\t", '', $string);
	$pattern = array (
                    "/[\s]+/",
                    "/<!--[\\w\\W\r\\n]*?-->/",
                    "'/\*[^*]*\*/'"
                    );
    $replace = array (
                    " ",
                    "",
                    ""
                    );
    return preg_replace($pattern, $replace, $string);
}

function mkdir2($dir)
{
	if (!is_dir($dir))
	{
		if (!mkdir2(dirname($dir)))
		{
			return false;
		}
		if (!mkdir($dir, 0777))
		{
			return false;
		}
	}
	return true;
}
