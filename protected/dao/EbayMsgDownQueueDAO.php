<?php

/**
 * @desc eb_msg_down_queue表操作类
 * @author YangLong
 * @date 2015-07-06
 */
class EbayMsgDownQueueDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-07-06
     * @return EbayMsgDownQueueDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-07-06
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'eb_msg_down_queue';
        $this->primaryKey = 'down_queue_id';
        $this->created = 'create_time';
    }

    /**
     * @desc 获取下载队列数据
     * @param number $limit
     * @author YangLong
     * @date 2015-01-28
     * @return mixed
     */
    public function getDownQueueData($limit = 1)
    {
        $table = $this->tableName;
        $conditions = "process_sign=:process_sign or (lastruntime<:lastruntime and runcount<:runcount)";
        // TODO枚举化
        $params = array(
            ':process_sign' => boolConvert::toInt01(false),
            ':lastruntime' => time() - 300 * 6,
            ':runcount' => 4
        );
        
        $data = $this->dbCommand->reset()
            ->select('down_queue_id,seller_id,shop_id,site_id,token,folder_id,start_time,end_time')
            ->from($table)
            ->where($conditions, $params)
            ->limit($limit)
            ->order("priority desc,down_queue_id asc")
            ->queryAll();
        
        if (! empty($data)) {
            $down_queue_id = array();
            foreach ($data as $key => $value) {
                $down_queue_id[] = $value['down_queue_id'];
            }
            $down_queue_id = implode(',', $down_queue_id);
            $columns = array(
                'process_sign' => 1,
                'lastruntime' => time()
            );
            $conditions = "down_queue_id in ({$down_queue_id})";
            $this->dbCommand->reset()->update($table, $columns, $conditions);
            $this->dbCommand->reset()
                ->setText("UPDATE {$table} SET runcount = runcount + 1 WHERE down_queue_id IN ({$down_queue_id})")
                ->execute();
            return $data;
        } else {
            return false;
        }
    }
}