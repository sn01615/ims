<?php

/**
 * @desc EBAY Listing 多属性SKU表
 * @author liaojianwen
 * @date 2015-07-29
 */
class EbayListingSkuDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className
     * @return EbayListingSkuDAO
     * @author liaojianwen
     * @date 2015-07-29
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 初始化方法
     * @author liaojianwen
     * @date 2015-07-29
     */
    public function __construct()
    {
        $this->tableName = 'ebay_listing_skus';
        $this->primaryKey = 'sku_id';
        $this->fields = array(
            'sku_id',
            'listing_id',
            'sku',
            'start_price',
            'quantity',
            'quantity_sold'
        );
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
    }
}