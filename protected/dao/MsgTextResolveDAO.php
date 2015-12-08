<?php

/**
 * @desc msg_text_resolve表操作类
 * @author YangLong
 * @date 2015-06-23
 */
class MsgTextResolveDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-06-23
     * @return MsgTextResolveDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-06-23
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'msg_text_resolve';
        $this->primaryKey = 'msg_text_resolve_id';
        $this->updated = 'update_time';
        $this->created = 'create_time';
    }

}
