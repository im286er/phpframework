<?php
/**
* 系统的初始化入口文件
* @filename init.php
* @touch date 2014-07-23 16:15:22
* @author Rain<563268276@qq.com>
* @copyright 2014 http://www.94cto.com/
* @license http://www.apache.org/licenses/LICENSE-2.0   LICENSE-2.0
* @package Rain PHP Frame(RPF)
*/

// 检测PHP环境
//php 5.3.7 之后PDO的支持才完美,所以框架限制php版本>=5.3.7
if (version_compare(PHP_VERSION,'5.3.7','<'))  die('require PHP > 5.3.7 !');

/**
*  @global int  $GLOBALS['_bTime'] 记录开始运行时间
*/
$GLOBALS['_bTime'] = microtime(true);

/**
*  MEMORY_LIMIT_ON 是否支持获取内存使用量
*/
define('MEMORY_LIMIT_ON', function_exists('memory_get_usage'));

if (MEMORY_LIMIT_ON) $GLOBALS['_sMem'] = memory_get_usage();

date_default_timezone_set('PRC');

/**
*  @global int $GLOBALS['_reqFile'] 记录文件加载个数,初始值为3
*/
$GLOBALS['_reqFile'] = 3;

/**
*  @global int $GLOBALS['_sqlCount'] 记录执行的SQL的条数
*/
$GLOBALS['_sqlCount'] = 0;

/**
*  RPF_VERSION 版本信息
*/
const RPF_VERSION       =   '0.2.0';

/**
*  URL_COMMON URL普通模式
*/
const URL_COMMON        =   0;  //普通模式

/**
*  URL_PATHINFO URL PATHINFO模式
*/
const URL_PATHINFO      =   1;  //PATHINFO模式

/**
*  URL_REWRITE URL REWRITE模式
*/
const URL_REWRITE       =   2;  //REWRITE模式

/**
*  URL_COMPAT URL兼容模式
*/
const URL_COMPAT        =   3;  // 兼容模式

defined('ORM')          or 
/**
*  是否开启ORM，默认不开启
*/
define('ORM', false);

defined('SAFE_MODEL')   or 
/**
*  是否开启安全模式SAFE_MODEL，默认开启,true开启，false不开启
*/
define('SAFE_MODEL', true);

defined('RPF_PATH')     or 
/**
*  RPF_PATH 框架路径，默认使用__DIR__.'/'赋值
*/
define('RPF_PATH',   __DIR__.'/');

defined('APP_NAME')     or 
/**
*  APP_NAME 应用程序名称，默认Application
*/
define('APP_NAME',   'Application');

defined('APP_PATH')     or 
/**
*  APP_PATH 应用程序路径，默认dirname($_SERVER['SCRIPT_FILENAME']).'/'.APP_NAME.'/'
*/
define('APP_PATH',   dirname($_SERVER['SCRIPT_FILENAME']).'/'.APP_NAME.'/');

defined('DEBUG')        or 
/**
*  DEBUG 是否开启debug模式，默认false
*/
define('DEBUG',      false);

defined('C_DEMO')       or 
/**
*  C_DEMO 是否创建demo，默认true
*/
define('C_DEMO',     true);

defined('FONTS_PATH')   or 
/**
*  FONTS_PATH 字体路径，默认RPF_PATH.'fonts/'
*/
define('FONTS_PATH', RPF_PATH.'fonts/');

//系统目录定义          
defined('SYS_CONF')     or 
/**
*  SYS_CONF 系统配置信息路径，默认RPF_PATH.'conf/'
*/
define('SYS_CONF', RPF_PATH.'conf/');            //系统配置信息

defined('SYS_FUNC')     or 
/**
*  SYS_FUNC 系统函数目录路径，默认RPF_PATH.'functions/'
*/
define('SYS_FUNC', RPF_PATH.'functions/');       //系统函数目录

defined('SYS_KERNEL')   or 
/**
*  SYS_KERNEL 系统核心代码目录路径，默认RPF_PATH.'kernel/'
*/
define('SYS_KERNEL', RPF_PATH.'kernel/');        //系统核心代码目录

defined('SYS_LANG')     or 
/**
*  SYS_LANG 系统语言包路径，默认RPF_PATH.'lang/'
*/
define('SYS_LANG', RPF_PATH.'lang/');            //语言包

defined('SYS_LIB')      or 
/**
*  SYS_LIB 系统类库代码路径，默认RPF_PATH.'lib/'
*/
define('SYS_LIB', RPF_PATH.'lib/');              //类库代码

