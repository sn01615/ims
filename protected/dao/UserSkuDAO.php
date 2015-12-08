<?php

/**
 * @desc user_sku 表操作类
 * @author YangLong
 * @date 2015-11-19
 */
class UserSkuDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-11-19
     * @return UserSkuDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-11-19
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'user_sku';
        $this->primaryKey = 'seller_id,user_id,SKU';
        $this->created = 'create_time';
        // $this->updated = 'update_time';
    }

}
