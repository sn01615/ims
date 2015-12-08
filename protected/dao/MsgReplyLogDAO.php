<?php

/**
 * @desc Msg回复日志表/msg_reply_log
 * @author YangLong
 * @date 2015-05-22
 */
class MsgReplyLogDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-05-22
     * @return MsgReplyLogDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-05-22
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'msg_reply_log';
        $this->primaryKey = 'msg_reply_log_id';
        $this->created = 'create_time';
    }
    
}