defined('SYS_CORE')     or 
/**
*  SYS_CORE 系统核心类库代码,框架自己定义的类库代码路径，默认SYS_LIB.'core/'
*/
define('SYS_CORE', SYS_LIB.'core/');             //核心类库代码,框架自己定义的类库代码

defined('SYS_VENDOR')   or 
/**
*  SYS_VENDOR 系统框架引入的第三方类库代码路径，默认SYS_LIB.'vendor/'
*/
define('SYS_VENDOR', SYS_LIB.'vendor/');         //框架引入的第三方类库代码

//token相关配置
defined('OPEN_TOKEN')   or 
/**
*  OPEN_TOKEN 是否开启token，默认true
*/
define('OPEN_TOKEN', true);                      //是否开启token，默认true

defined('TOKEN_NAME')   or 
/**
*  TOKEN_NAME token名称，即hidden的input的name值
*/
define('TOKEN_NAME', 'token_name');              //token名称，即hidden的input的name值
//end

//邮件发送相关配置常量的定义 配置为常量原因在于相对与配置项效率更高
//以下配置，请务必在您的入口文件进行自定义配置，如果您需要进行邮件发送功能的话
defined('EMAIL_CHARSET')or 
/**
*  EMAIL_CHARSET 邮件发送编码，默认utf-8
*/
define('EMAIL_CHARSET', 'utf-8');

defined('EMAIL_DEBUG')  or 
/**
*  EMAIL_DEBUG 是否开启邮件debug模式，1:errors and messages, 2:messages only, 0:no debug
*/
define('EMAIL_DEBUG', 1); //1:errors and messages, 2:messages only, 0:no debug

defined('EMAIL_SMTP')   or 
/**
*  EMAIL_SMTP 是否启用SMTP认证,默认true
*/
define('EMAIL_SMTP', true);

defined('EMAIL_SSL')    or 
/**
*  EMAIL_SSL 是否启用安全协议证书,默认false
*/
define('EMAIL_SSL', false);

defined('EMAIL_HOST')   or 
/**
*  EMAIL_HOST 提供SMTP服务的服务器地址,默认smtp.qq.com
*/
define('EMAIL_HOST', 'smtp.qq.com');

defined('EMAIL_PORT')   or 
/**
*  EMAIL_PORT smtp服务器端口,默认25
*/
define('EMAIL_PORT', 25);

defined('EMAIL_USER')   or 
/**
*  EMAIL_USER smtp服务器登录用户名,默认xxxx@qq.com
*/
define('EMAIL_USER', 'xxxx@qq.com');

defined('EMAIL_PWD')    or 
/**
*  EMAIL_PWD smtp服务器登录密码,默认xxxx
*/
define('EMAIL_PWD', 'xxxx');

//显示的发送者名称
defined('EMAIL_NAME')   or 
/**
*  EMAIL_NAME 显示的发送者名称,默认admin
*/
define('EMAIL_NAME', 'admin');
//end

//项目相关的配置信息定义
defined('COMMON_PATH')  or 
/**
*  COMMON_PATH 应用公共目录,默认APP_PATH.'common/'
*/
define('COMMON_PATH',    APP_PATH.'common/');    // 应用公共目录

defined('APP_CONF')     or 
/**
*  APP_CONF 应用配置目录,默认COMMON_PATH.'conf/'
*/
define('APP_CONF',       COMMON_PATH.'conf/');   // 应用配置目录

defined('APP_LANG')     or 
/**
*  APP_LANG 应用配置目录,默认COMMON_PATH.'lang/'
*/
define('APP_LANG',       COMMON_PATH.'lang/');   // 应用语言目录

defined('APP_FUNC')     or 
/**
*  APP_FUNC 应用函数目录,默认COMMON_PATH.'func/'
*/
define('APP_FUNC',       COMMON_PATH.'func/');   // 应用函数目录

defined('HTML_PATH')    or 
/**
*  HTML_PATH 应用静态目录,默认APP_PATH.'html/'
*/
define('HTML_PATH',      APP_PATH.'html/');      // 应用静态目录

defined('RUNTIME_PATH') or 
/**
*  RUNTIME_PATH 系统运行时目录,默认APP_PATH.'runtime/'
*/
define('RUNTIME_PATH',   APP_PATH.'runtime/');   // 系统运行时目录

defined('LOG_PATH')     or 
/**
*  LOG_PATH 应用日志目录,默认RUNTIME_PATH.'logs/'
*/
define('LOG_PATH',       RUNTIME_PATH.'logs/');  // 应用日志目录

