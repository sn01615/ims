<?php

/**
 * @desc return_response_history主表
 * @author liaojianwen
 * @date 2015-06-17
 */
class ReturnHistoryDAO extends BaseDAO
{
    
    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-06-17
     * @return ReturnHistoryDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-06-17
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'return_response_history';
        $this->primaryKey = 'return_response_history_id';
        $this->created = 'create_time';
        $this->return = 'return_request';
        $this->detail = 'return_request_detail';
        
        $this->shop = 'shop';
    }
    
    /**
     * @desc 获取历史信息
     * @param  int $returnid
     * @param  array $shopId
     * @author liaojianwen
     * @date 2015-06-24
     */
    public function getReturnHistory($returnid, $shopId)
    {
        $selects = 'a.author,a.activity,a.fromState,a.toState,a.note,a.creationDate,a.sellerReturnAddr_name,a.sellerReturnAddr_street1,
        a.sellerReturnAddr_street2,a.sellerReturnAddr_city,a.sellerReturnAddr_county,a.sellerReturnAddr_stateOrProvince,a.sellerReturnAddr_country,
        a.sellerReturnAddr_postalCode,a.sellerReturnAddr_any,a.escalateReason,a.trackingNumber,a.carrierUsed,a.partialRefundAmount,a.rma';
        $condition = "b.shop_id in ({$shopId}) and a.return_id={$returnid}";
        $this->dbCommand->reset();
        $result = $this->dbCommand->select($selects)
            ->from("{$this->tableName} a")
            ->join("{$this->return} b", 'b.return_request_id= a.return_id')
            ->where($condition)
            ->order('a.creationDate DESC')
            ->queryAll();
        return $result;
    }
    
    /**
     * @desc 通过caseid获取return ID
     * @param $caseid
     * @param $shopId
     * @author liaojianwen
     * @date 2015-07-07
     * @return Ambigous <mixed, string>
     */
    public function getReturnRequest($caseid, $shopId)
    {
        $selects = 'a.returnId_id';
        $conditions = "a.shop_id in ({$shopId}) and b.S_eI_eBPCaseId={$caseid}";
        $result = $this->dbCommand->reset()
            ->select($selects)
            ->from("{$this->return} a")
            ->join("{$this->detail} b", "a.return_request_id = b.return_id")
            ->where($conditions)
            ->queryScalar();
        return $result;
    }
    
//    /**
//     * @desc 获取退货地址
//     * @param $returnId_id
//     * @author liaojianwen
//     * @date 2015-06-29
//     * @return mixed
//     */
//    public function getReturnAddr($returnId_id)
//    {
//        $selects = 'h.sellerReturnAddr_name name,sellerReturnAddr_street1 street1,sellerReturnAddr_street2 street2,sellerReturnAddr_city city,sellerReturnAddr_county county,
//        			sellerReturnAddr_stateOrProvince stateOrProvince,sellerReturnAddr_country country,sellerReturnAddr_postalCode postalCode,sellerReturnAddr_any any';
//        $conditions = "r.returnId_id ={$returnId_id}";
//        $result = $this->dbCommand->reset()
//            ->select($selects)
//            ->from("{$this->tableName} h")
//            ->join("{$this->return} r", "r.return_request_id= h.return_id")
//            ->where($conditions)
//            ->limit(1)
//            ->queryRow();
//        return $result;
//    }
    
    /**
     * @desc 通过returnId_id 获取request 历史
     * @param $returnId_id
     * @param $sellerId
     * @author liaojianwen
     * @date 2015-06-29
     * @return unknown
     */
    public function getReturn2Case($returnId_id, $sellerId)
    {
        $selects = 'h.return_id,h.author,h.activity,h.fromState,h.toState,h.note,h.creationDate,h.sellerReturnAddr_name,h.sellerReturnAddr_street1,
        h.sellerReturnAddr_street2,h.sellerReturnAddr_city,h.sellerReturnAddr_county,h.sellerReturnAddr_stateOrProvince,h.sellerReturnAddr_country,
        h.sellerReturnAddr_postalCode,h.sellerReturnAddr_any,h.escalateReason,h.trackingNumber,h.carrierUsed,h.partialRefundAmount';
        $conditions = "s.seller_id = {$sellerId} and r.returnId_id ={$returnId_id}";
        $result = $this->dbCommand->reset()
            ->select($selects)
            ->from("{$this->tableName} h")
            ->join("{$this->return} r", "r.return_request_id = h.return_id")
            ->join("{$this->shop} s", "s.shop_id = r.shop_id")
            ->where($conditions)
            ->order("h.creationDate DESC")
            ->queryAll();
        return $result;
    }
    
}
