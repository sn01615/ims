<?php

/**
 * @desc case_dispute_message操作类
 * @author YangLong
 * @date 2015-04-07
 */
class CaseDisputeMessageDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-04-07
     * @return CaseDisputeMessageDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-04-07
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'case_dispute_message';
        $this->primaryKey = 'case_dispute_message_id';
        $this->created = 'create_time';
        $this->case = 'case';
    }
    
	/**
     * @desc 获取卖家发起case的历史对话
     * @param caseid  case的id
     * @param shopId  当前用户所拥有店铺的id
     * @author lvjianfei
     * @return resultArr 
     * @date 2015-04-08
     */
    public function getCaseDisputeMessage($caseid,$shopId){
    	$selects = 'a.case_id,a.MessageCreationTime,a.MessageSource,a.MessageText';
    	$condition = "b.shop_id in ({$shopId}) and a.case_id={$caseid}";
    	$this->dbCommand->reset();
    	$result = $this->dbCommand->select($selects)
    		->from("{$this->tableName} a")
    		->join("{$this->case} b",'a.case_id = b.case_id')
    		->where($condition)
    		->order("a.MessageCreationTime desc")
    		->queryAll();
    	return $result;
    }
    
}