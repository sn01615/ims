<?php

/**
 * @desc msg_label 表操作类
 * @author YangLong
 * @date 2015-08-26
 */
class MsgLabelDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-08-26
     * @return MsgLabelDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-08-26
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'msg_label';
        $this->primaryKey = 'msg_label_id';
        $this->created = 'create_time';
    }
    
}