<?php

/**
 * @desc case_note操作类
 * @author lvjianfei
 * @date 2015-04-03
 */
class CaseNoteDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author lvjianfei
     * @date 2015-04-03
     * @return CaseNoteDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author lvjianfei
     * @date 2015-04-03
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'case_note';
        $this->primaryKey = 'case_note_id';
        $this->created = 'create_time';
        $this->case = 'case';
    }
}