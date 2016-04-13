<?php

/**
 * @desc seller_config 表操作类
 * @author YangLong
 * @date 2015-11-26
 */
class SellerConfigDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-11-26
     * @return SellerConfigDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-11-26
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'seller_config';
        $this->primaryKey = 'seller_id,config_name';
        // $this->created = 'create_time';
        // $this->updated = 'update_time';
    }
}
