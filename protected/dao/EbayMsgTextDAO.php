<?php

/**
 * @desc msg_text表操作类
 * @author YangLong
 * @date 2015-06-23
 */
class EbayMsgTextDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-06-23
     * @return EbayMsgTextDAO
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
        $this->tableName = 'msg_text';
        $this->primaryKey = 'msg_text_id';
        $this->created = 'create_time';
    }

}