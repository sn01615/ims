<?php

/**
 * @desc case_dispute_resolution操作类
 * @author YangLong
 * @date 2015-04-07
 */
class CaseDisputeResolutionDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-04-07
     * @return CaseDisputeResolutionDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-04-07
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'case_dispute_resolution';
        $this->primaryKey = 'case_dispute_resolution_id';
        $this->created = 'create_time';
    }
    
}