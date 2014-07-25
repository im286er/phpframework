<?php
/**
* Mysql的操作类库 
* @filename Mysql.class.php
* @touch date 2014-07-24 10:24:29
* @author Rain<563268276@qq.com>
* @copyright 2014 http://www.94cto.com/
* @license http://www.apache.org/licenses/LICENSE-2.0   LICENSE-2.0
* @package Rain PHP Frame(RPF)
*/

defined('RPF_PATH') or exit();

/**
* Mysql的操作类库 
*/
class Mysql
{
	/**
	* 数据库配置存储变量
	*/
	private $conf = null;

	/**
	* 数据库pdo对象存储变量
	*/
	private $pdo = null;

	/**
	* 是否开启事务，默认false
	*/
	private $trans = false;

	/**
	* SQL预处理对象存储变量
	*/
	private $statement = null;

	/**
	* 最后一次插入的自增ID存储变量
	*/
	private $lastInsID = null;

	/**
	* 存储Mysql类对象的变量
	*/
	private static $_instance;

	/**
	* 构造mysql对象供外部调用的方法
	* @param array $conf mysql的配置数组信息
	* @return object 构造出来的mysql对象
	*/
	public static function getInstance($conf = null)
	{
		if (!(self::$_instance instanceof self))
		{
			self::$_instance = new self($conf);
		}
		return self::$_instance;
	}

	/**
	* 启动事务处理模式
	* @return bool 成功返回true，失败返回false
	*/
	public function startTrans()
	{
		if ($this->trans)
		  return;
		if (is_null($this->pdo))
			$this->connect();
		$this->trans = $this->pdo->beginTransaction();
		return $this->trans;
	}

	/**
	* 提交事务
	* @return bool 成功返回true，失败返回false
	*/
	public function commit()
	{
		if (!$this->trans)
		  return false;
		if (is_null($this->pdo))
			$this->connect();
		$ret = $this->pdo->commit();
		$this->trans = false;
		return $ret;
	}

	/**
	* 事务回滚
	* @return bool 成功返回true，失败返回false
	*/
	public function rollback()
	{
		if (!$this->trans)
		  return false;
		if (is_null($this->pdo))
			$this->connect();
		$ret = $this->pdo->rollBack();
		$this->trans = false;
		return $ret;
	}

	/**
	* 获取最后一次插入的自增值
	* @return bool|int 成功返回最后一次插入的id，失败返回false
	*/
	public function getLastId()
	{
		if (is_null($this->pdo))
			$this->connect();
		return $this->pdo->lastInsertId();
	}

	/**
	* 建立到mysql的链接
	* @return void
	*/
	public function connect()
	{
		if (!is_null($this->pdo))
			return $this->pdo;
		try {
			$this->pdo = new PDO($this->conf['dsn'], $this->conf['un'], $this->conf['pw'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\'', PDO::ATTR_EMULATE_PREPARES => false));
		} catch (PDOException $e) {
			if (DEBUG)
				echo $e->getMessage(); 
			echo '<p>Please try the command line: <b>setsebool httpd_can_network_connect 1</b>'."</p>";
			die(Kernel::$_lang['_SYS_LANG_NEW_PDO_ERROR']);
		}
	}

	/**
	* 执行SELECT获取单条的一维数组的记录
	* @param string $sql 需要执行的SQL语句
	* @param array $data 传递给SQL的变量数组
	* @return array 执行的结果一维数组的记录
	*/
	public function fetchOne($sql, $data = array())
	{
		return $this->query($sql, $data, true, false);
	}

	/**
	* 执行SELECT获取所有记录的二维数组
	* @param string $sql 需要执行的SQL语句
	* @param array $data 传递给SQL的变量数组
	* @return array 执行的结果数组的记录
	*/
	public function fetchAll($sql, $data = array())
	{
		return $this->query($sql, $data, false, false);
	}

	/**
	* 执行SELECT获取单条的一维数组的记录 含缓存功能
	* @param string $sql 需要执行的SQL语句
	* @param array $data 传递给SQL的变量数组
	* @param string $cache_type 缓存类型，f是文件缓存，m是内存缓存
	* @param int $timeout 缓存过期时间
	* @return array 执行的结果数组的记录
	*/
	public function fetchOneCache($sql, $data = array(), $cache_type = null, $timeout = null)
	{
		return $this->query($sql, $data, true, $cache_type, $timeout);
	}

	/**
	* 执行SELECT获取所有记录的二维数组 含缓存功能
	* @param string $sql 需要执行的SQL语句
	* @param array $data 传递给SQL的变量数组
	* @param string $cache_type 缓存类型，f是文件缓存，m是内存缓存
	* @param int $timeout 缓存过期时间
	* @return array 执行的结果数组的记录
	*/
	public function fetchAllCache($sql, $data = array(), $cache_type = null, $timeout = null)
	{
		return $this->query($sql, $data, false, $cache_type, $timeout);
	}

	/**
	* 执行除了SELECT以外的SQL操作,底层实现调用对应的query方法，只是简便方法参数
	* @param string $sql 需要执行的SQL语句
	* @param array $data 传递给SQL的变量数组
	* @return array 执行的结果
	*/
	public function execute($sql, $data = array())
	{
		return $this->query($sql, $data, true, false);
	}

