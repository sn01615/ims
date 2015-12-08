<?php
/**
 * @desc mongodb操作类，http://www.jb51.net/article/50038.htm
 * @author shiyongbao
 * @date 2014-10-17
 */
class CMongodbHelper {
	/**
	 * @var array
	 */
    private static $_instance;
    /**
     * @var Mongo
     */
    private $mongo;
    /**
     * @var MongoDB
     */
    private $mongodb;
    /**
     * @desc 当前数据库名
     */    
    private $curr_db_name;
    private $error;

    /**
     * @desc 单例模式
     * @author shiyongbao
     * @date 2014-10-24
     */
    private function __construct($hostName = 'default'){
    	try {
    		$hostInfo = Yii::app()->params['mongodb_conn'][$hostName];
    		$this->mongo = new Mongo("mongodb://{$hostInfo['username']}:{$hostInfo['password']}@{$hostInfo['ip']}:{$hostInfo['port']}");
    		
    		$this->setDBName(Yii::app()->params['mongodbList'][0]);	//默认选择OMS数据库
    	} catch ( MongoConnectionException $e ) {
    		$this->error = $e->getMessage ();
    		return false;
    	}
    }
    
    /**
     * @desc 创建__clone方法防止对象被复制克隆
     * @author shiyongbao
     * @date 2014-10-24
     */
    private function __clone(){
    }
    
    /**
     * @desc 用双冒号::操作符访问静态方法获取实例$mongo = CMongodbHelper::getInstance();
     * @author shiyongbao
     * @date 2014-10-24
     * @return CMongodbHelper
     */
    public static function getInstance($hostName = 'default')
    {
        if(!(self::$_instance[$hostName] instanceof self)){
        	self::$_instance[$hostName] = new self($hostName);
        }
        return self::$_instance[$hostName];
    }

    /**
     * @desc 设置当前数据库的名称
     * @param string $dbname
     * @author shiyongbao
     * @date 2014-10-24
     */
    public function setDBName($dbname)
    {
        $this->curr_db_name = $dbname;
    }
    
    /**
     * @desc Gets a database
     * @param string $dbName
     * @date 2014-10-29
     * @author ChenLuoyong
     */
    public function selectDB($dbName)
    {
    	$this->mongodb = $this->mongo->selectDB($dbName);
    }

