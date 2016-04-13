<?php

/**
 * @desc ebay_orders_down表操作类
 * @author YangLong
 * @date 2015-06-14
 */
class EbayOrdersDownDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-06-14
     * @return EbayOrdersDownDAO
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
        $this->tableName = 'ebay_orders_down';
        $this->primaryKey = 'ebay_orders_down_id';
        $this->created = 'create_time';
    }

    /**
     * @desc 获取区需要解析的订单数据
     * @param number $limit
     * @author YangLong
     * @date 2015-06-14
     * @return Ambigous <multitype:, mixed>
     */
    public function getOrdersArray($limit = 1)
    {
        $conditions = 'process_sign=:false or (process_sign=:true and picktime<:picktime and pickcount<:pickcount)';
        $params = array(
            ':false' => boolConvert::toInt01(false),
            ':true' => boolConvert::toInt01(true),
            ':picktime' => time() - 60,
            ':pickcount' => 5
        );
        $ordersArray = $this->dbCommand->reset()
            ->select($this->primaryKey . ',shop_id,base64data')
            ->from($this->tableName)
            ->where($conditions, $params)
            ->limit($limit)
            ->queryAll();
        $columns = array(
            'process_sign' => boolConvert::toInt01(true),
            'picktime' => time()
        );
        if (! empty($ordersArray)) {
            $pids = array();
            foreach ($ordersArray as $row) {
                $pids[] = $row[$this->primaryKey];
            }
            $pids = implode(',', $pids);
            $conditions = "{$this->primaryKey} in ({$pids})";
            $params = array();
            $this->iupdate($columns, $conditions, $params);
            
            $this->increase('pickcount', "{$this->primaryKey} in ({$pids})");
        }
        return $ordersArray;
    }
}
