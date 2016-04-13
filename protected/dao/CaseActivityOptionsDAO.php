<?php

/**
 * @desc case_activity_options操作类
 * @author YangLong
 * @date 2015-04-10
 */
class CaseActivityOptionsDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-03-31
     * @return CaseActivityOptionsDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-03-27
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'case_activity_options';
        $this->primaryKey = 'case_activity_options_id';
        $this->created = 'create_time';
    }
}