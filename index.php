<?php
// 应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.7','<'))  die('require PHP > 5.3.7 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('DEBUG',true);

// 定义应用目录, 默认应用目录 Application
define('APP_PATH','./Application/');
