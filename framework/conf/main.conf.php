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
			//URL模式可选值:URL_COMMON/URL_PATHINFO/URL_REWRITE/URL_COMPAT
			/* Demo
			 * URL_COMPAT   like this: http://localhost/index.php?s=index/Index/index/un/rain/pw/123456
			 * URL_COMMON   like this: http://localhost/index.php?app=index&con=Index&act=index&un=rain&pw=123456
			 * URL_PATHINFO like this: http://localhost/index.php/index/Index/index/un/rain/pw/123456
			 * URL_REWRITE  like this: http://localhost/index/Index/index/un/rain/pw/123456
			 */
			'URL_MODEL' => URL_COMPAT,       // URL模式，默认使用兼容模式
			'LANG' => 'zh',                  // 默认的语言包选择中文
			'M_NAME' => 'Model',             // 默认的model class的名称后缀
			'V_NAME' => '.html',             // 默认的view template的文件扩展名
			'C_NAME' => 'Controller',        // 默认的controller class的名称后缀
			'A_NAME' => 'Action',            // 默认的action class的名称后缀

			//数据库的配置信息开始
			'DB_DSN' => 'mysql:host=127.0.0.1;dbname=test;charset=utf8',            //数据库PDO方式DSN配置
			'DB_UN' => 'root',                                                      //数据库链接用户名
			'DB_PW' => 'rain717',                                                   //数据库链接密码
			'DB_PRE' => 'tb_',                                                      //数据库表前缀
			//end

			'MEM_HOST' => '127.0.0.1',   //memcache的配置主机IP
			'MEM_PORT' => '11211',       //memcache的配置端口
			'MEM_TIMEOUT' => '1',        //memcache的配置超时时间

			//对于未开启memcache扩展的，虽然设置DB_CACHE_TYPE为m，但是程序仍然选择file cache
			//如果DB_CACHE_TYPE的值为false，则不采用缓存机制
			'DB_CACHE_TYPE' => 'm',       //数据库查询的缓存类型，默认m代表memcache，f代表文件缓存
			//对于后台的任何数据库操作不用缓存机制,都是实时生效
			'DB_CACHE_EXPIRE' => '7200',  //数据库查询的缓存时间，默认缓存7200秒即2小时

			'ADMIN_APP_NAME' => 'admin',  //后台管理模块的APP_NAME

			'SESSION_SAVE_TYPE' => 'm',  //m值表示保存在memcache，f值表示session是文件保存方式
);
