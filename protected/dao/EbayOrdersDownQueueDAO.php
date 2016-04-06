<?php

/**
 * @desc ebay_orders_down_queue表操作类
 * @author YangLong
 * @date 2015-06-14
 */
class EbayOrdersDownQueueDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-06-14
     * @return EbayOrdersDownQueueDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-06-14
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'ebay_orders_down_queue';
        $this->primaryKey = 'ebay_orders_down_queue_id';
        $this->created = 'create_time';
    }

    /**
     * @desc 获取订单下载队列
     * @param number $limit
     * @author YangLong
     * @date 2015-06-14
     * @return mixed
     */
    public function getOrdersDownQueueData($limit = 1)
    {
        $_time = time();
        $table = $this->tableName;
        $conditions = "process_sign=:process_sign or (lastruntime<:lastruntime and runcount<:runcount)";
        $params = array(
            ':process_sign' => boolConvert::toInt01(false),
            ':lastruntime' => time() - 3600,
            ':runcount' => 4
        );
        $data = $this->dbCommand->reset()
            ->select($this->primaryKey . ',token,start_time,end_time,shop_id,seller_id,site_id')
            ->from($table)
            ->where($conditions, $params)
            ->limit($limit)
            ->order("priority desc,{$this->primaryKey} asc")
            ->queryAll();
        
        if (! empty($data)) {
            $queue_ids = array();
            foreach ($data as $key => $value) {
                $queue_ids[] = $value[$this->primaryKey];
            }
            $queue_ids = implode(',', $queue_ids);
            $columns = array(
                'process_sign' => 1,
                'lastruntime' => time()
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
