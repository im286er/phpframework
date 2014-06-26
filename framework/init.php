<?php
// +----------------------------------------------------------------------
// | RPF  [Rain PHP Framework ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.itziy.com All rights reserved.
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
const RPF_VERSION     =   '0.0.1';

// URL 模式定义
const URL_COMMON        =   0;  //普通模式
const URL_PATHINFO      =   1;  //PATHINFO模式
const URL_REWRITE       =   2;  //REWRITE模式
const URL_COMPAT        =   3;  // 兼容模式

// 系统常量定义
defined('RPF_PATH')   or define('RPF_PATH',     __DIR__.'/');
defined('APP_PATH')     or define('APP_PATH',       dirname($_SERVER['SCRIPT_FILENAME']).'/');
defined('DEBUG')    or define('DEBUG',      false); // 是否调试模式
defined('APP_NAME') or define('APP_NAME', 'Application');


