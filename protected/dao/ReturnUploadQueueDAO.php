<?php

/**
 * @desc Return更新队列表/return_upload_queue
 * @author liaojianwen
 * @date 2015-06-30
 */
class ReturnUploadQueueDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-06-30
     * @return ReturnUploadQueueDAO
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
        $this->tableName = 'return_upload_queue';
        $this->primaryKey = 'return_upload_queue_id';
        $this->created = 'create_time';
    }
    
    /**
     * @desc 取出Return上传队列数据
     * @param number $limit
     * @author liaojianwen
     * @date 2015-07-01
     * @return Ambigous <multitype:, mixed>|boolean
     */
    public function getReturnUploadQueueData($limit = 1)
    {
        $this->begintransaction();
        try {
            $_time = time();
            $table = $this->tableName;
            $conditions = "process_sign=:process_sign or (lastruntime<:lastruntime and runcount<:runcount)";
            $params = array(
                ':process_sign' => boolConvert::toInt01(false),
                ':lastruntime' => $_time - EnumOther::RETURN_REUPLOAD_TIME,
                ':runcount' => EnumOther::MAX_RUN_COUNT
            );
            $data = $this->dbCommand->reset()
                ->select('return_upload_queue_id,upload_type,upload_data,token')
                ->from($table)
                ->where($conditions, $params)
                ->limit($limit)
                ->order("priority desc,return_upload_queue_id asc")
                ->queryAll();
            
            if (! empty($data)) {
                $queueIds = array();
                foreach ($data as $key => $value) {
                    $queueIds[] = $value['return_upload_queue_id'];
                }
                $queueIds = implode(',', $queueIds);
                $columns = array(
                    'process_sign' => boolConvert::toInt01(true),
                    'lastruntime' => $_time
                );
                $conditions = "return_upload_queue_id in ({$queueIds})";
                $this->dbCommand->reset()->update($table, $columns, $conditions);
                $this->dbCommand->reset()
                    ->setText("UPDATE {$table} SET runcount = runcount + 1 WHERE return_upload_queue_id IN ({$queueIds})")
                    ->execute();
                $this->commit();
                return $data;
            } else {
                $this->rollback();
                return false;
            }
        } catch (Exception $e) {
            iMongo::getInstance()->setCollection('getReturnUploadQueueData')->insert(array(
                'getCode' => $e->getCode(),
                'getFile' => $e->getFile(),
                'getLine' => $e->getLine(),
                'getMessage' => $e->getMessage(),
                'getPrevious' => $e->getPrevious(),
                'getTrace' => $e->getTrace(),
                'getTraceAsString' => $e->getTraceAsString(),
                'time' => time()
            ));
            $this->rollback();
            return false;
        }
    }
    
   
    
}