<?php
/**
* 字符串操作类库 
* @filename String.class.php
* @touch date 2014-07-24 10:50:08
* @author Rain<563268276@qq.com>
* @copyright 2014 http://www.94cto.com/
* @license http://www.apache.org/licenses/LICENSE-2.0   LICENSE-2.0
* @package Rain PHP Frame(RPF)
*/

defined('RPF_PATH') or exit();

/**
* 字符串操作类库 
*/
class String
{
    /**
     * 字符串截取函数
     * @access public
     * @param  string  $str      待截取的字符串
     * @param  int     $start    开始位置
     * @param  int     $len      截取字符串长度
     * @param  string  $encoding 编码，默认UTF-8
     * @param  string  $prex     后缀，默认...
     * @return string            截取后的字符串
     */
    public static function substring($str, $start, $len, $encoding = 'UTF-8', $prex = '...')
	{
		if (mb_strlen($str, $encoding) > $len)
			return  mb_substr($str, $start, $len, $encoding).$prex;
		return  mb_substr($str, $start, $len, $encoding);
    }

	/**
     * 字符串长度计算
     * @access public
     * @param  string  $str      待计算长度的字符串
     * @param  string  $encoding 编码，默认UTF-8
     * @return int               字符串长度
     */
	public static function strlength($str, $encoding = 'UTF-8')
	{
		return mb_strlen($str, $encoding);
	}

	/**
     * 字符串编码检测
     * @access public
     * @param  string  $str      字符串
     * @param  string  $encoding 编码，默认UTF-8
     * @return bool              成功返回true，失败返回false
     */
	public static function strlength($str, $encoding = 'UTF-8')
	{
		return mb_check_encoding($str, $encoding);
	}

	/**
     * 字符串编码转换
     * @access public
     * @param  string  $str      字符串
     * @param  string  $from     从xx编码，默认GBK
     * @param  string  $to       转换为xx编码，默认UTF-8
     * @return string            转换完成的string字符串
     */
	public static function striconv($str,$from = 'GBK', $to = 'UTF-8')
	{
		return mb_convert_encoding($str, $to, $from);
	}
}
