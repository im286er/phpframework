<?php
// 应用入口文件
// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false 默认false
define('DEBUG',true);

// 定义应用目录, 默认应用程序目录为当前入口文件所在目录
define('APP_PATH',       dirname($_SERVER['SCRIPT_FILENAME']).'/');

//应用程序名称，默认Application 不同项目引用同一个框架APP_NAME必须保证唯一
define('APP_NAME', 'Application');

require('./framework/init.php');
