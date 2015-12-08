<?php

/**
 * @desc Return更新队列表/return_update_queue
 * @author liaojianwen
 * @date 2015-06-30
 */
class ReturnUpdateQueueDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-06-30
     * @return ReturnUpdateQueueDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-06-30
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'return_update_queue';
        $this->primaryKey = 'return_update_queue_id';
        $this->created = 'create_time';
    }
    
    /**
     * @desc 取出Return_request下载队列数据
     * @param number $limit
     * @author jiaojianwen
     * @date 2015-17-02
     * @return Ambigous <multitype:, mixed>|boolean
     */
    public function getUpdateQueueData($limit = 1)
    {
        $this->begintransaction();
        try {
            $_time = time();
            $table = $this->tableName;
            $conditions = "process_sign=:process_sign or (lastruntime<:lastruntime and runcount<:runcount)";
            $params = array(
                ':process_sign' => boolConvert::toInt01(false),
                ':lastruntime' => $_time - EnumOther::HEARTBEAT_TIME * 6,
                ':runcount' => EnumOther::MAX_RUN_COUNT
            );
            $data = $this->dbCommand->reset()
                ->select('return_update_queue_id,seller_id,shop_id,AccountID,site_id,token,start_time,end_time')
                ->from($table)
                ->where($conditions, $params)
                ->limit($limit)
                ->order("priority desc,return_update_queue_id asc")
                ->queryAll();
            
            if (! empty($data)) {
                $return_request_queue_id = array();
                foreach ($data as $key => $value) {
                    $return_request_queue_id[] = $value['return_update_queue_id'];
                }
                $return_request_queue_id = implode(',', $return_request_queue_id);
                $columns = array(
                    'process_sign' => 1,
                    'lastruntime' => $_time
                );
                $conditions = "return_update_queue_id in ({$return_request_queue_id})";
                $this->dbCommand->reset()->update($table, $columns, $conditions);
                $this->dbCommand->reset()
                    ->setText("UPDATE {$table} SET runcount = runcount + 1 WHERE return_update_queue_id IN ({$return_request_queue_id})")
                    ->execute();
                $this->commit();
                return $data;
            } else {
                $this->rollback();
                return false;
            }
        } catch (Exception $e) {
            iMongo::getInstance()->setCollection('getReturnQueueData')->insert(array(
                'getCode' => $e->getCode(),
                'getMessage' => $e->getMessage(),
                'getTraceAsString' => $e->getTraceAsString(),
                'time' => time()
            ));
            $this->rollback();
            return false;
        }
    }
    
}