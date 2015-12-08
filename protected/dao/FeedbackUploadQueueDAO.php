<?php

/**
 * @desc feedback回复队列表/feedback_upload_queue
 * @author liaojianwen
 * @date 2015-08-28
 */
class FeedbackUploadQueueDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-08-28
     * @return FeedbackUploadQueueDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-08-28
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'feedback_upload_queue';
        $this->primaryKey = 'feedback_upload_queue_id';
        $this->created = 'create_time';
    }
    
    /**
     * @desc 取出feedback上传队列数据
     * @param number $limit
     * @author liaojianwen
     * @date 2015-08-28
     * @return Ambigous <multitype:, mixed>|boolean
     */
    public function getFeedbackUploadQueueData($limit = 1)
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
            ->select('feedback_upload_queue_id,upload_type,upload_data,token')
            ->from($table)
            ->where($conditions, $params)
            ->limit($limit)
            ->order("priority desc,feedback_upload_queue_id asc")
            ->queryAll();
    
            if (! empty($data)) {
                $queueIds = array();
                foreach ($data as $key => $value) {
                    $queueIds[] = $value['feedback_upload_queue_id'];
                }
                $queueIds = implode(',', $queueIds);
                $columns = array(
                    'process_sign' => boolConvert::toInt01(true),
                    'lastruntime' => $_time
                );
                $conditions = "feedback_upload_queue_id in ({$queueIds})";
                $this->dbCommand->reset()->update($table, $columns, $conditions);
                $this->dbCommand->reset()
                ->setText("UPDATE {$table} SET runcount = runcount + 1 WHERE feedback_upload_queue_id IN ({$queueIds})")
                ->execute();
                $this->commit();
                return $data;
            } else {
                $this->rollback();
                return false;
            }
        } catch (Exception $e) {
            iMongo::getInstance()->setCollection('getFeedbackUploadQueueData')->insert(array(
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