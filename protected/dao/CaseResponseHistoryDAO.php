<?php

/**
 * @desc case_response_history表操作类/case_response_history
 * @author YangLong
 * @date 2015-03-31
 */
class CaseResponseHistoryDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-03-31
     * @return CaseResponseHistoryDAO
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
        $this->tableName = 'case_response_history';
        $this->primaryKey = 'case_id,number';
        $this->created = 'create_time';
        $this->updated = 'update_time';
    }
    
}