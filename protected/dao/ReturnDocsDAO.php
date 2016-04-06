<?php

/**
 * @desc return_docs主表
 * @author liaojianwen
 * @date 2015-06-26
 */
class ReturnDocsDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-06-26
     * @return ReturnDocsDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-06-26
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'return_docs';
        $this->primaryKey = 'return_docs_id';
        $this->created = 'create_time';
        
        $this->shop = 'shop';
    }
}