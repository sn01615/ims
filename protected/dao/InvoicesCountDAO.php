<?php

/**
 * @desc invoices_count操作类
 * @author liaojianwen
 * @date 2015-11-03
 */
class InvoicesCountDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-11-03
     * @return InvoicesCountDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-11-03
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'invoices_count';
        $this->primaryKey = 'invoices_count_id';
        $this->created = 'create_time';
        $this->order = 'ebay_orders';
    }
}