<?php

/**
 * @desc ebay_order_note表操作类
 * @author liaojianwen
 * @date 2015-05-21
 */
class EbayOrderNoteDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-05-21
     * @return EbayOrderNoteDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-05-21
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'ebay_order_note';
        $this->primaryKey = 'ebay_order_note_id';
        $this->created = 'create_time';
    }
}