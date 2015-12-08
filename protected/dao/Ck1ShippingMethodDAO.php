<?php

/**
 * @desc ck1_shipping_method 表操作类
 * @author YangLong
 * @date 2015-11-02
 */
class Ck1ShippingMethodDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-11-02
     * @return Ck1ShippingMethodDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-11-02
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'ck1_shipping_method';
        $this->primaryKey = 'ck1_shipping_method_id';
        // $this->created = 'create_time';
        // $this->updated = 'update_time';
    }

}
