<?php
/**
 * CRedisCache class file
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CRedisCache implements a cache application component based on {@link http://redis.io/ redis}.
 *
 * CRedisCache needs to be configured with {@link hostname}, {@link port} and {@link database} of the server
 * to connect to. By default CRedisCache assumes there is a redis server running on localhost at
 * port 6379 and uses the database number 0.
 *
 * CRedisCache also supports {@link http://redis.io/commands/auth the AUTH command} of redis.
 * When the server needs authentication, you can set the {@link password} property to
 * authenticate with the server after connect.
 *
 * See {@link CCache} manual for common cache operations that are supported by CRedisCache.
 *
 * To use CRedisCache as the cache application component, configure the application as follows,
 * <pre>
 * array(
 *     'components'=>array(
 *         'cache'=>array(
 *             'class'=>'CRedisCache',
 *             'hostname'=>'localhost',
 *             'port'=>6379,
 *             'database'=>0,
 *         ),
 *     ),
 * )
 * </pre>
 *
 * The minimum required redis version is 2.0.0.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @package system.caching
 * @since 1.1.14
 */
class CRedisCache extends CCache
{
	/**
	 * @var string hostname to use for connecting to the redis server. Defaults to 'localhost'.
	 */
	public $hostname='localhost';
	/**
	 * @var int the port to use for connecting to the redis server. Default port is 6379.
	 */
	public $port=6379;
	/**
	 * @var string the password to use to authenticate with the redis server. If not set, no AUTH command will be sent.
	 */
	public $password;
	/**
	 * @var int the redis database to use. This is an integer value starting from 0. Defaults to 0.
	 */
	public $database=0;
	/**
	 * @var float timeout to use for connection to redis.
	 */
	public $timeout=30;
	/**
	 * @var Redis
	 */
	private $redis;
	/**
	 * @var bool
	 */
	private $connected;
	/**
	 * @var Array
	 */
	private static $instances=array();
	
	/**
	 * @desc Establishes a connection to the redis server.
	 * It does nothing if the connection has already been established.
	 * @throws CException if connecting fails
	 */
	protected function connect()
	{
		if(empty($this->redis))
			$this->redis = new Redis();
		$this->connected = $this->redis->connect($this->hostname,$this->port,$this->timeout);
		if(!$this->connected)
			throw new CException('Failed to connect to redis' . $this->redis->getLastError());
		$this->redis->select($this->database);
	}
	
	/**
	 * @desc 实例化CRedisCache,不传入参数则使用框架默认的redis
	 * @param string $hostname redis主机名
	 * @param int $port redis端口
	 * @param int $database redis数据库编号
	 * @return CRedisCache
	 * @author ChenLuoyong
	 * @date 2015-9-29
	 */
	public function __construct($hostname = '',$port = 6379,$database = 0)
	{
		$this->redis = new Redis();
		if($hostname){
			$this->hostname = $hostname;
			$this->port = $port;
			$this->database = intval($database);
		}
	}
	
	/**
	 * @desc 连接到默认的redis，并返回创建的实例（单例模式）
	 * @param string $redisName redis_conn配置项键名
	 * @author ChenLuoyong
	 * @date 2015-10-13
	 * @return CRedisCache
	 */
	public static function getInstance($redisName = '')
	{
		if(empty($redisName))
			return Yii::app()->cache;
		if(!isset(self::$instances[$redisName])){
			$config = Yii::app()->params['redis_conn'][$redisName];
			if(empty($config))
				return  false;
			self::$instances[$redisName] = new self($config['ip'],$config['port']);
		}
		return self::$instances[$redisName];
	}
	
	/**
	 * @desc 选择redis几号数据库
	 * @param string $id 数据库编号
	 * @author ChenLuoyong
	 * @date 2015-10-13
	 */
	public function select($database)
	{
		if(!$this->connected)
			$this->connect();
		return $this->redis->select ( $database );
	}
	/**
	 * Retrieves a value from cache with a specified key.
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key a unique key identifying the cached value
	 * @return string|boolean the value stored in cache, false if the value is not in the cache or expired.
	 * @modify chenluoyong 2015-9-25
	 */
	protected function getValue($key)
	{
		if(!$this->connected)
			$this->connect();
		return $this->redis->get($key);
	}

	/**
	 * Retrieves multiple values from cache with the specified keys.
	 * @param array $keys a list of keys identifying the cached values
	 * @return array a list of cached values indexed by the keys
	 */
	protected function getValues($keys)
	{
		if(!$this->connected)
			$this->connect();
		$func= is_array($keys) ? 'mGet': 'get';
		return $this->redis->{$func}($keys);
	}

