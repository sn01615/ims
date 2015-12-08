<?php

/**
 * @desc msg_response_rul 表操作类
 * @author YangLong
 * @date 2015-11-14
 */
class MsgResponseUrlDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-11-14
     * @return MsgResponseUrlDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-11-14
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'msg_response_rul';
        $this->primaryKey = 'msg_id';
        // $this->created = 'create_time';
        // $this->updated = 'update_time';
    }

}
