<?php
/**
* 系统函数文件 
* @filename core.func.php
* @touch date 2014-07-23 16:14:39
* @author Rain<563268276@qq.com>
* @copyright 2014 http://www.94cto.com/
* @license http://www.apache.org/licenses/LICENSE-2.0   LICENSE-2.0
* @package Rain PHP Frame(RPF)
*/

defined('RPF_PATH') or exit();

/**
* get client IP if $num is true return int number else return ip address by string
* <code>
* $ip = getIp();
* </code>
* @param bool $num  一个标记位，如果值为true，则对IP地址使用ip2long进行返回，否则直接返回IP地址字符串
* @return bool / string
*/
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

/**
* this function use for setting cookie to client
* <code>
* setc('cookiename', 'cookievalue', 86400);
* </code>
* @param string $name cookie的名称
* @param string $value cookie的值
* @param int    $expire cookie的到期时间，单位：秒，默认86400秒
* @param string $path cookie的路径,默认值/
* @param string $domain cookie的域名，默认当前URL的域名
* @param bool $secure 是否HTTPS方式cookie设置,默认false
* @param bool $httponly 是否http only，默认true
* @return bool 成功返回true，失败返回false
*/
function setc($name, $value, $expire = null, $path = '/', $domain = null, $secure = false, $httponly = true)
{
	if (is_null($expire))
		$expire = 86400;
	if (is_null($domain) && isset($_SERVER['HTTP_HOST']))
		$domain = trim(str_ireplace('www.', '', $_SERVER['HTTP_HOST']));
	return setcookie($name, $value, time() + $expire, $path, $domain, $secure, $httponly);
}

/**
* this function use for getting cookie from client
* <code>
* echo getc('cookiename');
* </code>
* @param string $name cookie的名称
* @return string/bool 成功返回经过htmlspecialchars函数处理后的cookie值，失败返回false
*/
function getc($name)
{
	if (!isset($_COOKIE[$name]))
		return false;
	return htmlspecialchars($_COOKIE[$name]);
}

/**
* 发送邮件的函数
* <code>
* sendmail('563268276@qq.com', '测试标题', '<h2>hello world</h2>');
* </code>
* @param string $to 邮件接受方邮箱地址
* @param string $subject 邮件标题
* @param string $body 邮件内容
* @return bool 成功返回true，失败返回false
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

/**
* this function use for deleting cookie from client
* <code>
* delc('cookiename');
* </code>
* @param string $name cookie的名称
* @return bool 成功返回true，失败返回false
*/
function delc($name)
{
	return setcookie ($name, '', time() - 3600);
}

/**
* get path if parameter is true return url path else return file real path default true
* <code>
* echo getpath(APP_PATH, true);
* </code>
* @param string $path 路径
* @param bool   $p 如果是true，返回URL方式的绝对地址，如果是false，返回文件目录方式的绝对地址，默认true
* @return bool/string if some errors find return false else return string
*/
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

/**
* this function use for remove directories or files
* <code>
* rm(APP_PATH, true);
* </code>
* @param string $dir 路径
* @param bool   $deleteRootToo 如果是true，同时删除当前目录，否则不删除当前目录，默认false
* @return bool 成功返回true，失败返回false
*/
function rm($dir, $deleteRootToo = false)
{
	$dir = str_replace("\\", '/', $dir);
	if (is_file($dir) && file_exists($dir))
		return @unlink($dir);
	if (is_dir($dir))
		return unlinkRecursive($dir, $deleteRootToo);
}

/**
* Recursively delete a directory 此函数仅供系统内部调用，如需删除目录或文件，请使用rm函数
* @param string $dir Directory name
* @param boolean $deleteRootToo Delete specified top-level directory as well default value false
* @return bool 成功返回true，失败返回false
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

/**
* 发送http头
* <code>
* send_http_status(200);
* </code>
* @param int $code http的状态码
* @return void
*/
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

/**
* 安全模式，过滤相关的全局数组参数和值包括$_REQUEST / $_POST / $_GET / $_COOKIE / $_SERVER, 开启安全模式后，系统自动调用此函数
* <code>
* safe();
* </code>
* @return void
*/
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

