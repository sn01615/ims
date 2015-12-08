<?php

/**
 * @desc Case状态更新队列表/case_update_status_queue
 * @author YangLong
 * @date 2015-05-26
 */
class CaseUpdateStatusQueueDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-05-26
     * @return CaseUpdateStatusQueueDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-05-26
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'case_update_status_queue';
        $this->primaryKey = 'case_update_status_queue_id';
        $this->created = 'create_time';
    }

    /**
     * @desc 获取需要更新的队列信息
     * @param number $limit
     * @author YangLong
     * @date 2015-05-26
     * @return mixed
     */
    public function getUpdateStatusQueueData($limit = 1)
    {
        $_time = time();
        $table = $this->tableName;
        $conditions = "process_sign=:process_sign";
        $params = array(
            ':process_sign' => boolConvert::toInt01(false)
        );
        $data = $this->dbCommand->reset()
            ->select("{$this->primaryKey},shop_id,site_id,token")
            ->from($table)
            ->where($conditions, $params)
            ->limit($limit)
            ->order("priority desc,{$this->primaryKey} asc")
            ->queryAll();
        
        if (! empty($data)) {
            $queue_ids = array();
            foreach ($data as $key => $value) {
                $queue_ids[] = $value["{$this->primaryKey}"];
            }
            $queue_ids = implode(',', $queue_ids);
            $columns = array(
                'process_sign' => 1
            );
            $conditions = "{$this->primaryKey} in ({$queue_ids})";
            $this->dbCommand->reset()->update($table, $columns, $conditions);
            
            $this->dbCommand->reset()
                ->setText("UPDATE {$table} SET runcount = runcount + 1 WHERE {$this->primaryKey} IN ({$queue_ids})")
                ->execute();
            
            return $data;
        } else {
            return false;
        }
    }
    
}