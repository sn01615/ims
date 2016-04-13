<?php

/**
 * @desc Dispute_messages主表/dispute_messages
 * @author liaojianwen
 * @date 2015-08-18
 */
class DisputeMsgDAO extends BaseDAO
{

    private $shop;
    // shop表名
    
    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-08-18
     * @return DisputeMsgDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-08-18
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'dispute_messages';
        $this->dispute = 'disputes';
        $this->primaryKey = 'dispute_messages_id';
        $this->created = 'create_time';
        
        $this->shop = 'shop';
    }

    /**
     * @desc 获取卖家发起cancel dispute的历史对话
     * @param disputeid  dispute的id
     * @param shopId  当前用户所拥有店铺的id
     * @author liaojianwen
     * @return resultArr
     * @date 2015-04-08
     */
    public function getCancelDisputeMessage($disputeid, $shopId)
    {
        $selects = 'a.disputes_id,a.MessageCreationTime,a.MessageSource,a.MessageText';
        $condition = "b.shop_id in ({$shopId}) and a.disputes_id={$disputeid}";
        $this->dbCommand->reset();
        $result = $this->dbCommand->select($selects)
            ->from("{$this->tableName} a")
            ->join("{$this->dispute} b", 'a.disputes_id = b.disputes_id')
            ->where($condition)
            ->order("a.MessageCreationTime desc")
            ->queryAll();
        return $result;
    }
}