	/**
	 * Stores a value identified by a key in cache.
	 * This is the implementation of the method declared in the parent class.
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param string $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	protected function setValue($key,$value,$expire)
	{
		if(!$this->connected)
			$this->connect();
		if($expire == 0)
			return (bool)$this->redis->set($key,$value);
		return (bool)$this->redis->setex($key,$expire,$value);
	}

	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 * This is the implementation of the method declared in the parent class.
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param string $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	protected function addValue($key,$value,$expire)
	{
		if(empty($key) || empty($value))
			return false;
		if(!$this->connected)
			$this->connect();
		if ($expire == 0)
			return (bool)$this->redis->setnx($key,$value);//('SETNX',array($key,$value));
		
		if($this->redis->setnx($key,$value))
		{
			$this->redis->expire($key,$expire);
			return true;
		}
		else
			return false;
	}

	/**
	 * Deletes a value with the specified key from cache
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 */
	protected function deleteValue($key)
	{
		if(!$this->connected)
			$this->connect();
		return (bool)$this->redis->del($key);
	}
	
	/**
	 * @desc 一次性删除多个键值对
	 * @author ChenLuoyong
	 * @date 2015-9-25
	 * @param Array $keyArray=array('key1','key2',...)
	 * @return boolean
	 */
	public function deleteValues($keyArray)
	{
		if(!$this->connected)
			$this->connect();
		return (bool)$this->redis->del($keyArray);
	}
	
	/**
	 * @desc 指定的key值自增1
	 * $redis->incr('key1');  //key1 didn't exists, set to 0 before the increment and now has the value 1  
	 * $redis->incr('key1');  //2 
	 * $redis->incr('key1');  //3 
	 * @param string $key
	 * @return int 自增之后的值
	 * @author ChenLuoyong
	 * @date 2015-9-29
	 */
	public function incr($key)
	{
		if(empty($key))
			return false;
		if(!$this->connected)
			$this->connect();
		$this->redis->incr($key);
	}
	
	/**
	 * @desc 指定的key值自减1
	 * @param string $key
	 * @return int 自减之后的值.
	 * @author ChenLuoyong
	 * @date 2015-9-29
	 */
	public function decr($key)
	{
		if(empty($key))
			return false;
		if(!$this->connected)
			$this->connect();
		$this->redis->incr($key);
	}
	
	/**
	 * @desc 键值对写入hash数组缓存
	 * @param string $hashName hash组name
	 * @param string $key KEY
	 * @param string $value 缓存值
	 * @return mixed
	 * @author ChenLuoyong
	 * @date 2015-9-29
	 */
	public function hSet($hashName, $key, $value)
	{
		if(empty($hashName) || empty($value))
			return false;
		if(!$this->connected)
			$this->connect();
		return $this->redis->hSet($hashName, $key, $value);
	}
	
	/**
	 * @desc 对hash数组一次写入多个键值
	 * @param string $hashName hash组name
	 * @param array $keyValueArray 键值对数组
	 * @return boolean
	 * @date 2014-12-20
	 * @author ChenLuoyong
	 * @date 2015-9-29
	 */
	public function hmSet($hashName, $keyValueArray)
	{
		if(empty($hashName) || empty($keyValueArray))
			return false;
		if(!$this->connected)
			$this->connect();
		return $this->redis->hmSet($hashName, $keyValueArray);
	}
	
	/**
	 * @desc 读hash数组缓存
	 * @param string $hashName redis hash组name
	 * @param string $field hash结构中键名
	 * @return mixed
	 * @author ChenLuoyong
	 * @date 2015-9-29
	 */
	public function hGet($hashName, $field = '')
	{
		if(empty($hashName))
			return false;
		if(!$this->connected)
			$this->connect();
		if(empty($field)){
			return $this->redis->hGetAll($hashName);	// 获取全部field
		}
		$func = is_array($field) ? 'hmGet': 'hGet';
		return $this->redis->{$func}($hashName, $field);
	}
	
	/**
	 * @desc 获取hash表的长度
	 * $redis->delete('h')
	 * $redis->hSet('h', 'key1', 'hello');
	 * $redis->hSet('h', 'key2', 'plop');
	 * $redis->hLen('h');	//returns 2 
	 * @param string $hashName
	 * @return int
	 * @author ChenLuoyong
	 * @date 2015-9-29
	 */
	public function hLen($hashName)
	{
		if(empty($hashName))
			return false;
		if(!$this->connected)
			$this->connect();
		return $this->redis->hLen($hashName);
	}
	
