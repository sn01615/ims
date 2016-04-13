<?php

/**
 * @desc ebay_listing_queue主表
 * @author liaojianwen
 * @date 2015-07-28
 */
class EbayListingQueueDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-07-28
     * @return EbayListingQueueDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-07-28
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'ebay_listing_queue';
        $this->primaryKey = 'down_queue_id';
        $this->created = 'create_time';
        
        $this->shop = 'shop';
    }

    /**
     * @desc 取ebay_listing_queue 队列数据
     * @param int $limit
     * @author liaojianwen
     * @date 2015-07-28
     */
    public function getListingQueueData($limit = 1)
    {
        if (empty($limit)) {
            return false;
        }
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
                ->select('down_queue_id,seller_id,shop_id,site_id,token,start_time,end_time')
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
                    'lastruntime' => $_time
                );
                $conditions = "down_queue_id in ({$down_queue_id})";
                $this->dbCommand->reset()->update($table, $columns, $conditions);
                $this->dbCommand->reset()
                    ->setText("UPDATE {$table} SET runcount = runcount + 1 WHERE down_queue_id IN ({$down_queue_id})")
                    ->execute();
                $this->commit();
                return $data;
            } else {
                $this->rollback();
                return false;
            }
        } catch (Exception $e) {
            iMongo::getInstance()->setCollection('getListingQueueData')->insert(
                array(
                    'getCode' => $e->getCode(),
                    'getFile' => $e->getFile(),
                    'getLine' => $e->getLine(),
                    'getTraceAsString' => $e->getTraceAsString(),
                    'time' => time()
                ));
            $this->rollback();
            return false;
        }
    }
}