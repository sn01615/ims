<?php

/**
 * @desc return_activity_option主表
 * @author liaojianwen
 * @date 2015-06-18
 */
class ReturnActivityDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-06-18
     * @return ReturnActivityDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-06-18
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'return_activity_option';
        $this->primaryKey = 'return_activity_option_id';
        $this->created = 'create_time';
        
        $this->shop = 'shop';
    }
}
