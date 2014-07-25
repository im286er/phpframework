<?php
/**
* model的父类
* @filename Model.class.php
* @touch date 2014-07-23 17:26:01
* @author Rain<563268276@qq.com>
* @copyright 2014 http://www.94cto.com/
* @license http://www.apache.org/licenses/LICENSE-2.0   LICENSE-2.0
* @package Rain PHP Frame(RPF)
*/

defined('RPF_PATH') or exit();

/**
* 所有的model都应该继承此类
*/
class Model
{
	/**
	* 存储表的名称
	*/
	private $tb = null;

	/**
	* 存储WHERE条件字符串
	*/
	private $where = null;

	/**
	* 存储WHERE条件数组
	*/
	private $whereArr = null;

	/**
	* WHERE条件的拼接字符串
	*/
	private $flag = 'AND';

	/**
	* 删除数据库的记录信息
	* @return bool|int 成功返回影响的行数，失败返回false
	* <code>$this->tb('user')->where('un=:un', array(':un' => 'rain'))->where('pw=:pw', array(':pw' => 123456))->del();</code>
	*/
	public function del()
	{
		if (empty($this->tb))
		  return false;

		$sql = "DELETE FROM ".Config::get('DB_PRE').$this->tb;
		if (!empty($this->where))
		  $sql .= " WHERE ".$this->where;
		else
		  $this->whereArr = array();

		$mysql = Mysql::getInstance();
		return $mysql->execute($sql, $this->whereArr);
	}

	/**
	* 向数据库更新记录，相当于Mysql的update操作,调用此方法前必须先调用Model类的tb方法设定需要的表名称，不走事务
	* @param array $data 更新的数据，数据例子，array('un' => 'rain', 'pw' => '123456')
	* @return bool|int 成功返回影响的行数，失败返回false
	* <code>$this->tb('user')->where('un=:un', array(':un' => 'rain'))->save(array('un' => 'rainupdate', 'pw' => '123456update'));</code>
	*/
	public function save($data)
	{
		if (empty($this->tb))
		  return false;

		if (empty($data) || !is_array($data))
		  return false;

		$sql = "UPDATE ".Config::get('DB_PRE').$this->tb." SET ";
		$data2 = array();
		foreach ($data as $k => $v)
		{
			$sql .= $k.'=:'.$k.'_,';
			$data2[':'.$k.'_'] = $v;
		}
		$sql = substr($sql, 0, -1);
		if (!empty($this->where))
		  $sql .= " WHERE ".$this->where;
		else
		  $this->whereArr = array();

		$mysql = Mysql::getInstance();
		return $mysql->execute($sql,array_merge($data2, $this->whereArr));
	}

	/**
	* 向数据库添加记录，相当于Mysql的insert操作,调用此方法前必须先调用Model类的tb方法设定需要的表名称，不走事务
	* @param array $data 添加的数据，数据例子，array('un' => 'rain', 'pw' => '123456')
	* @return bool|int 成功返回插入的最后的自增ID，失败返回false
	* <code>$this->tb('user')->add(array('un' => 'rain', 'pw' => '123456'));</code>
	*/
	public function add($data)
	{
		if (empty($this->tb))
		  return false;

		if (empty($data) || !is_array($data))
		  return false;

		$field = array_keys($data);
		$fieldStr = '';
		$fieldStr2 = '';
		foreach ($field as $f)
		{
			$fieldStr .= '`'.trim($f).'`,';
			$fieldStr2 .= ':'.trim($f).',';
		}
		$fieldStr = substr($fieldStr, 0, -1);
		$fieldStr2 = substr($fieldStr2, 0, -1);
		$data2 = array();
		foreach ($data as $dk => $dv)
			$data2[':'.$dk] = $dv;

		$sql = "INSERT INTO ".Config::get('DB_PRE').$this->tb."(".$fieldStr.")VALUES(".$fieldStr2.")";
		$mysql = Mysql::getInstance();
		return $mysql->execute($sql, $data2);
	}

	/**
	* 返回SQL执行例如插入操作时候的最后一个自增ID
	* @return int 插入操作时候的最后一个自增ID
	*/
	public function lastId()
	{
		$mysql = Mysql::getInstance();
		return $mysql->getLastId();
	}

	/**
	* 执行SQL，通常用于创建表等操作
	* @param string $sql 需要执行的SQL语句
	* @param array $data 传递给SQL的变量数组
	* @return array 执行的结果
	*/
	public function execute($sql, $data = array())
	{
		$mysql = Mysql::getInstance();
		return $mysql->execute($sql, $data);
	}

	/**
	* 执行SQL查询操作，返回所有记录,该方法不支持连用where,该方法不缓存数据，实时查询数据库
	* @param string $sql 需要执行的完整SQL语句
	* @param array $data 传递给SQL的变量数组
	* @return array 执行的结果
	*/
	public function select($sql, $data = array())
	{
		$mysql = Mysql::getInstance();
		return $mysql->fetchAll($sql, $data);
	}

	/**
	* 执行SQL查询操作，返回单条记录,该方法不支持连用where,该方法不缓存数据，实时查询数据库
	* @param string $sql 需要执行的完整SQL语句
	* @param array $data 传递给SQL的变量数组
	* @return array 执行的结果
	*/
	public function find($sql, $data = array())
	{
		$mysql = Mysql::getInstance();
		return $mysql->fetchOne($sql, $data);
	}

	/**
	* 执行SQL查询操作，返回所有记录,该方法不支持连用where,该方法会缓存数据
	* @param string $sql 需要执行的完整SQL语句
	* @param array $data 传递给SQL的变量数组
	* @param string $cache_type 缓存类型，f是文件缓存，m是内存缓存
	* @param int $timeout 缓存过期时间
	* @return array 执行的结果
	*/
	public function selectCache($sql, $data = array(), $cache_type = null, $timeout = null)
	{
		$mysql = Mysql::getInstance();
		return $mysql->fetchAllCache($sql, $data, $cache_type, $timeout);
	}

	/**
	* 执行SQL查询操作，返回单条记录,该方法不支持连用where,该方法会缓存数据
	* @param string $sql 需要执行的完整SQL语句
	* @param array $data 传递给SQL的变量数组
	* @param string $cache_type 缓存类型，f是文件缓存，m是内存缓存
	* @param int $timeout 缓存过期时间
	* @return array 执行的结果
	*/
	public function findCache($sql, $data = array(), $cache_type = null, $timeout = null)
	{
		$mysql = Mysql::getInstance();
		return $mysql->fetchOneCache($sql, $data, $cache_type, $timeout);
	}

	/**
	* 设置表名称
	* @param string $tb 表的名称，不含表的前缀
	* @return object
	* <code>$this->tb('user');</code>
	*/
	public function tb($tb)
	{
		$this->tb = $tb;
		return $this;
	}

	/**
	* 修改flag拼接条件
	* @param string $flag 拼接字符串，默认AND
	* @return object
	* <code>$this->flag('OR');</code>
	*/
	public function flag($flag)
	{
		$this->flag = $flag;
		return $this;
	}

	/**
	* 设置SQL的where条件
	* @param string $str 设置SQL的where条件，可以重复调用此方法设置
	* @param array $data 传递给SQL的变量数组
	* @return object
	* <code>$this->where('id=:id', array(':id' => 1));</code>
	*/
	public function where($str, $data)
	{
		if (empty($this->where))
		{
			$this->where = $str;
			$this->whereArr = $data;
		}
		else
		{
			$this->where .= ' '.$this->flag.' '.$str;
			$this->whereArr = array_merge($this->whereArr, $data);
		}
		return $this;
	}
}
