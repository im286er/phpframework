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

class Mysql
{
	private $conf = null;
	private $pdo = null;
	private $statement = null;
	private $lastInsID = null;
	private static $_instance;

	public static function getInstance($conf = null)
	{
		if (!(self::$_instance instanceof self))
		{
			self::$_instance = new self($conf);
		}
		return self::$_instance;
	}

	/*
	 * 功能: 获取最后一次插入的自增值
	*/
	public function getLastId()
	{
		if (is_null($this->pdo))
			$this->connect();
		return $this->pdo->lastInsertId();
	}

	public function connect()
	{
		if (!is_null($this->pdo))
			return $this->pdo;
		try {
			$this->pdo = new PDO($this->conf['dsn'], $this->conf['un'], $this->conf['pw'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_EMULATE_PREPARES => false));
		} catch (PDOException $e) {
			if (DEBUG)
				throw new Exception($e->getMessage()); 
			die(Kernel::$_lang['_SYS_LANG_NEW_PDO_ERROR']);
		}
	}

	/*
	 * 功能: 执行SELECT获取单条的一维数组的记录
    */
	public function fetchOne($sql, $data = array())
	{
		return $this->query($sql, $data, true, false);
	}

	/*
	 * 功能: 执行SELECT获取所有记录的二维数组
    */
	public function fetchAll($sql, $data = array())
	{
		return $this->query($sql, $data, false, false);
	}

	/*
	 * 功能: 执行SELECT获取单条的一维数组的记录 含缓存功能
    */
	public function fetchOneCache($sql, $data = array(), $cache_type = null, $timeout = null)
	{
		return $this->query($sql, $data, true, $cache_type, $timeout);
	}

	/*
	 * 功能: 执行SELECT获取所有记录的二维数组 含缓存功能
    */
	public function fetchAllCache($sql, $data = array(), $cache_type = null, $timeout = null)
	{
		return $this->query($sql, $data, false, $cache_type, $timeout);
	}

	/*
	 * 功能: 执行除了SELECT以外的SQL操作,底层实现调用对应的query方法，只是简便方法参数
    */
	public function execute($sql, $data = array())
	{
		return $this->query($sql, $data, true, false);
	}

	/*
	 * 功能: 可以执行任何的SQL，包括SELECT/INSERT/CREATE等等，但是参数比较多
	 * 参数说明
	 * $sql          要执行的SQL
	 * $data         要赋值到SQL里面做参数绑定的数组，只支持一维数组
	 * $one          是否只取单条记录，true为单条记录（一维数组），false为全部记录（二维数组），对于INSERT/UPDATE操作此参数无效
	 * $cache_type   缓存类型，m所内存缓存，f所文件缓存，false为不进行缓存，默认内存memcache缓存模式
	 * $timeout      缓存有效时间，默认2小时，必须$cache_type不为false时候有效
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

	public function free()
	{
		if (!is_null($this->statement))
		{
			$this->statement->closeCursor();
			$this->statement = null;
		}
	}

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

	private function __clone()
	{
	}
}
