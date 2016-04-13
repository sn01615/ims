<?php

/**
 * @desc msg_create_log 表操作类
 * @author YangLong
 * @date 2015-12-19
 */
class MsgCreateLogDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-12-19
     * @return MsgCreateLogDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-12-19
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'msg_create_log';
        $this->primaryKey = 'msg_create_log_id';
        $this->created = 'create_time';
        // $this->updated = 'update_time';
    }
}
