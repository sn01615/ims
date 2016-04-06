<?php

/**
 * @desc ck1_bills 表操作类
 * @author YangLong
 * @date 2015-10-31
 */
class Ck1BillsDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-10-31
     * @return Ck1BillsDAO
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
        $this->tableName = 'ck1_bills';
        $this->primaryKey = 'ck1_bills_id';
        // $this->created = '';
        // $this->updated = '';
    }
}
