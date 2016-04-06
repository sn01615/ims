<?php

/**
 * @desc Dispute_resolution主表/dispute_resolution
 * @author liaojianwen
 * @date 2015-08-18
 */
class DisputeResolutionDAO extends BaseDAO
{

    private $shop;
 // shop表名
    
    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-08-18
     * @return DisputeResolutionDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-08-18
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'dispute_resolution';
        $this->primaryKey = 'dispute_resolution_id';
        $this->created = 'create_time';
        
        $this->shop = 'shop';
    }
}