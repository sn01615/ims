<?php

/**
 * @desc case_upload_queue操作类
 * @author liaojianwen
 * @date 2015-04-15
 */
class CaseUploadQueueDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-04-15
     * @return CaseUploadQueueDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-04-15
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'case_upload_queue';
        $this->primaryKey = 'case_upload_queue_id';
        $this->created = 'create_time';
    }
    
    /**
     * @desc 取出Case上传队列数据
     * @param number $limit
     * @author YangLong
     * @date 2015-04-16
     * @return mixed
     */
    public function getUploadQueueData($limit = 1)
    {
        $_time = time();
        $table = $this->tableName;
        $conditions = "process_sign=:process_sign or (lastruntime<:lastruntime and runcount<:runcount)";
        $params = array(
            ':process_sign' => boolConvert::toInt01(false),
            ':lastruntime' => $_time - EnumOther::CASE_REUPLOAD_TIME,
            ':runcount' => EnumOther::MAX_RUN_COUNT
        );
        $data = $this->dbCommand->reset()
            ->select('case_upload_queue_id,upload_type,upload_data,token')
            ->from($table)
            ->where($conditions, $params)
            ->limit($limit)
            ->order("priority desc,case_upload_queue_id asc")
            ->queryAll();
        
        if (! empty($data)) {
            $queueIds = array();
            foreach ($data as $key => $value) {
                $queueIds[] = $value['case_upload_queue_id'];
            }
            $queueIds = implode(',', $queueIds);
            $columns = array(
                'process_sign' => boolConvert::toInt01(true),
                'lastruntime' => $_time
            );
            $conditions = "case_upload_queue_id in ({$queueIds})";
            $this->dbCommand->reset()->update($table, $columns, $conditions);
            $this->dbCommand->reset()
                ->setText("UPDATE {$table} SET runcount = runcount + 1 WHERE case_upload_queue_id IN ({$queueIds})")
                ->execute();
            return $data;
        } else {
            return false;
        }
    }
    
}