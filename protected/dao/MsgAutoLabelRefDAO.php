<?php

/**
 * @desc msg_auto_label_ref 表操作类
 * @author YangLong
 * @date 2015-11-07
 */
class MsgAutoLabelRefDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-11-07
     * @return MsgAutoLabelRefDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-11-07
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'msg_auto_label_ref';
        $this->primaryKey = 'msg_id,msg_auto_label_id';
        // $this->created = 'create_time';
        // $this->updated = 'update_time';
    }
}
