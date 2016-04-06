<?php

/**
 * @desc case_document_info操作类
 * @author YangLong
 * @date 2015-03-31
 */
class CaseDocumentInfoDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-03-31
     * @return CaseDocumentInfoDAO
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
        $this->tableName = 'case_document_info';
        $this->primaryKey = 'case_id,number';
        $this->created = 'create_time';
        $this->updated = 'update_time';
    }
}