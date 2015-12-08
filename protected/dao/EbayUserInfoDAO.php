<?php

/**
 * @desc ebay_user_info操作类
 * @author YangLong
 * @date 2015-06-13
 */
class EbayUserInfoDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-03-31
     * @return EbayUserInfoDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-06-13
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'ebay_user_info';
        $this->primaryKey = 'ebay_user_info_id';
        $this->created = 'create_time';
    }
    
}