<?php

/**
 * @desc case_response_history操作类
 * @author lvjianfei
 * @date 2015-04-02
 */
class CaseHistoryDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author lvjianfei
     * @date 2015-04-02
     * @return CaseHistoryDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author lvjianfei
     * @date 2015-04-02
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'case_response_history';
        $this->primaryKey = 'case_response_id';
        $this->created = 'create_time';
        $this->case = 'case';
        $this->returnDet  = 'return_request_detail';
        $this->returnHis = 'return_response_history';
    }
    
	/**
     * @desc 获取买家发起case的历史对话
     * @param caseid  case的id
     * @param shopId  当前用户所拥有店铺的id
     * @author lvjianfei
     * @date 2015-04-08
     * @return resultArr 
     */
    public function getCaseHistory($caseid, $shopId)
    {
        $selects = 'a.author_role,a.author_userId,a.note,a.creationDate,a.activityDetial_description';
        $condition = "b.shop_id in ({$shopId}) and a.case_id={$caseid}";
        $this->dbCommand->reset();
        $result = $this->dbCommand->select($selects)
            ->from("{$this->tableName} a")
            ->join("{$this->case} b", 'a.case_id = b.case_id')
            ->where($condition)
            ->order('a.creationDate desc')
            ->queryAll();
        
        $selects = 'h.return_id,h.author author_role,h.activity activityDetial_description,h.fromState,h.toState,h.note,h.creationDate,h.sellerReturnAddr_name,h.sellerReturnAddr_street1,
        h.sellerReturnAddr_street2,h.sellerReturnAddr_city,h.sellerReturnAddr_county,h.sellerReturnAddr_stateOrProvince,h.sellerReturnAddr_country,
        h.sellerReturnAddr_postalCode,h.sellerReturnAddr_any,h.escalateReason,h.trackingNumber,h.carrierUsed,h.partialRefundAmount';
        $conditions = "d.S_eI_eBPCaseId =(select caseId_id from `{$this->case}` where case_id = {$caseid} )";
        $result1 = $this->dbCommand->reset()
            ->select($selects)
            ->from("{$this->returnHis} h")
            ->join("{$this->returnDet} d", "h.return_id= d.return_id")
            ->where($conditions)
            ->order("h.creationDate DESC")
            ->queryAll();
        $res = array_merge($result, $result1);
        $createDate = array();
        foreach ($res as $key => $value) {
            $createDate[$key] = $value['creationDate'];
        }
        array_multisort($createDate, SORT_DESC, $res);
        
        return $res;
    }
}