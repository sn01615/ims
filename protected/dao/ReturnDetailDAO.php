<?php

/**
 * @desc return_request_detail主表/return_detail
 * @author liaojianwen
 * @date 2015-06-16
 */
class ReturnDetailDAO extends BaseDAO
{
    
    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-06-16
     * @return ReturnDetailDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-006-16
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'return_request_detail';
        $this->primaryKey = 'return_request_detail_id';
        $this->created = 'create_time';
        $this->return = 'return_request';
        
        $this->shop = 'shop';
    }
    
    /**
     * @desc 获取return 明细
     * @author liaojianwen
     * @date 2015-06-19
     */
    public function getReturnDetail($returnId,$shopId)
    {
        $selects = "r.returnId_id,S_buyerLoginName,r.responseDue_party_userId,r.responseDue_party_role,r.responseDue_respondByDate,r.creationDate,
        			S_CI_reason,S_state,S_status,D_iD_itemId,D_iD_itemTitle,D_iD_itemPicUrl,D_iD_itemPrice,D_iD_currencyId,D_iD_transactionId,D_iD_returnQuantity,
        			S_sRD_activityDue,S_sRD_respondByDate,D_rSI_sT_carrierUsed,D_rSI_sT_trackingNumber,D_rSI_sT_deliveryStatus,D_closeReason,S_eI_eBPCaseId,S_eI_caseType,s.nick_name S_sellerLoginName";
        $conditions = "r.shop_id in ({$shopId}) and d.return_id = {$returnId}";
        $result = $this->dbCommand->reset()
            ->select($selects)
            ->from("{$this->tableName} d")
            ->join("{$this->return} r", "d.return_id = r.return_request_id")
            ->join("{$this->shop} s", "s.shop_id = r.shop_id")
            ->where($conditions)
            ->limit(1)
            ->queryRow();
        if (Yii::app()->session['userInfo']['username'] == 'guest') {
            $result['flag'] = 1;
        } else {
            $result['flag'] = 0;
        }
        $colseTime = ReturnHistoryDAO::getInstance()->findByAttributes(array(
            'return_id' => $returnId
        ), array(
            'creationDate'
        ), array(
            'creationDate DESC'
        ));
        $result['closeTime'] = $colseTime['creationDate'];
        return $result;
    
    }
    
    /**
     * @desc 获取return 客户
     * @author liaojianwen
     * @date 2015-06-19
     */
    public function getReturnInfo($returnId,$sellerId)
    {
        $selects = "S_buyerLoginName,d.D_iD_itemId";
        
        return $this->dbCommand->reset()
                        ->select($selects)
                        ->from("{$this->return} r")
                        ->join("{$this->tableName} d","r.return_request_id = d.return_id")
                        ->join("{$this->shop} s","r.shop_id = s.shop_id")
                        ->where("d.return_id= {$returnId} and s.seller_id = {$sellerId}")
                        ->limit(1)
                        ->queryRow();
                        
    }
    
    
    /**
     * @desc 根据returnid 获取item信息
     * @param string $returnid
     * @param string $sellerId
     * @author liaojianwen
     * @date 2015-06-19
     * @return array
     */
    public function getItemInfoByReturnId($returnid,$sellerId)
    {
        $selects = "D_iD_itemId i_itemId,D_iD_transactionId i_transactionId ,s.site_id,s.token";
        $conditions = "d.return_id ={$returnid} and s.seller_id = {$sellerId}";
        $tokeninfo =$this->dbCommand->reset()
                        ->select($selects)
                        ->from("{$this->tableName} d")
                        ->join("{$this->return} r","r.return_request_id = d.return_id")
                        ->join("{$this->shop} s","s.shop_id = r.shop_id")
                        ->where($conditions)
                        ->limit(1)
                        ->queryRow(); 
         return $tokeninfo;
    }
    
    /**
     * @desc 根据return_id 获取requestID
     * @param int $returnid
     * @param array $shopidArr
     * @author liaojianwen
     * @date 2015-06-30
     */
   public function  getReturnID($returnid,$shopidArr)
   {
       $conditions = "shop_id in ({$shopidArr}) and return_request_id = {$returnid}";
       return $this->dbCommand->reset()
                       ->select('returnId_id')
                       ->from($this->return)
                       ->where($conditions)
                       ->queryScalar();
        
       
   }
   
    /**
     * @desc 获取退货地址
     * @param $returnId_id
     * @author liaojianwen
     * @date 2015-06-29
     * @return mixed
     */
    public function getReturnAddr($returnId_id)
    {
        $selects = 'h.D_sA_name name,D_sA_street1 street1,D_sA_street2 street2,D_sA_city city,D_sA_county county,
        			D_sA_stateOrProvince stateOrProvince,D_sA_country country,D_sA_postalCode postalCode,D_sA_any any';
        $conditions = "r.returnId_id ={$returnId_id}";
        $result = $this->dbCommand->reset()
            ->select($selects)
            ->from("{$this->tableName} h")
            ->join("{$this->return} r", "r.return_request_id= h.return_id")
            ->where($conditions)
            ->limit(1)
            ->queryRow();
        return $result;
    }
}