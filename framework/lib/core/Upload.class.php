<?php
/**
* 文件上传类库 
* @filename Upload.class.php
* @touch date 2014-07-24 10:51:28
* @author Rain<563268276@qq.com>
* @copyright 2014 http://www.94cto.com/
* @license http://www.apache.org/licenses/LICENSE-2.0   LICENSE-2.0
* @package Rain PHP Frame(RPF)
*/

defined('RPF_PATH') or exit();

/**
* 文件上传类库，Upload
*/
class Upload 
{
	/**
	* 文件上传存储位置，默认APP_F
	*/
	private $path = APP_F;
	
	/**
	* 文件类型，默认images
	*/
	private $type = 'images';

	/**
	* 允许上传的文件的扩展名,默认array('jpg', 'gif', 'png')
	*/
	private $ext = array('jpg', 'gif', 'png');

	/**
	* 上传的文件的input表单的name值，默认file
	*/
	private $name = 'file';

	/**
	* 上传的文件的路径
	*/
	private $file_path = null;

	/**
	* 上传文件的构造方法
	* @param $type string 上传文件类型，默认images代表图片
	* @param $extArr array 上传文件扩展名的数组，默认array('jpg', 'gif', 'png')
	* @return void
	*/
	public function __construct($type = 'images', $extArr = array('jpg', 'gif', 'png'))
	{
		$dir = $this->path.$type.'/'.date('Ymd').'/';
		if (!is_dir($dir))
			mkdirs($dir, true);
		$this->type = $type;
		$this->ext = $extArr;
		$this->file_path = $dir;
	}

	/**
	* 上传文件的构造方法
	* @param $name string 上传的文件的input表单的名称
	* @return string 返回json格式的字符串
	*/
	public function upload($name = 'file')
	{
		$this->name = $name;
		if (!is_array($_FILES) || empty($_FILES) || !isset($_FILES[$this->name]))
			return json_encode(array('code' => '-1', 'msg' => 'no upload file find'));

		$code = 0;
		$msg = 'upload success';
		if ($_FILES[$name]['error'] != 0)
		{
			$code = $_FILES[$name]['error'];
			switch ($_FILES[$name]['error'])
			{
			case 1:
			case 2:
				$msg = 'upload file size not allow';
				break;
			case 3:
				$msg = 'File upload only partially';
				break;
			case 4:
				$msg = 'No file was uploaded';
				break;
			case 5:
				$msg = 'Upload file size is 0';
				break;
			default:
				$msg = 'Unknown error';
				break;
			}
		}
		if ($code != 0)
			return json_encode(array('code' => $code, 'msg' => $msg));
		if (!is_uploaded_file($_FILES[$name]['tmp_name']))
			return json_encode(array('code' => -2, 'msg' => 'this file not uploaded file'));
		if (!in_array(substr($_FILES[$name]['name'], strrpos($_FILES[$name]['name'], '.') + 1), $this->ext))
			return json_encode(array('code' => -2, 'msg' => 'this file extension not allow'));
		$file = $this->file_path.md5(microtime()).substr($_FILES[$name]['name'], strrpos($_FILES[$name]['name'], '.'));
		$ret = move_uploaded_file($_FILES[$name]['tmp_name'], $file);
		if (!$ret)
			return json_encode(array('code' => -3, 'msg' => 'move uploaded file failed'));
		else
			return json_encode(array('code' => 0, 'msg' => 'upload success', 'file' => getpath($file)));
	}
}
