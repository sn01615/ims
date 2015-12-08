<?php

/**
 * @desc memcache封装
 * @author YangLong
 * @date 2015-08-11
 */
class iMemcache
{
    
    /**
     * @desc Memcache实例
     * @var Memcache
     */
    private $memcache;
    
    /**
     * @desc memcache服务器数组
     * @var array
     */
    private $servers;
    
    /**
     * @desc set的第二个参数
     * @var $flags
     */
    private $flags;
    
    /**
     * @desc iMemcache实例
     * @var iMemcache
     */
    private static $_instance;
    
    /**
     * @desc 获取对象
     * @author YangLong
     * @date 2015-08-11
     * @return iMemcache
     */
    public static function getInstance()
    {
        if (! (self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-08-11
     */
    public function __construct()
    {
        $this->memcache = new Memcache();
        
        $this->servers = Yii::app()->params['memcache_conn'];
        if (! empty($this->servers) && is_array($this->servers)) {
            foreach ($this->servers as $_server) {
                if (is_array($_server) && isset($_server['ip']) && isset($_server['ip'])) {
                    $this->memcache->addServer($_server['ip'], $_server['port']);
                }
            }
        }
    }
    
    /**
     * @desc 向key存储一个元素值为 var
     * @param string $key 要设置值的key
     * @param mixed $var 要存储的值，字符串和数值直接存储，其他类型序列化后存储
     * @param int $expire 当前写入缓存的数据的失效时间。如果此值设置为0表明此数据永不过期。你可以设置一个UNIX时间戳或 以秒为单位的整数（从当前算起的时间差）来说明此数据的过期时间，但是在后一种设置方式中，不能超过 2592000秒（30天）
     * @param int $flag 使用MEMCACHE_COMPRESSED指定对值进行压缩(使用zlib)
     * @author YangLong
     * @date 2015-08-11
     * @return boolean 成功时返回 TRUE， 或者在失败时返回 FALSE
     */
    public function set($key, $var, $expire, $flag = MEMCACHE_COMPRESSED)
    {
        return $this->memcache->set($key, $var, $flag, $expire);
    }
    
    /**
     * @desc 从服务端检回一个元素
     * @param string $key 要获取值的key或key数组
     * @author YangLong
     * @date 2015-08-11
     * @return mixed 返回key对应的存储元素的字符串值或者在失败或key未找到的时候返回FALSE
     */
    public function get($key)
    {
        return $this->memcache->get($key, $this->flags);
    }
    
    /**
     * @desc 从服务端删除一个元素
     * @param string $key
     * @param int $timeout
     * @author YangLong
     * @date 2015-09-17
     * @return boolean
     */
    public function delete($key, $timeout = 0)
    {
        return $this->memcache->delete($key, $timeout);
    }
    
    /**
     * @desc 增加一个元素的值
     * @param string $key
     * @param int $value
     * @author YangLong
     * @date 2015-09-20
     * @return boolean
     */
    public function increment($key, $value = 1)
    {
        return $this->memcache->increment($key, $value);
    }
    
    /**
     * @desc 减小元素的值
     * @param string $key
     * @param int $value
     * @author YangLong
     * @date 2015-09-20
     * @return boolean
     */
    public function decrement($key, $value = 1)
    {
        return $this->memcache->decrement($key, $value);
    }
    
    /**
     * @desc 返回$flags变量当前值
     * @author YangLong
     * @date 2015-08-11
     * @return int
     */
    public function getflags()
    {
        return $this->flags;
    }
    
}
