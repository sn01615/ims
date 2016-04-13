<?php

/**
 * @desc case_detail操作类
 * @author YangLong
 * @date 2015-03-31
 */
class CaseDetailDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-03-31
     * @return CaseDetailDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-03-27
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'case_detail';
        $this->primaryKey = 'case_detail_id';
        $this->created = 'create_time';
        $this->case = 'case';
    }

    /**
     * @desc 获取买家发起case的详细内容
     * @param caseid  case的id
     * @param shopId  当前用户所拥有店铺的id
     * @author lvjianfei
     * @return resultArr 
     * @date 2015-04-08
     */
    public function getCaseDetail($caseid, $shopId)
    {
        $selects = 'a.case_id,b.shop_id,b.i_transactionId,b.caseId_type,b.caseId_id,b.user_userId,b.user_role,b.otherParty_userId,b.otherParty_role,
            b.creationDate,b.caseAmount,b.currencyId,b.caseQuantity,b.i_itemId,b.i_transactionDate,a.case_detail_id,a.dSI_description,
            a.dRD_content,a.dRD_description,a.decisionDate,a.openReason,a.iBED_description,a.sS_carrierUsed,a.sS_trackingNumber,
            a.sS_deliveryDate,a.dSI_content,b.s_EBPINRStatus,b.s_EBPSNADStatus,a.decision';
        $condition = "b.shop_id in ({$shopId}) and a.case_id = {$caseid}";
        $result = $this->dbCommand->reset()
            ->select($selects)
            ->from("{$this->tableName} a")
            ->join("{$this->case} b", 'a.case_id = b.case_id')
            ->where($condition)
            ->limit(1)
            ->queryRow();
        
        // 别名替换
        $columns = array(
            'account',
            'nick_name'
        );
        $conditions = 'shop_id=:shop_id';
        $params = array(
            ':shop_id' => $result['shop_id']
        );
        $_shopinfo = ShopDAO::getInstance()->iselect($columns, $conditions, $params, false);
        
        $result['user_userId'] = str_ireplace($_shopinfo['account'], $_shopinfo['nick_name'], $result['user_userId']);
        $result['otherParty_userId'] = str_ireplace($_shopinfo['account'], $_shopinfo['nick_name'], $result['otherParty_userId']);
        
        unset($result['shop_id']);
        return $result;
    }
}
