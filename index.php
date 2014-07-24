<?php
/**
* 应用程序前台入口文件
* @filename index.php
* @touch date 2014-07-23 16:12:57
* @author Rain<563268276@qq.com>
* @copyright 2014 http://www.94cto.com/
* @license http://www.apache.org/licenses/LICENSE-2.0   LICENSE-2.0
* @package Rain PHP Frame(RPF)
*/

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false 默认false
define('DEBUG',true); //此设置非必须

//应用程序名称，默认Application 不同项目引用同一个框架APP_NAME必须保证唯一
define('APP_NAME', 'Application'); //此设置非必须，除非有多个入口

// 定义应用目录, 默认应用程序目录为当前入口文件所在目录
define('APP_PATH',       dirname($_SERVER['SCRIPT_FILENAME']).'/'.APP_NAME.'/'); // 此设置非必须

//define('ORM', true);

//引入框架初始化文件，大功告成了，其他事情都不用做了
require('./framework/init.php');
