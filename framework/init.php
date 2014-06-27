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

//初始化入口文件

// 检测PHP环境
//php 5.3.7 之后PDO的支持才完美,所以框架限制php版本>=5.3.7
if (version_compare(PHP_VERSION,'5.3.7','<'))  die('require PHP > 5.3.7 !');

//记录开始运行时间
$GLOBALS['_bTime'] = microtime(true);
// 记录内存初始使用
define('MEMORY_LIMIT_ON', function_exists('memory_get_usage'));
if (MEMORY_LIMIT_ON) $GLOBALS['_sMem'] = memory_get_usage();

// 版本信息
const RPF_VERSION       =   '0.0.1';

// URL 模式定义
const URL_COMMON        =   0;  //普通模式
const URL_PATHINFO      =   1;  //PATHINFO模式
const URL_REWRITE       =   2;  //REWRITE模式
const URL_COMPAT        =   3;  // 兼容模式

// 系统常量定义
defined('RPF_PATH')     or define('RPF_PATH',   __DIR__.'/');
defined('APP_PATH')     or define('APP_PATH',   dirname($_SERVER['SCRIPT_FILENAME']).'/');
defined('DEBUG')        or define('DEBUG',      false);
defined('APP_NAME')     or define('APP_NAME',   'Application');

//系统目录定义          
defined('SYS_CONF')     or define('SYS_CONF', RPF_PATH.'conf/');            //系统配置信息
defined('SYS_FUNC')     or define('SYS_FUNC', RPF_PATH.'functions/');       //系统函数目录
defined('SYS_KERNEL')   or define('SYS_KERNEL', RPF_PATH.'kernel/');        //系统核心代码目录
defined('SYS_LANG')     or define('SYS_LANG', RPF_PATH.'lang/');            //语言包
defined('SYS_LIB')      or define('SYS_LIB', RPF_PATH.'lib/');              //类库代码
defined('SYS_CORE')     or define('SYS_CORE', SYS_LIB.'core/');             //核心类库代码,框架自己定义的类库代码
defined('SYS_VENDOR')   or define('SYS_VENDOR', SYS_LIB.'vendor/');         //框架引入的第三方类库代码

//项目相关的配置信息定义
defined('COMMON_PATH')  or define('COMMON_PATH',    APP_PATH.'common/');    // 应用公共目录
defined('APP_CONF')     or define('APP_CONF',       COMMON_PATH.'conf/');   // 应用配置目录
defined('APP_LANG')     or define('APP_LANG',       COMMON_PATH.'lang/');   // 应用语言目录
defined('HTML_PATH')    or define('HTML_PATH',      APP_PATH.'html/');      // 应用静态目录
defined('RUNTIME_PATH') or define('RUNTIME_PATH',   APP_PATH.'runtime/');   // 系统运行时目录
defined('LOG_PATH')     or define('LOG_PATH',       RUNTIME_PATH.'logs/');  // 应用日志目录
defined('TEMP_PATH')    or define('TEMP_PATH',      RUNTIME_PATH.'temp/');  // 应用缓存目录
defined('DATA_PATH')    or define('DATA_PATH',      RUNTIME_PATH.'data/');  // 应用数据目录
defined('CACHE_PATH')   or define('CACHE_PATH',     RUNTIME_PATH.'cache/'); // 应用模板缓存目录
defined('CONF_EXT')     or define('CONF_EXT',       '.conf.php');           // 配置文件后缀
defined('CLS_EXT')      or define('CLS_EXT',        '.class.php');          // 类库的扩展名
defined('APP_M')        or define('APP_M',          APP_PATH.'model/');     // 应用model目录
defined('APP_V')        or define('APP_V',          APP_PATH.'template/');  // 应用template目录
defined('APP_C')        or define('APP_C',          APP_PATH.'controller/');// 应用model目录
defined('APP_A')        or define('APP_A',          APP_C.'action/');       // 应用action目录
defined('APP_P')        or define('APP_P',          APP_PATH.'public/');    // 应用公共文件如js/css/image等存放目录
defined('APP_F')        or define('APP_F',          APP_PATH.'uploads/');   // 应用上传文件存放目录，可以根据需要创建images/files等文件夹

//关闭GPC
if (version_compare(PHP_VERSION,'5.4.0','<'))
{
	ini_set('magic_quotes_runtime',0);
	define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc()?true:false);
}
else
{
	define('MAGIC_QUOTES_GPC',false);
}
require SYS_KERNEL.'Kernel'.CLS_EXT;
Kernel::start();
