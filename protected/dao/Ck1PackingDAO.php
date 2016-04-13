<?php

/**
 * @desc ck1_packing 表操作类
 * @author YangLong
 * @date 2015-10-31
 */
class Ck1PackingDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-10-31
     * @return Ck1PackingDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-10-31
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'ck1_packing';
        $this->primaryKey = 'ck1_packing_id';
        // $this->created = '';
        // $this->updated = '';
    }
}
