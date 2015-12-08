<?php

/**
 * @desc msg_label_ref 表操作类
 * @author YangLong
 * @date 2015-08-26
 */
class MsgLabelRefDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-08-26
     * @return MsgLabelRefDAO
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
        $this->tableName = 'msg_label_ref';
        $this->primaryKey = 'msg_label_id,msg_id';
        // $this->created = '';
    }

}