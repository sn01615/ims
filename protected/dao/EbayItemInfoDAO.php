<?php

/**
 * @desc ebay_item_info表操作类
 * @author YangLong
 * @date 2015-05-04
 */
class EbayItemInfoDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-05-04
     * @return EbayItemInfoDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-05-04
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'ebay_item_info';
        $this->primaryKey = 'ebay_item_info_id';
        $this->created = 'create_time';
    }
}