    /**
     * @desc 创建索引：如索引已存在，则返回。
     * @param string $dbname
     * @param array $index 索引-array("id"=>1)-在id字段建立升序索引
     * @param array $index_param 其它条件-是否唯一索引等   
     * @return  bool
     * @author shiyongbao
     * @date 2014-10-24
     */
    public function ensureIndex($table_name, $index, $index_param=array())
    {
        $dbname = $this->curr_db_name;
        $index_param['w'] = 1;
        try {
            $this->mongo->$dbname->$table_name->ensureIndex($index, $index_param);
            return true;
        }
        catch (MongoCursorException $e)
        {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @desc 插入记录
     * @param string $collectionName 集合名称
     * @param array $data 记录
     * @param array $dbname 数据库名
     * @return  bool
     * @author shiyongbao
     * @date 2014-10-24
     */
    public function insert($collectionName, $data,$dbname = null)
    {
    	if(empty($dbname)){
        	$dbname = $this->curr_db_name;
    	}
        try {
            $this->mongo->$dbname->$collectionName->insert($data, array('w'=>true));
            return true;
        }
        catch (MongoCursorException $e)
        {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @desc 查询表的记录数
     * @param string $dbname 表名
     * @return  null
     * @author shiyongbao
     * @date 2014-10-24
     */
    public function count($table_name)
    {
        $dbname = $this->curr_db_name;
        return $this->mongo->$dbname->$table_name->count();
    }     

    /**
     * @desc 更新记录
     * @param string $table_name 表名
     * @param array $condition 更新条件
     * @param array $newdata 新的数据记录
     * @param array $options 更新选择-upsert/multiple
     * @return  bool
     * @author shiyongbao
     * @date 2014-10-24
     */
    public function update($table_name, $condition, $newdata, $options=array())
    {
        $dbname = $this->curr_db_name;
        $options['w'] = 1;
        if (!isset($options['multiple'])) {
            $options['multiple'] = 0;
        }
        try {
            $this->mongo->$dbname->$table_name->update($condition, $newdata, $options);
            return true;
        }
        catch (MongoCursorException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }     

    /**
     * @desc 删除记录
     * @param string $table_name 表名
     * @param array $condition 删除条件
     * @param array $newdata 新的数据记录
     * @param array $options 删除选择-justOne
     * @return mongo bool
     * @author shiyongbao
     * @date 2014-10-24
     */
    public function remove($table_name, $condition=array(), $options=array())
    {
        $dbname = $this->curr_db_name;
        $options['w'] = 1;
        try {
            $this->mongo->$dbname->$table_name->remove($condition, $options);
            return true;
        }
        catch (MongoCursorException $e) {
            $this->error = $e->getMessage();
            return false;
     	}
    }

    /**
     * @desc 查找记录
     * @param string $table_name 表名
     * @param array $query_condition 字段查找条件
     * @param array $fields 获取字段
     * @param array $result_condition 查询结果限制条件-limit/sort等
     * @return array
     * @author shiyongbao
     * @date 2014-10-24
     */
    public function find($table_name, $query_condition=array(), $fields=array(), $result_condition=array())
    {
        $dbname = $this->curr_db_name;
        $cursor = $this->mongo->$dbname->$table_name->find($query_condition, $fields);
        if (!empty($result_condition['limit'])) {
            $cursor->limit($result_condition['limit']);
        }
        if (!empty($result_condition['skip'])) {
            $cursor->skip($result_condition['skip']);
        }
        if (!empty($result_condition['sort'])) {
            $cursor->sort($result_condition['sort']);
        }
        $result = array();
        
        try {
            while ($cursor->hasNext()) {
                $result[] = $cursor->getNext();
            }
        }
        catch (MongoConnectionException $e) {
            $this->error = $e->getMessage();
            return false;
        }
        catch (MongoCursorTimeoutException $e) {
            $this->error = $e->getMessage();
            return false;
        }
        return $result;
    }     

    /**
     * @desc 查找一条记录
     * @param string $table_name 表名
     * @param array $condition 字段查找条件
     * @param array $fields 获取字段
     * @return array or false
     * @author shiyongbao
     * @date 2014-10-24
     */
    public function findOne($table_name, $condition, $fields=array(),$dbname=null)
    {
    	if(empty($dbname)){
        	$dbname = $this->curr_db_name;
    	}
        return $this->mongo->$dbname->$table_name->findOne($condition, $fields);
    }     

    /**
     * @desc 获取当前错误信息
     * @return string
     * @author shiyongbao
     * @date 2014-10-24
     */
    public function getError()
    {
        return $this->error;
    }
    
    /**
     * @desc 数据库操作异常信息写入db_exception_log日志表
     * @param string $method 异常所在方法
     * @param string $exceptionInfo 异常信息
     * @param int $code 错误码
     * @param string $tableName 表名
     * @return boolean
     * @author ChenLuoyong
     * @date 2014-11-11
     */
    public function writeDBExceptionLog($method,$exceptionInfo,$code,$tableName)
    {
    	if(empty($method) && empty($exceptionInfo)){
    		return false;
    	}
    	$dbname = Yii::app()->params['mongodbList'][0];
    	$collectionName = Yii::app()->params['mongodbTableList'][$dbname][1];
    	$exception = array(
    			'method'=>$method,
    			'message'=>$exceptionInfo,
    			'code'=>$code,
    			'table'=>$tableName,
    			'time'=>date('Y-m-d H:i:s')
    		);
    	$this->insert($collectionName, $exception, $dbname);
    }
    
    /**
     * @desc 应用程序异常信息写入app_exception_log日志表
     * @param string $method 异常所在方法
     * @param string $exceptionInfo 异常信息
     * @param int $code 错误码
     * @return boolean
     * @author ChenLuoyong
     * @date 2014-11-11
     */
    public function writeAppExceptionLog($method,$exceptionInfo,$code)
    {
    	if(empty($method) && empty($exceptionInfo)){
    		return false;
    	}
    	$dbname = Yii::app()->params['mongodbList'][0];
    	$collectionName = Yii::app()->params['mongodbTableList'][$dbname][2];
    	$exception = array(
    			'method'=>$method,
    			'message'=>$exceptionInfo,
    			'code'=>$code,
    			'time'=>date('Y-m-d H:i:s')
    	);
    	$this->insert($collectionName, $exception, $dbname);
    }
}