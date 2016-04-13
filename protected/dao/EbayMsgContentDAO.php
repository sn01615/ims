<?php

/**
 * @desc msg_content表操作类
 * @author YangLong
 * @date 2015-06-23
 */
class EbayMsgContentDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-06-23
     * @return EbayMsgContentDAO
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
        $this->tableName = 'msg_content';
        $this->primaryKey = 'msg_content_id';
        $this->created = 'create_time';
    }
}