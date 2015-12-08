<?php
/**
 * @desc Redis操作类封装
 * @author ChenLuoyong
 * @date 2014-10-24
 */
class CRedisHelper
{
	/**
	 * @var Redis
	 */
	private $redis = null;
	/**
	 * @var bool
	 */
	private $connected = false;
	/**
	 * @var array
	 */
	private static $instances = array();
	/**
	 * @desc 初始化Redis对象
	 * @param string $redisName
	 * @author ChenLuoyong
	 * @date 2014-10-24
	 */
	private function __construct($redisName = 'default')
    {
        $config = Yii::app()->params['redis_conn'][$redisName];
        $this->redis = new Redis();
        $this->connected = $this->redis->connect($config['ip'], $config['port']);
        $this->redis->auth($config['password']);
    }
	
	/**
	 * @desc 创建redis实例（单例模式）
	 * @param string $redisName
	 * @author ChenLuoyong
	 * @date 2014-10-30
	 * @return CRedisHelper
	 */
	public static function getInstance($redisName = 'default')
	{
		if(!isset(self::$instances[$redisName])){
			self::$instances[$redisName] = new self($redisName);
		}
		return self::$instances[$redisName];
	}
	
	/**
	 * @desc 关闭redis连接
	 * @author ChenLuoyong
	 * @date 2014-10-24
	 */
	public function close()
	{
		if($this->redis){
			$this->redis->close();
		}
	}
	
	/**
	 * @desc 键值对写入
	 * @param string $key 键
	 * @param string $value 值
	 * @param int $expire 过期时间,单位：秒
	 * @return boolean
	 * @author ChenLuoyong
	 * @date 2014-10-24
	 */
	public function set($key, $value, $expire=0)
	{
		if(!$this->connected || empty($key) || is_null($value)){
			return false;
		}
		if($expire == 0){
			$ret = $this->redis->set($key, $value);
		}else{
			$ret = $this->redis->setex($key, $expire, $value);
		}
		return $ret;
	}

	/**
	 * @desc 一次写入多个键值
	 * @param array $keyValueArray 键值对数组
	 * @return boolean
	 * @date 2014-12-20
	 */
	public function mSet($keyValueArray)
	{
		if(!$this->connected || empty($keyValueArray)){
			return false;
		}
		return $this->redis->mSet($keyValueArray);
	}
	
	/**
	 * @desc 读缓存
	 * @param string $key 缓存KEY,支持一次取多个 $key = array('key1','key2')
	 * @return string || boolean  失败返回 false, 成功返回字符串
	 * @author ChenLuoyong
	 * @date 2014-10-24
	 */
	public function get($key)
	{
		if(!$this->connected)
			return null;
		$func= is_array($key) ? 'mGet': 'get';
		return $this->redis->{$func}($key);
	}
	
	/**
	 * @desc 键值对写入hash数组缓存
	 * @param string $hashName hash组name
	 * @param string $key KEY
	 * @param string $value 缓存值
	 * @return mixed
	 */
	public function hSet($hashName, $key, $value)
	{
		if(!$this->connected || empty($hashName) || empty($value))
			return null;
		$ret = $this->redis->hSet($hashName, $key, $value);
		return $ret;
	}

	/**
	 * @desc 对hash数组一次写入多个键值
	 * @param string $hashName hash组name
	 * @param array $keyValueArray 键值对数组
	 * @return boolean
	 * @date 2014-12-20
	 */
	public function hmSet($hashName, $keyValueArray)
	{
		if(!$this->connected || empty($hashName) || empty($keyValueArray)){
			return false;
		}
		return $this->redis->hmSet($hashName, $keyValueArray);
	}
	
	/**
	 * @desc 读hash数组缓存
	 * @param string $hashName redis hash组name
	 * @param string $field hash结构中键名
	 * @return mixed
	 */
	public function hGet($hashName, $field = null)
	{
		if(!$this->connected || empty($hashName))
			return false;
		if(is_null($field)){
			// 获取全部field
			return $this->redis->hGetAll($hashName);
		}
		$func = is_array($field) ? 'hmGet': 'hGet';
		return $this->redis->{$func}($hashName, $field);
	}

	/**
	 * @desc 设置过期时间
	 * @param string $key hash结构中键名
	 * @param int $time 时间值(可以是秒数[表示$time秒后过期]; 可以是时间戳[表示$time时刻过期]）
	 * @param bool $isTimestamp 是否使用时间戳(false:$time为秒数；true:$time为时间戳）
	 * @return boolean 
	 */
	public function setTimeout($key, $time, $isTimestamp = false)
	{
		if(!$this->connected || empty($key) || empty($time)){
			return false;
		}
		$func = $isTimestamp ? 'expireAt' : 'setTimeout';
		return $this->redis->{$func}($key, $time);
	}
}