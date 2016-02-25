<?php

/**
 * @desc Mongo Tool
 * @author YangLong
 * @date 2015-06-08
 */
class iMongo
{

    /**
     * @desc MongoClient实例
     * @var MongoClient
     */
    private $m;

    /**
     * @desc MongoDB实例
     * @var MongoDB
     */
    private $db;

    /**
     * @desc MongoCollection实例
     * @var MongoCollection
     */
    private $c;

    /**
     * @desc iMongo实例
     * @var iMongo
     */
    private static $_instance;
    
    /**
     * @desc 构造方法
     * @param string $connStr 链接字符串
     * @param string $dbname 数据库名
     * @param string $Collection 表名
     * @author YangLong
     * @date 2015-06-08
     */
    public function __construct($connStr = 'mongodb://127.0.0.1:27017', $dbname = 'ImsLogs', $Collection = '')
    {
        if (! empty($connStr)) {
            $this->setConnstr($connStr);
        }
        if (! empty($dbname)) {
            $this->setDbname($dbname);
        }
        if (! empty($Collection)) {
            $this->setCollection($Collection);
        }
    }
    
    /**
     * @desc 析构方法
     * @author YangLong
     * @date 2015-09-01
     */
    public function __destruct()
    {
        if (! empty($this->m)) {
            $this->m->close();
        }
    }
    
    /**
     * @desc 选择集合(表名)
     * @param string $Collection
     * @author YangLong
     * @date 2015-06-08
     * @return iMongo
     */
    public function setCollection($Collection)
    {
        $this->c = $this->db->selectCollection($Collection);
        return self::$_instance;
    }
    
    /**
     * @desc 设置数据库名
     * @param string $dbname
     * @author YangLong
     * @date 2015-06-08
     * @return iMongo
     */
    public function setDbname($dbname = 'ImsLogs')
    {
        $this->db = $this->m->selectDB($dbname);
        return self::$_instance;
    }

    /**
     * @desc 设置mongo链接字符串
     * @param string $connStr
     * @author YangLong
     * @date 2015-06-08
     * @return iMongo
     */
    public function setConnstr($connStr = 'mongodb://127.0.0.1:27017')
    {
        $this->m = new MongoClient($connStr);
        return self::$_instance;
    }
    
    /**
     * @desc 获取对象
     * @author YangLong
     * @date 2015-03-30
     * @return iMongo
     */
    public static function getInstance()
    {
        if (! (self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * @desc 插入一条记录
     * @param array $a 。。。
     * @author YangLong
     * @date 2015-06-08
     * @return bool|array
     * @throws Exception
     */
    public function insert($a)
    {
        if (empty($this->c)) {
            throw new Exception('$Collection is empty.');
        }
        
        $this->utf8_encode_deep($a);
        
        return $this->c->insert($a);
    }
    
    /**
     * @desc 将数组或字符串转码为utf-8
     * @param mixed $input
     * @date 2015-07-14
     */
    function utf8_encode_deep(&$input)
    {
        if (is_string($input)) {
            $input = utf8_encode($input);
        } elseif (is_array($input)) {
            foreach ($input as &$value) {
                $this->utf8_encode_deep($value);
            }
            unset($value);
        } elseif (is_object($input)) {
            $vars = array_keys(get_object_vars($input));
            foreach ($vars as $var) {
                $this->utf8_encode_deep($input->$var);
            }
        }
    }
    
    /**
     * @desc 更新一条记录
     * @param array $criteria 条件
     * @param array $new_object 
     * @param array $options 
     * @author YangLong
     * @date 2015-06-08
     * @return bool|array
     * @throws Exception
     */
    public function update($criteria, $new_object, $options = array())
    {
        if (empty($this->c)) {
            throw new Exception('$Collection is empty.');
        }
        return $this->c->update($criteria, $new_object, $options);
    }

    /**
     * @desc 查询一条记录
     * @param array $query 条件数组
     * @param array $fields 字段列表
     * @author YangLong
     * @date 2015-06-08
     * @return mixed
     * @throws Exception
     */
    public function findOne($query, $fields)
    {
        if (empty($this->c)) {
            throw new Exception('$Collection is empty.');
        }
        return $this->c->findOne($query, $fields);
    }

    /**
     * @desc 查询满足条件的记录
     * @param array $query 条件数组
     * @param array $fields 字段列表
     * @author YangLong
     * @date 2015-06-08
     * @return mixed
     * @throws Exception
     */
    public function find($query, $fields)
    {
        if (empty($this->c)) {
            throw new Exception('$Collection is empty.');
        }
        return $this->c->findOne($query, $fields);
    }
    
    /**
     * @desc MongoCollection::count — 返回集合中的文档数量
     * @param array $query            
     * @param number $limit            
     * @param number $skip            
     * @author YangLong
     * @date 2016-02-25
     * @return int
     */
    public function count($query, $limit = 0, $skip = 0)
    {
        if (empty($this->c)) {
            throw new Exception('$Collection is empty.');
        }
        return $this->c->count($query, $limit, $skip);
    }
    
    /**
     * @desc 获取MongoCollection对象
     * @param string $Collection
     * @author YangLong
     * @date 2015-08-24
     * @throws Exception
     * @return MongoCollection
     */
    public function getCollection($Collection = '')
    {
        if (! empty($Collection)) {
            $this->c = $this->db->selectCollection($Collection);
        }
        if (empty($this->c)) {
            throw new Exception('$Collection is empty.');
        }
        return $this->c;
    }
    
}