	/**
	* 可以执行任何的SQL，包括SELECT/INSERT/CREATE等等，但是参数比较多
	* @param string $sql          要执行的SQL
	* @param array  $data         要赋值到SQL里面做参数绑定的数组，只支持一维数组
	* $param bool $one          是否只取单条记录，true为单条记录（一维数组），false为全部记录（二维数组），对于INSERT/UPDATE操作此参数无效
	* @param string $cache_type   缓存类型，m所内存缓存，f所文件缓存，false为不进行缓存，默认内存memcache缓存模式
	* @param int $timeout      缓存有效时间，默认2小时，必须$cache_type不为false时候有效
	* @return mixed 执行的结果
	*/
	public function query($sql, $data = array(), $one = false, $cache_type = null, $timeout = null)
	{
		if (is_null($cache_type))
			$cache_type = Kernel::$_conf['DB_CACHE_TYPE'];

		if (is_null($timeout))
			$timeout = Kernel::$_conf['DB_CACHE_EXPIRE'];

		if ($cache_type == 'm' && !extension_loaded('memcache'))
			$cache_type = 'f';

		if (is_null($this->pdo))
			$this->connect();
		$this->free();
		
		$this->statement = $this->pdo->prepare($sql);
		if (false === $this->statement)
		{
			if (DEBUG)
			{
				echo '<pre>';
				print_r($this->pdo->errorInfo());
				echo '</pre>';
				throw new Exception('sql:'.$sql);
			}
			die(Kernel::$_lang['_SYS_LANG_EXECUTE_SQL_ERROR']);
        }
		if (!empty($data) && is_array($data))
		{
			foreach ($data as $k => $v)
				$this->statement->bindValue($k, $v);
		}
		if (!$this->statement->execute())
		{
			if ($this->trans)
			  $this->rollback();

			if (DEBUG)
			{
				echo '<pre>';
				print_r($this->statement->errorInfo());
				echo '</pre>';
				throw new Exception('sql:'.$sql);
			}
			die(Kernel::$_lang['_SYS_LANG_EXECUTE_SQL_ERROR']);
		}

		$GLOBALS['_sqlCount']++;

		if (preg_match("/^\s*(INSERT\s+INTO|REPLACE\s+INTO)\s+/i", $sql))
			$this->lastInsID = $this->getLastId();
		else
			$this->lastInsID = null;

		if (!is_null($this->lastInsID))
			return $this->lastInsID;

		if (is_array($data) && !empty($data))
			$key = 'db_cache_'.md5($sql.serialize($data));
		else
			$key = 'db_cache_'.md5($sql);

		if ($one)
		{
			if (APP_NAME == Kernel::$_conf['ADMIN_APP_NAME'] || !$cache_type)
				return $this->statement->fetch(PDO::FETCH_ASSOC);
			else
			{
				$cache = Cache::getInstance($cache_type, array('timeout' => $timeout));
				$ret = $cache->get($key);
				if ($ret)
				{
					$GLOBALS['_sqlCount']--;
					return $ret; 
				}
				$val = $this->statement->fetch(PDO::FETCH_ASSOC);
				$cache->set($key, $val, $timeout);
				return $val;
			}
		}
		else
		{
			if (APP_NAME == Kernel::$_conf['ADMIN_APP_NAME'] || !$cache_type)
				return $this->statement->fetchAll(PDO::FETCH_ASSOC);
			else
			{
				$cache = Cache::getInstance($cache_type, array('timeout' => $timeout));
				$ret = $cache->get($key);
				if ($ret)
				{
					$GLOBALS['_sqlCount']--;
					return $ret; 
				}
				$val = $this->statement->fetchAll(PDO::FETCH_ASSOC);
				$cache->set($key, $val, $timeout);
				return $val;
			}
		}
	}

	/**
	* 释放资源
	* @return void
	*/
	public function free()
	{
		if (!is_null($this->statement))
		{
			$this->statement->closeCursor();
			$this->statement = null;
		}
	}

	/**
	* 构造方法，仅供内部使用
	* @param $conf array mysql链接配置信息
	* @return void
	*/
	private function __construct($conf)
	{
		if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql'))
		{
			if (DEBUG)
			  die(Kernel::$_lang['_SYS_LANG_EXT_NOT_FIND'].' : PDO or pdo_mysql extension');
			else
			  die(Kernel::$_lang['_SYS_LANG_EXT_NOT_FIND']);
		}

		$this->conf = array(
			'dsn' => Kernel::$_conf['DB_DSN'],
			'un' => Kernel::$_conf['DB_UN'],
			'pw' => Kernel::$_conf['DB_PW'],
		);

		if (is_array($conf) && !empty($conf))
		{
			foreach ($conf as $k => $v)
			{
				if (!is_scalar($v) || !isset($this->conf[$k]))
				{
					unset($conf[$k]);
					continue;
				}
				$this->conf[$k] = $v;
			}
		}
	}

	/**
	* 单例方法，禁用clone
	*/
	private function __clone()
	{
	}
}