/**
* 返回格式化的字节单位值
* <code>
* echo echo_memory_usage(2054);
* </code>
* @param int $mem_usage 字节数
* @return string 格式化后字节数
*/
function echo_memory_usage($mem_usage)
{
	if ($mem_usage < 1024)
		 return $mem_usage." b";
	elseif ($mem_usage < 1048576)
		 return round($mem_usage/1024,2)." kb";
	else
	 return round($mem_usage/1048576,2)." mb";
}

/**
* 字符串测试
* <code>
* echo get_word('abc');
* </code>
* @param string $str 待测试字符串
* @param bool $chinese 是否允许中文，默认true表示允许
* @return string/bool 检测通过返回字符串本身，检测不通过，返回false
*/
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

/**
* 字符串测试是否为链接地址
* <code>
* echo get_link('http://www.baidu.com/');
* </code>
* @param string $str 待测试字符串
* @param bool $chinese 是否允许中文，默认true表示允许
* @return string/bool 检测通过返回字符串本身，检测不通过，返回false
*/
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

/**
* 检测验证码
* <code>
* check_code('code');
* </code>
* @param string $name 存放用户输入验证码的表单input的name值
* @return bool 检测通过返回true，检测失败返回false
*/
function check_code($name)
{
	if (!isset($_SESSION['code']))
		return false;
	$s_code = $_SESSION['code'];
	unset($_SESSION['code']);
	return (strtolower(trim($_REQUEST[$name])) == $s_code);
}

/**
* 判断get或post数据是否都传递
* <code>
* check_data(array('un', 'pw'), 'get');
* </code>
* @param array $data 需要检测的key名称
* @param array $type 参数的传输方式，可选值：get或post，默认post
* @return bool 检测通过返回true，检测失败返回false
*/
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
* 递归读取目录下所有文件(含目录全路径)自动过滤.和..
* <code>
* print_r(read_dir(APP_PATH));
* </code>
* @param array $dir 需要读取的目录路径
* @param array $clean 是否清除上次读取，默认true，此参数系统内部调用，无需修改
* @return bool/array 成功返回目录下所有文件一维数组，失败返回false
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
* 脚本退出时候执行,仅供系统调用，不提供对外调用
* <code>
* shutdown();
* </code>
* @return void
*/
function shutdown()
{
	debuginfo();
}

/**
* 输出调试信息，仅供系统内部调用，不对外公开调用
* <code>
* debuginfo();
* </code>
* @return void
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
* 文件包含函数
* <code>
* import($file);
* </code>
* @param string $file 需要的文件路径,是全路径
* @return 返回值同require函数
*/
function import($file)
{
	if (!is_file($file))
	  return false;
	$GLOBALS['_reqFile']++;
	return require_once($file);
}

/**
* 判断是否所单词，含字母/数字/下划线
* <code>
* echo isword('abc');
* </code>
* @param string $str 需要测试的字符串 类型: string
* @return bool 成功返回1，失败返回0
*/
function isword($str)
{
	return preg_match('/^(\w|\-)+$/', $str);
}

/**
* 递归创建目录
* <code>
* mkdirs(APP_PATH);
* </code>
* @param string/array $dir 需要递归创建的目录
* @return bool 成功返回true，失败返回false
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

/**
* 自动写入token到form表单,系统内部函数，不对外公开
* @param string $content 需要添加token的html字符串
* @return string 成功返回添加完成token的html字符串
*/
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

/**
* 自动根据URL路由模式，生成URL地址
* @param string $act 控制器和操作方法 例如 User/add
* @param array $param 传递参数的key=>value的键值对，可为空 例如 array('un' => 'rain', 'pw' => 123456)
* @param string $file 入口文件，不含.php扩展名，默认当前入口文件 例如 index
* @param string $domain 网址，默认当前host网址 例如 SITE_URL
* @return string/bool 成功返url字符串，失败返回false
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

/**
* 压缩html代码，仅供系统内部调用
* @param string $string 待压缩的代码字符串
* @return string 返回压缩完成的html字符串
*/
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

/**
* 创建目录，仅供系统内部调用
* @param string $dir 需要创建的目录
* @return bool 成功返回true，失败返回false
*/
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
