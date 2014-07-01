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

//系统默认的配置文件，如果需要覆盖，请在对应的应用程序的配置目录APP_CONF下进行改写，请勿修改此文件
return array(
			'URL_MODEL' => URL_COMPAT,       // URL模式，默认使用兼容模式
			'LANG' => 'zh',                  // 默认的语言包选择中文
			'M_NAME' => 'Model',             // 默认的model class的名称后缀
			'V_NAME' => '.html',             // 默认的view template的文件扩展名
			'C_NAME' => 'Controller',        // 默认的controller class的名称后缀
			'A_NAME' => 'Action',            // 默认的action class的名称后缀
);
