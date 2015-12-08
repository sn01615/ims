<?php
/**
 * @desc casedispute处理类
 * @author lvjianfei
 * @date 2015-04-08
 */
class CaseDisputeModel extends BaseModel{
	
	/**
	 * @desc 覆盖父方法返回CaseDisputeModel对象
	 * @param string $className 需要实例化的类名
     * @author lvjianfei
     * @date 2015-04-08
     * @return CaseDisputeModel
	 */
	public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * @desc 获取卖家发起的Case列表
     * @param String  $Type      case类型
     * @param integer $page      页码
     * @param integer $pageSize  分页大小
     * @param string $status     查询条件状态
     * @param integer $itemId    查询条件itemNo
     * @param  stirng $cust      查询条件客户
     * @param string $query 判断是查询还是列表展示/query 为查询
     * author lvjianfei 
     * @date 2015-04-02
     * @reutrn array 列表信息
     */
    public function getCaseDisputeList($Type, $page, $pageSize, $status, $itemId, $cust, $query)
    {
        switch ($Type) {
            case EnumOther::CASE_CANCEL:
                $param = 'CANCEL_TRANSACTION';
                break;
            case EnumOther::CASE_UPI:
                $param = 'UPI';
                break;
        }
        // 获取店铺信息
        $parr = array();
        $parr['seller_id'] = Yii::app()->session['userInfo']['seller_id'];
        $parr['is_delete'] = boolConvert::toInt01(false);
        $parr['status'] = 1;
        // 获取切换店铺信息
        $siteId = isset(Yii::app()->session['switchInfo']['siteId']) ? Yii::app()->session['switchInfo']['siteId'] : - 1;
        $accountId = isset(Yii::app()->session['switchInfo']['accountId']) ? Yii::app()->session['switchInfo']['accountId'] : 0;
        if (is_numeric($siteId) && $siteId > - 1) {
            $parr['site_id'] = $siteId;
        }
        $return = array(
            'shop_id'
        );
        $shopArr = ShopDAO::getInstance()->findAllByAttributes($parr, $return);
        if (empty($shopArr)) {
            $result = $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户还未注册店铺');
            return $result;
        }
        $shopIdArr = array();
        foreach ($shopArr as $value) {
            $shopIdArr[] = $value['shop_id'];
        }
        $shopId = implode(',', $shopIdArr);
        if (is_numeric($accountId) && $accountId > 0) {
            $shopId = $accountId;
        }
        $paramArr['param'] = $param;
        $paramArr['shop_id'] = $shopId;
        // 开始查询数据库
        $res = CaseDisputeDAO::getInstance()->getCaseDisputeList($paramArr, $page, $pageSize, $status, $itemId, $cust, $query);
        if (empty($res['list'])) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '获取数据失败');
        }
        $result = $this->handleApiFormat(EnumOther::ACK_SUCCESS, $res, '');
        return $result;
    }
    
    /**
     * @desc 获取卖方发起case的详细信息
     * @param $caseid case的id
     * @param $type case的类型
     * @author lvjianfei
     * @date 2015-04-08
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function getCaseDisputeDetail($caseid, $type)
    {
        switch ($type) {
            case EnumOther::CASE_CANCEL:
                $params = 'TransactionMutuallyCanceled';
                break;
            case EnumOther::CASE_UPI:
                $params = 'BuyerHasNotPaid';
                break;
        }
        if (empty($caseid)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '返回id是空的');
        }
        $paramArr['param'] = $params;
        
        // 获取店铺信息
        $parr = array();
        $parr['seller_id'] = Yii::app()->session['userInfo']['seller_id'];
        $parr['is_delete'] = boolConvert::toInt01(false);
        $parr['status'] = 1;
        // 获取切换店铺信息
        $return = array(
            'shop_id'
        );
        $shopArr = ShopDAO::getInstance()->findAllByAttributes($parr, $return);
        if (empty($shopArr)) {
            $result = $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户还未注册店铺');
            return $result;
        }
        $shopIdArr = array();
        foreach ($shopArr as $value) {
            $shopIdArr[] = $value['shop_id'];
        }
        $shopId = implode(',', $shopIdArr);
        $res = CaseDisputeDAO::getInstance()->getCaseDisputeDetail($caseid, $shopId);
        
        if (isset(Yii::app()->session['switchInfo']['accountId']) && ! empty(Yii::app()->session['switchInfo']['accountId'])) {
            $shopId = Yii::app()->session['switchInfo']['accountId'];
        }
        
        $prenextID = CaseDisputeDAO::getInstance()->getPreNexID($res['DisputeCreatedTime'], $caseid, $paramArr, $shopId);
        if (empty($res)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '查询数据库失败');
        }
        $result['list'] = $res;
        $result['prenextID'] = $prenextID;
        
        // guest ItemID hide
        if (Yii::app()->session['userInfo']['user_id'] == 99999) {
            $result['user_id'] = 99999;
        }
        
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
    }
    
    /**
     * @desc 获取卖方发起case的历史对话
     * @param caseid     case的id
     * @author lvjianfei
     * @date 2015-04-08
     */
    public function getCaseDisputeMessage($caseid)
    {
    	$casedisputemsg = CaseDisputeMessageDAO::getInstance();
    	if(empty($caseid)){
    		return $this->handleApiFormat(EnumOther::ACK_FAILURE,'','返回id是空的');
    	}
    	//获取店铺信息
        $parr = array();
        $parr['seller_id'] = Yii::app()->session['userInfo']['seller_id'];
        $parr['is_delete'] = boolConvert::toInt01(false);
        $parr['status'] = 1;
        //获取切换店铺信息
        $return = array(
        	'shop_id'
        );
        $shopArr = ShopDAO::getInstance()->findAllByAttributes($parr, $return);
    	if (empty($shopArr)) {
            $result= $this->handleApiFormat(EnumOther::ACK_FAILURE,'','用户还未注册店铺');
            return $result;
        }
        $shopIdArr = array();
        foreach ($shopArr as $value) {
            $shopIdArr[] = $value['shop_id'];
        }
        $shopId = implode(',', $shopIdArr);
    	$result['list'] = $casedisputemsg->getCaseDisputeMessage($caseid,$shopId);
    	if(empty($result['list'])){
    		return $this->handleApiFormat(EnumOther::ACK_FAILURE,'','查询失败');
    	}
    	return $this->handleApiFormat(EnumOther::ACK_SUCCESS,$result,'');
    	}
    	
    	/**
    	 * @desc  新增case 查找订单
    	 * @param string $ItemID
    	 * @param string $BuyerUserID
    	 * @param string $shop_id
    	 * @param string $sellerId
    	 * @author liaojianwen
    	 * @date 2015-07-14
    	 */
    	public function searchOrder($ItemID,$BuyerUserID,$shop_id,$sellerId,$page,$pageSize)
    	{
    	  	if(empty($shop_id)){
    		    return $this->handleApiFormat(EnumOther::ACK_FAILURE,'','the shop_id can not be empty');
    	    }
    	     //获取店铺信息
            $parr = array();
            $parr['seller_id'] = Yii::app()->session['userInfo']['seller_id'];
            $parr['is_delete'] = boolConvert::toInt01(false);
            $parr['status'] = 1;
            $parr['shop_id'] = $shop_id;
            //获取切换店铺信息
            $return = array(
            	'shop_id'
            );   
            $shopArr = ShopDAO::getInstance()->findByAttributes($parr, $return);
        	 if (empty($shopArr)) {
                $result= $this->handleApiFormat(EnumOther::ACK_FAILURE,'','the store is unusable');
                return $result;
            }
            $rangeTime = time() - 45*24*60*60;//添加dispute 只能是45天内的订单
        	$result = EbayOrderTransactionDAO::getInstance()->searchOrder($ItemID,$BuyerUserID,$shopArr['shop_id'],$page,$pageSize,$rangeTime);
    	    if(empty($result)){
        		return $this->handleApiFormat(EnumOther::ACK_FAILURE,'','searched fail');
        	}
        	return $this->handleApiFormat(EnumOther::ACK_SUCCESS,$result,'');
    	}
}