	/**
	 * @desc 删除指定的hash表，或hash中的指定键值对
	 * @param string $hashName
	 * @param string $key
	 * @return bool
	 * @author ChenLuoyong
	 * @date 2015-9-29
	 */
	public function hDel($hashName,$key = '')
	{
		if(empty($hashName) || empty($key))
			return false;
		if(!$this->connected)
			$this->connect();
		return $this->redis->hDel($hashName,$key);
	}
	
	/**
	 * @desc 在List的头部添加元素
	 * @param string $key
	 * @param string $value
	 * @return 成功返回List长度，失败返回false
	 * @author ChenLuoyong
	 * @date 2015-9-29
	 */
	public function lPush($key, $value) {
		if(empty($key) || empty($value)){
			return false;
		}
		if(!$this->connected)
			$this->connect();
		return $this->redis->lpush ( $key, $value );
	}
	
	/**
	 * @desc 在List的尾部添加元素
	 * @param string $key
	 * @param string $value
	 * @author ChenLuoyong
	 * @date 2015-9-29
	 */
	public function rPush($key,$value)
	{
		if(empty($key)||empty($value))
			return false;
		if(!$this->connected)
			$this->connect();
		return $this->redis->rPush($key,$value);
	}
	
	/**
	 * @desc 删除并返回List头部第一个元素
	 * @param string $key
	 * @return string
	 * @author ChenLuoyong
	 * @date 2015-9-29
	 */
	public function lPop($key)
	{
		if(empty($key))
			return false;
		if(!$this->connected)
			$this->connect();
		return $this->redis->lPop($key);
	}
	
	/**
	 * @desc List尾部删除并返回最后一个元素
	 * @param string $key
	 * @return string
	 * @author ChenLuoyong
	 * @date 2015-9-29
	 */
	public function rPop($key)
	{
		if(empty($key))
			return false;
		if(!$this->connected)
			$this->connect();
		return $this->redis->lPop($key);
	}
	
	/**
	 * @desc 获取指定List中元素个数
	 * @param string $key
	 * @return long,不存在或为空则返回0
	 * @author ChenLuoyong
	 * @date 2015-9-29
	 */
	public function lSize($key)
	{
		if(empty($key))
			return false;
		if(!$this->connected)
			$this->connect();
		return $this->redis->lSize($key);
	}

	/**
	 * @desc 获取List中指定的元素(不做删除操作)
	 * @param string $key
	 * @param int $start 开始位置，0是第一个元素
	 * @param int $end 截止位置，-1是最后一个元素
	 * @return string
	 * @author ChenLuoyong
	 * @date 2015-9-29
	 */
	public function lRange($key,$start=0,$end=-1) {
		if(empty($key)){
			return false;
		}
		if(!$this->connected)
			$this->connect();
		return $this->redis->lRange($key,$start,$end);
	}
	
	/**
	 * @desc 从List中删除元素
	 * @param string $key
	 * @param string $value
	 * @param int $count 0:删除所有符合条件的元素，正整数:从左至右删除count个符合条件的元素，负整数:从右至左删除count个符合条件的元素
	 * @author ChenLuoyong
	 * @date 2015-9-29
	 */
	public function lRemove($key,$value,$count)
	{
		if(empty($key) || empty($value))
			return false;
		if(!$this->connected)
			$this->connect();
		return $this->redis->lRemove($key,$value,$count);
	}

	/**
	 * @desc Deletes all values from cache.
	 * This is the implementation of the method declared in the parent class.
	 * @return boolean whether the flush operation was successful.
	 */
	protected function flushValues()
	{
		if(!$this->connected)
			$this->connect();
		if(IS_DISABLE_REDIS_PERSISTENCE){
			return false;
		}
		return $this->redis->flushDB();
	}
	
	/**
	 * @desc 设置键值对的过期时间
	 * @param string $key
	 * @param int $expire 单位：秒
	 * @author ChenLuoyong
	 * @date 2015-9-29
	 */
	public function expire($key,$expire)
	{
		if(!$this->connected)
			$this->connect();
		$this->redis->expire($key,$expire);
	}
	
	/**
	 * @desc Disconnects from the Redis instance, except when pconnect is used
	 * @author ChenLuoyong
	 * @date 2015-9-25
	 * @return void
	 */
	public function close()
	{
		if($this->connected)
			$this->redis->close();
	}
}
