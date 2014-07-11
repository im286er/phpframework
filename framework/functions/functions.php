<?php
defined('IN_FW') or die('deny');

//some useful functions by Rain

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
		return str_replace(APP_PATH, SITE_URL, $path);
	}
	else
		return str_replace(SITE_URL, APP_PATH, $path);
}

//this function use for making directories
//if success return true else return false
//note: this function parameter need  the absolute address
function mkdirs($dir, $need_file = false, $mode = 0700)
{
	$dir = str_replace("\\", '/', $dir);
	if (is_dir($dir))
		return true;
	$dirArr = explode('/', $dir);
	$dirArr = array_filter($dirArr);
	if (!is_array($dirArr) || empty($dirArr))
		return true;
	$tmp = '';
	foreach ($dirArr as $k => $dir)
	{
		if (0 != ($k % 2))
			$tmp .= '/'.$dir.'/';
		else
			$tmp .= $dir;
		if (!is_dir($tmp))
		{
			$ret = @mkdir($tmp, $mode);
			if (!$ret)
			{
				unset($dirArr);
				return $ret;
			}
			else
			{
				if (substr($tmp, strlen($tmp) - 1) == '/')
					$f = $tmp.'index.html';
				else
					$f = $tmp.'/'.'index.html';

				if ((!file_exists($f) &&  !file_exists(RUNTIME_PATH.'build.lock')) || $need_file)
					file_put_contents($f, '');
			}
		}
	}
	unset($dirArr);
	return true;
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

//get configuration value
//success return value else return false
function C($key)
{
	static $arr = array();

	if (!file_exists(CONFIG_FILE))
		return false;

	if (!is_array($arr) || empty($arr))
		$arr = require(CONFIG_FILE);
	$tmpArr = explode('-', $key);
	$value = false;
	foreach ($tmpArr as $t)
	{
		if ((!isset($arr[$t]) && (false === $value)) || ((false !== $value) && !isset($value[$t])))
			return false;
		if (false === $value)
			$value = $arr[$t];
		else
			$value = $value[$t];
	}
	unset($arr, $tmpArr);
	return $value;
}

//safe model filter variable from $_REQUEST / $_POST / $_GET / $_COOKIE / $_SERVER
//default open safe model
function safe()
{
	if (!OPEN_SAFE_MODEL)
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

//if build success return true else return false
function build()
{
	if (file_exists(RUNTIME_PATH.'build.lock'))
		return true;
	if (!defined('APP_NAME') || !defined('APP_PATH'))
		return false;
	$path = str_replace("\\", '/', realpath(str_replace("\\", '/', APP_PATH)));
	if (!$path)
		return false;
	$ret = true;
	if (!is_dir($path.'/'.APP_NAME.'/Common'))
		$ret = mkdirs($path.'/'.APP_NAME.'/Common');
	if (!$ret)
		return false;
	if (!is_dir($path.'/'.APP_NAME.'/Static/js'))
		$ret = mkdirs($path.'/'.APP_NAME.'/Static/js');
	if (!$ret)
		return false;
	if (!is_dir($path.'/'.APP_NAME.'/Static/css'))
		$ret = mkdirs($path.'/'.APP_NAME.'/Static/css');
	if (!$ret)
		return false;
	if (!is_dir($path.'/'.APP_NAME.'/Static/images'))
		$ret = mkdirs($path.'/'.APP_NAME.'/Static/images');
	if (!$ret)
		return false;
	if (!is_dir($path.'/'.APP_NAME.'/Lib/Action'))
		$ret = mkdirs($path.'/'.APP_NAME.'/Lib/Action');
	if (!$ret)
		return false;
	if (!is_dir($path.'/'.APP_NAME.'/Lib/Model'))
		$ret = mkdirs($path.'/'.APP_NAME.'/Lib/Model');
	if (!$ret)
		return false;

	if (!is_dir($path.'/'.APP_NAME.'/uploads/'))
		$ret = mkdirs($path.'/'.APP_NAME.'/uploads/');
	if (!$ret)
		return false;

	if (!is_dir($path.'/'.APP_NAME.'/Lib/Class'))
		$ret = mkdirs($path.'/'.APP_NAME.'/Lib/Class');
	if (!$ret)
		return false;

	if (!is_dir($path.'/'.APP_NAME.'/Runtime/Cache'))
		$ret = mkdirs($path.'/'.APP_NAME.'/Runtime/Cache');
	if (!$ret)
		return false;
	if (!is_dir($path.'/'.APP_NAME.'/Runtime/Data'))
		$ret = mkdirs($path.'/'.APP_NAME.'/Runtime/Data');
	if (!$ret)
		return false;
	if (!is_dir($path.'/'.APP_NAME.'/Tpl'))
		$ret = mkdirs($path.'/'.APP_NAME.'/Tpl');
	if (!$ret)
		return false;
	file_put_contents(RUNTIME_PATH.'build.lock', '');
	return true;
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

//if success return true else return false
function import($file)
{
	if (file_exists($file))
	{
		$GLOBALS['_FileCount']++;
		compile($file);
		require_once($file);
		return true;
	}
	return false;
}

function getFileKey()
{
	$key = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
	if ($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		foreach ($_POST as $k => $v)
			$key .= $k;
	}
	else
	{
		foreach ($_GET as $k => $v)
			$key .= $k;
	}
	$key = md5($key);
	return $key;
}

function compile($file)
{
	if (APP_DEBUG || APP_NAME == 'admin')
		return;
	$key = getFileKey();
	$compiled_file2 = RUNTIME_CACHE.$key.'_pre.php';
	$compiled_file = RUNTIME_CACHE.$key.'_finish.php';
	if (file_exists($compiled_file) && filemtime($compiled_file) > filemtime($file))
		return;
	$extension = pathinfo($file, PATHINFO_EXTENSION);
	$content = file_get_contents($file);
	$content = str_replace("\r", '', $content);
	if ('php' == $extension)
		$content = str_replace("<?php\n", '', $content);
	file_put_contents($compiled_file2, $content, FILE_APPEND);
}

function debuginfo()
{
	if (!APP_DEBUG)
		return;
	if (!isset($_SERVER["HTTP_X_REQUESTED_WITH"]) || strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) != "xmlhttprequest")
		echo '<div style="clear:both;text-align:center">use time: '.($GLOBALS['_endTime'] - $GLOBALS['_beginTime']).' seconds<br/>memory use: '.echo_memory_usage($GLOBALS['_endUseMems'] - $GLOBALS['_startUseMems']).'<br/>SQL Counts: '.$GLOBALS['_SQLCount'].'<br/>require file counts: '.$GLOBALS['_FileCount'].'</div>';
}

//load system functions and user definition functions
function load_functions()
{
	if (is_dir(APP_COMMON))
	{
		if (!($dir = @opendir(APP_COMMON)))
			die('open User common function directory failed');
		while (false !== ($file = readdir($dir)))
		{
			if ($file != "." && $file != ".." && is_file(APP_COMMON.$file) && substr($file, strpos($file, '.')) == '.php')
				import(APP_COMMON.$file);
		}
		closedir($dir);
	}
}

function my_autoload($classname)
{
	$sys_class = FRAMEWORK_PATH.'Class/'.$classname.'.php';
	$user_class1 = APP_CLASS.$classname.'.php';
	$user_class2 = APP_ACTION.$classname.'.php';
	$user_class3 = APP_MODEL.$classname.'.php';
	if (!import($sys_class) && !import($user_class1) && !import($user_class2) && !import($user_class3))
		die('load class: '.$classname.' failed');
}

//if success return url else return false
function U($act, $param = null, $app = null, $domain = null)
{
	if (is_null($app))
		$app = APP_NAME;
	if (is_null($domain))
		$domain = SITE_URL;
	if (stripos($domain, 'http') === false)
		$domain = 'http://'.$domain;

	$act = trim($act);
	if (strlen($act) < 1)
		return false;
	$ret = $domain.$app.'/';
	$ret .= strtoupper(substr($act, 0, 1)).substr($act, 1).'/';
	if (is_array($param) && !empty($param))
	{
		foreach ($param as $k => $v)
			$ret .= urlencode($k).'/'.urlencode($v).'/';
	}
	$ret = substr($ret, 0, -1);
	return $ret.'.html';
}

function location($url = SITE_URL, $time = 0, $msg = '')
{
    $url = str_replace(array("\n", "\r"), '', $url);
    if (empty($msg))
        $msg = "系统将在{$time}秒之后自动跳转到{$url}！";
	$tpl = '
	<style type="text/css" rel="stylesheet">
	*{margin:0;padding:0}
	ul,li{list-style:none;}
	.line_box{text-align:center;border:1px solid #E9E9E9;padding:0;margin:30px 0 70px 0;position:relative;clear:both;zoom:1}
	.line_box h3{background:#F7F7F7;border-bottom:1px solid #E9E9E9;font-family:simSun;font-size:14px;font-weight:bold;height:28px;line-height:28px;overflow:hidden}
	.line_box h3 span.line{font-weight:normal;color:#999;background:0;float:none;padding:0;margin:0;border:0}
	.error_page h1{margin-bottom:35px;margin-top:50px;}
	.error_page li{margin-bottom:35px;}
	.line_box h3 strong{font-weight:normal;font-size:12px}
	.line_box h3 em{color:#999;font-weight:normal;font-size:12px}
	.line_box h3 em.hl{color:#c00}
	</style>
	<div class="line_box">
                <h3>
                    <span>信息提示</span></h3>
                <div class="error_page">
                    <h1>'.$msg.'</h1>
                    <ul>
					<li>系统将在'.$time.'秒之后自动跳转到'.$url.'
					，如果系统无跳转，请点击<a href="'.$url.'">返回</a>跳转回相应的页面</li>
                    </ul>
                </div>
                <div class="clear">
                </div>
	</div>
	';
	if (!headers_sent())
	{
        // redirect
        if (0 === $time) {
            header('Location: ' . $url);
		}
		else
		{
			header("Content-type: text/html; charset=utf-8");
            header("refresh:{$time};url={$url}");
            echo($tpl);
        }
        exit();
	}
	else
	{
        $str = "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' /><meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time != 0)
            $str .= $tpl;
        exit($str);
    }
}

function load_tpl($tpl, $open_token = true)
{
	$tpl = trim($tpl);
	if (!file_exists($tpl))
	{
		if (APP_DEBUG)
			die('template file: '.$tpl.' not exists. ');
		else
			die('template file not exists. ');
	}
	$cache_file = RUNTIME_CACHE.md5($tpl).'.php';
	if (!file_exists($cache_file) || filemtime($cache_file) < filemtime($tpl) || $open_token)
	{
		$content = file_get_contents($tpl);
		$content = str_replace("\r", '', $content);
		$content = str_replace("\n", '', $content);
		$token_key = substr(SITE_URL, 0, -1).$_SERVER['REQUEST_URI'];
		foreach ($_REQUEST as $k => $v)
		{
			if ($k == HIDDEN_TOKEN_NAME)
				continue;
			$token_key .= $k;
		}
		$token_key = md5($token_key);
		if ($open_token)
		{
			if (!isset($_SESSION[$token_key]) || !isset($_SESSION[HIDDEN_TOKEN_NAME]) || !isset($_SESSION[$_SESSION[HIDDEN_TOKEN_NAME]]))
			{
				$val = md5(microtime());
				if (!isset($_SESSION[HIDDEN_TOKEN_NAME]) || !isset($_REQUEST[HIDDEN_TOKEN_NAME]))
				{
					$_SESSION[HIDDEN_TOKEN_NAME] = $token_key;
				}
				$_SESSION[$token_key] = $val;
			}
			$content = preg_replace('/<form(.*?)>(.*?)<\/form>/i', '<form$1><input type="hidden" value="'.$_SESSION[$_SESSION[HIDDEN_TOKEN_NAME]].'" name="'.HIDDEN_TOKEN_NAME.'"/>$2</form>', $content);
		}

		//parse include
		/*
			如下
			<{include="Index/test"}> 这里的action是Index,对应的action的方法method是test，而且模板也是test.html
			<{include="Index/test/test_view"}> 这里的action是Index,对应的action的方法method是test，而且模板是test_view.html
		*/
		$ret = preg_match_all('/<\{\s*include\s*=\s*"(.*?)"\}>/i', $content, $match);
		if ($ret)
		{
			foreach ($match[1] as $k => $v)
			{
				$tArr = explode('/', $v);
				$tCount = count($tArr);
				if ($tCount == 3)
					$content = str_ireplace($match[0][$k], '<?php require_once(load_tpl(APP_TPL."'.$tArr[0].'".\'/\'."'.$tArr[2].'".\'.html\')); ?>', $content);
				elseif ($tCount == 2)
					$content = str_ireplace($match[0][$k], '<?php require_once(load_tpl(APP_TPL."'.$tArr[0].'".\'/\'."'.$tArr[1].'".\'.html\')); ?>', $content);
				unset($tArr);
			}
		}
		$content = preg_replace('/<\{\s*\$(.*?)\}>/i', '<?php echo \$${1}; ?>', $content);
		$content = preg_replace('/\{\s*u(.*?)\}/i', '<?php echo U${1}; ?>', $content);
		$content = preg_replace('/<\{\s*if\s*(.*?)\s*\}>/i', '<?php if(${1}) { ?>', $content);
		$content = preg_replace('/<\{\s*else\s*if\s*(.*?)\s*\}>/i', '<?php } elseif(${1}) { ?>', $content);
		$content = preg_replace('/<\{\s*else\s*\}>/i', '<?php } else { ?>', $content);
		$content = preg_replace('/<\{\s*\/if\s*\}>/i', '<?php } ?>', $content);
		$content = preg_replace('/<\{\s*loop(.*?)\s*\}>/i', '<?php foreach${1} { ?>', $content);
		$content = preg_replace('/<\{\s*\/loop\s*\}>/i', '<?php } ?>', $content);
		$content = preg_replace('/<\{\s*foreach(.*?)\s*\}>/i', '<?php foreach${1} { ?>', $content);
		$content = preg_replace('/<\{\s*\/foreach\s*\}>/i', '<?php } ?>', $content);
		$content = preg_replace('/<\{\s*(.*?)\}>/i', '<?php echo ${1}; ?>', $content);
		$content = compress_html($content);
		file_put_contents($cache_file, '<?php defined(\'IN_FW\') or die(\'deny\'); ?> '.$content);
	}
	return $cache_file;
}


function compress_html($string) {
    $string = str_replace("\r\n", '', $string);
    $string = str_replace("\n", '', $string);
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

function check_code($name)
{
	if (!isset($_SESSION['code']))
		return false;
	$s_code = $_SESSION['code'];
	unset($_SESSION['code']);
	return (strtolower(trim($_REQUEST[$name])) == $s_code);
}

function admin_md5($str)
{
	return md5(md5(md5(md5($str))));
}

function user_md5($str)
{
	return md5(md5((($str))));
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

function shutdown_function($req)
{
	$e = error_get_last();
	//remove some error like E_WARNING E_NOTICE and so on
	if (!is_null($e))
	{
		if (APP_DEBUG)
		{
			die('info: '.$e['message'].' , in file:'.$e['file'].' , line:'.$e['line']);
		}
		else if (in_array($e['type'], array(1,4, 16, 32, 64, 128, 256, 4096)))
		{
			header("Content-type: text/html; charset=utf-8");
			die('服务器异常，请稍后访问，或者通知服务器管理员，邮箱：'.ADMIN_EMAIL.' 谢谢合作！');
		}
	}
	//here to rename compile file
	$key = getFileKey();
	$compileed_file = RUNTIME_CACHE.$key.'_pre.php';
	$compileed_file2 = RUNTIME_CACHE.$key.'_finish.php';
	if (file_exists($compileed_file))
	{
		file_put_contents($compileed_file, "<?php\n".file_get_contents($compileed_file));
		rename($compileed_file, $compileed_file2);
	}
	$GLOBALS['_endTime'] = microtime(TRUE);
	if (MEMORY_LIMIT_ON) $GLOBALS['_endUseMems'] = memory_get_usage();
	if (APP_DEBUG)
		debuginfo();
}

function unlinkres($file)
{
	if (stripos($file, 'http') !== false)
		$file = getpath($file, false);
	if (file_exists($file) && is_file($file))
		return @unlink($file);
	return true;
}

//user log
function L($title, $content, $logtype = '系统日志', $level = 0)
{
	$_POST['subject'] = trim($title);
	$_POST['content'] = trim($content);
	$_POST['logtype'] = trim($logtype);
	$_POST['level'] = intval($level);
	LogModel::add();
}

//system log
function SL($title, $content, $logtype = '系统日志', $level = 0)
{
	$_POST['subject'] = trim($title);
	$_POST['content'] = trim($content);
	$_POST['logtype'] = trim($logtype);
	$_POST['level'] = intval($level);

	$mysql = Mysql::getInstance();
		$sql = "INSERT INTO ".C('db-pre').'log(subject,ip,content,logtype,level)VALUES(:subject,:ip,:content,:logtype,:level)';
		$data = array(
			':subject' => get_word($_POST['subject']),
			':content' => trim($_POST['content']),
			':ip' => trim(getIp()),
			':logtype' => get_word($_POST['logtype']),
			':level' => intval($_POST['level']),
		);
	return $mysql->query($sql, $data, true);
}

//pay log
function PL($content, $ordernum, $status, $ordertype, $money, $uid = null, $remark = '')
{
	$_POST['content'] = trim($content);
	$_POST['ordernum'] = trim($ordernum);
	$_POST['status'] = trim($status);
	$_POST['ordertype'] = trim($ordertype);
	$_POST['remark'] = trim($remark);
	if (is_null($uid))
		$uid = $_SESSION['userinfo']['id'];
	$_POST['uid'] = trim($uid);
	$_POST['money'] = trim($money);
	PaylogModel::add();
}


function sendmail($to,$subject = '',$body = '')
{
    $mail             = new PHPMailer();
    $mail->CharSet = "utf-8";
    $mail->IsSMTP();
    $mail->SMTPDebug  = 1;
    // 1 = errors and messages
    // 2 = messages only
    $mail->SMTPAuth   = true;                  // 启用 SMTP 验证功能
    //$mail->SMTPSecure = "ssl";                 // 安全协议，可以注释掉
    $mail->Host       = 'smtp.qq.com';      // SMTP 服务器
    $mail->Port       = 25;                   // SMTP服务器的端口号
    $mail->Username   = '563268276@qq.com';  // SMTP服务器用户名，PS：我乱打的
    $mail->Password   = 'rainWRRHH717';            // SMTP服务器密码
    $mail->SetFrom('563268276@qq.com', 'rain');
    $mail->Subject    = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($to, '');
    if(!$mail->Send()) {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
		return false;
    } else {
		return true;
    }
}