defined('TEMP_PATH')    or 
/**
*  TEMP_PATH 应用缓存目录,默认RUNTIME_PATH.'temp/'
*/
define('TEMP_PATH',      RUNTIME_PATH.'temp/');  // 应用缓存目录

defined('DATA_PATH')    or 
/**
*  DATA_PATH 应用数据目录,默认RUNTIME_PATH.'data/'
*/
define('DATA_PATH',      RUNTIME_PATH.'data/');  // 应用数据目录

defined('CACHE_PATH')   or 
/**
*  CACHE_PATH 应用模板缓存目录,默认RUNTIME_PATH.'cache/'
*/
define('CACHE_PATH',     RUNTIME_PATH.'cache/'); // 应用模板缓存目录

defined('CONF_EXT')     or 
/**
*  CONF_EXT 配置文件后缀,默认.conf.php
*/
define('CONF_EXT',       '.conf.php');           // 配置文件后缀

defined('CLS_EXT')      or 
/**
*  CLS_EXT 类库的扩展名,默认.class.php
*/
define('CLS_EXT',        '.class.php');          // 类库的扩展名

defined('CLS_M_EXT')    or 
/**
*  CLS_M_EXT model类库的扩展名,默认.model.class.php
*/
define('CLS_M_EXT',      '.model.class.php');    // model类库的扩展名

defined('CLS_C_EXT')    or 
/**
*  CLS_C_EXT controller类库的扩展名,默认.controller.class.php
*/
define('CLS_C_EXT',     '.controller.class.php');// controller类库的扩展名

defined('CLS_A_EXT')    or 
/**
*  CLS_A_EXT action类库的扩展名,默认.action.class.php
*/
define('CLS_A_EXT',      '.action.class.php');   // action类库的扩展名

defined('FUNC_EXT')     or 
/**
*  FUNC_EXT 函数定义文件的扩展名,默认.func.php
*/
define('FUNC_EXT',      '.func.php');            // 函数定义文件的扩展名

defined('APP_M')        or 
/**
*  APP_M 应用model目录,默认APP_PATH.'model/'
*/
define('APP_M',          APP_PATH.'model/');     // 应用model目录

defined('APP_V')        or 
/**
*  APP_V 应用template目录,默认APP_PATH.'template/'
*/
define('APP_V',          APP_PATH.'template/');  // 应用template目录

defined('APP_C')        or 
/**
*  APP_C 应用controller目录,默认APP_PATH.'controller/'
*/
define('APP_C',          APP_PATH.'controller/');// 应用controller目录

defined('APP_A')        or 
/**
*  APP_A 应用action目录,默认APP_PATH.'action/'
*/
define('APP_A',          APP_PATH.'action/');    // 应用action目录

defined('APP_P')        or 
/**
*  APP_P 应用公共文件如js/css/image等存放目录,默认APP_PATH.'public/'
*/
define('APP_P',          APP_PATH.'public/');    // 应用公共文件如js/css/image等存放目录

defined('APP_F')        or 
/**
*  APP_F 应用上传文件存放目录，可以根据需要创建images/files等文件夹,默认APP_PATH.'uploads/'
*/
define('APP_F',          APP_PATH.'uploads/');   // 应用上传文件存放目录，可以根据需要创建images/files等文件夹

//关闭GPC
if (version_compare(PHP_VERSION,'5.4.0','<'))
{
	ini_set('magic_quotes_runtime',0);
	/**
	*  MAGIC_QUOTES_GPC GPC是否开启的判断常量，true开启，false，关闭
	*/
	define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc()?true:false);
}
else
{
	/**
	*  MAGIC_QUOTES_GPC GPC是否开启的判断常量，true开启，false，关闭
	*/
	define('MAGIC_QUOTES_GPC',false);
}

if (!defined('SITE_URL'))
{
	$host = trim($_SERVER['HTTP_HOST']);
	if (count(explode('.', $host)) > 2)
		define('SITE_URL', 'http://'.$host.'/');
	else
	{
		if ($host != 'localhost')
			define('SITE_URL', 'http://www.'.$host.'/');
		else
			define('SITE_URL', 'http://'.$host.'/');
	}
}

//加载公共函数及核心启动类，启动框架执行
require SYS_FUNC.'core'.FUNC_EXT;
require SYS_KERNEL.'Kernel'.CLS_EXT;
if (ORM)
	import(SYS_VENDOR.'orm.php');
Kernel::start();
