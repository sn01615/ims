<?php

/**
 * @desc feedback下载/更新队列表/Feedback_update_queue
 * @author 廖建文
 * @date 2015-05-18
 */
class FeedbackUpdateQueueDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-04-20
     * @return FeedbackUpdateQueueDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-05-18
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'feedback_update_queue';
        $this->primaryKey = 'feedback_update_queue_id';
        $this->created = 'create_time';
    }
    
    /**
     * @desc 获取feedback队列数据
     * @author liaojianwen
     * @date 2015-05-19
     * @param $limit
     */
    public function getFeedbackUpdateQueueData($limit = 1)
    {
        $this->begintransaction();
        try {
            $_time = time();
            $conditions = "process_sign = :process_sign";
            $param = array(
                ':process_sign' => boolConvert::toInt01(false)
            );
            $data = $this->dbCommand->reset()
                ->select('feedback_update_queue_id,seller_id,shop_id,site_id,token')
                ->from($this->tableName)
                ->where($conditions, $param)
                ->limit($limit)
                ->order('feedback_update_queue_id asc')
                ->queryAll();
            if (! empty($data)) {
                $queue_ids = array();
                foreach ($data as $key => $value) {
                    $queue_ids[] = $value['feedback_update_queue_id'];
                }
                $queue_ids = implode(',', $queue_ids);
                $columns = array(
                    'process_sign' => 1
                );
                $conditions = "feedback_update_queue_id in ({$queue_ids})";
                $this->dbCommand->reset()->update($this->tableName, $columns, $conditions);
                
                $this->dbCommand->reset()
                    ->setText("UPDATE {$this->tableName} SET runcount = runcount + 1 WHERE feedback_update_queue_id IN ({$queue_ids})")
                    ->execute();
                
                $this->commit();
                return $data;
            } else {
                $this->rollback();
                return false;
            }
        } catch (Exception $e) {
            iMongo::getInstance()->setCollection('getFeedbackUpdateQueueData')->insert(array(
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
    