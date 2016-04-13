<?php

/**
 * @desc ebay_listing主表
 * @author liaojianwen
 * @date 2015-07-28
 */
class EbayListingDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-07-28
     * @return EbayListingDAO
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
        $this->tableName = 'ebay_listing';
        $this->primaryKey = 'listing_id';
        $this->created = 'create_time';
        
        $this->shop = 'shop';
    }
}