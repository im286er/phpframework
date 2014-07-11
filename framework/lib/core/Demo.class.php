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

class Demo
{
	/*
	  * 功能  ： 生成Demo例子程序
	  * 参数  ： void
	  * 返回  ： void
	*/
	public static function cdemo()
	{
		if (!C_DEMO)
		  return;
		$con_file = APP_C.Kernel::$_controller.CLS_C_EXT;
		$con_name = Kernel::$_controller.Kernel::$_conf['C_NAME'];

		$act_file = APP_A.Kernel::$_controller.'/'.ucfirst(Kernel::$_controller.'_'.Kernel::$_action).CLS_A_EXT;
		$act_name = Kernel::$_controller.'_'.Kernel::$_action.Kernel::$_conf['A_NAME'];

		$page_file = APP_V.Kernel::$_controller.'/'.strtolower(Kernel::$_action).Kernel::$_conf['V_NAME'];
		$dirArr = array(
					dirname($act_file),
					dirname($page_file),
		);
		mkdirs($dirArr);
		$con_content = <<<EOT
<?php
/**
 *  自动生成的代码
 *  author: None
 **/
class $con_name extends Controller
{
	//执行相关的初始化操作
	public function init()
	{
		echo "controller init ok<br/>";
	}
	//其他方法可以任意定义。但是框架只调用controller类里面的init
}
EOT;
		$act_content = <<<EOT
<?php
/**
 *  自动生成的代码
 *  author: None
 **/
class $act_name extends Action
{
	//执行相关的初始化操作
	public function init()
	{
		echo "action init ok<br/>";
	}

	//真正需要进行逻辑处理的运行代码类似其他框架的controller的action方法
	public function run()
	{
		\$val = '来自action里面的赋值';
		\$this->set('val', \$val);
		echo "action run ok<br/>";
		\$this->display();
	}

	//其他方法可以任意定义。但是框架只调用acion类里面的init和run
}
EOT;
	$page_content = <<<EOT
<html>
	<head>
		<title>模板自动生成代码</title>
	</head>
	<body>
		<h2><?php echo \$val; ?></h2>
	</body>
</html>
EOT;


		if (!is_file($con_file))
			file_put_contents($con_file, $con_content);

		if (!is_file($act_file))
			file_put_contents($act_file, $act_content);

		if (!is_file($page_file))
			file_put_contents($page_file, $page_content);

		unset($act_file, $act_content, $con_file, $con_content, $page_file, $page_content);
	}
}
