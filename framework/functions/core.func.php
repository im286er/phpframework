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

/*
   * 功能: 递归读取目录下所有文件(含目录全路径)自动过滤.和..
   * 参数: $dir 目录路径 类型: string
   * 返回: 成功返回目录一维数组，失败返回false
*/
function read_dir($dir)
{
	static $dirArr = array();

	$dir = trim($dir);

	if (!is_dir($dir))
	  return false;

	//补全后面的/
	if (substr($dir, -1) != '/')
	  $dir .= '/';

	if ($dh = opendir($dir))
	{
		while (($file = readdir($dh)) !== false)
		{
			if ('.' == $file || '..' == $file)
			  continue;
			if (is_file($dir.$file))
			  $dirArr[] = $dir.$file;
			else
			  return read_dir($dir.$file.'/');
		}
		closedir($dh);
	}
	return $dirArr;
}

/*
   * 功能: 包含文件，实现同require，只是做了包含文件个数统计
   * 参数: $file 需要包含的文件 类型: string
   * 返回: 成功返回同require，失败返回false
*/
function import($file)
{
	if (!is_file($file))
	  return false;
	$GLOBALS['_reqFile']++;
	return require_once($file);
}
