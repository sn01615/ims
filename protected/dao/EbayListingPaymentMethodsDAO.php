<?php

/**
 * @desc EBAY Listing 支付方式表
 * @author liaojianwen
 * @date 2015-07-29
 */
class EbayListingPaymentMethodsDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className
     * @return EbayListingPaymentMethodsDAO
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
        $this->tableName = 'ebay_listing_payment_methods';
        $this->primaryKey = 'listing_id,num';
        $this->fields = array(
            'listing_id',
            'num',
            'listing_id',
            'payment_methods'
        );
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
    }
}