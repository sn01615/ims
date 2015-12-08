<?php

/**
 * @desc case_dispute操作类
 * @author YangLong
 * @date 2015-04-07
 */
class CaseDisputeDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-04-07
     * @return CaseDisputeDAO
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
        $this->tableName = 'case_dispute';
        $this->primaryKey = 'case_dispute_id';
        $this->created = 'create_time';
        $this->case = 'case';
        $this->shop = 'shop';
        
    }
    
    /**
     * @desc 获取卖方发起case列表
     * @param $paramArr case类型
     * @param $page 页数
     * @pageSize $pageSize 页码
     * @param status case状态
     * @param $itemId itemID
     * @param $cust 用户id
     * @param $query 判断是查询还是列表展示/query 为查询
     * @author lvjianfei
     * @date 2015-04-08
     */
    public function getCaseDisputeList($paramArr, $page, $pageSize, $status, $itemId, $cust, $query)
    {
        $userConfig = UserModel::model()->getUserConfigs();
        $shopIds = implode(',', $userConfig['shops']);
        
        $limit = $pageSize;
        $offset = ($page - 1) * $limit;
        $selects = 'a.case_dispute_id, a.DisputeRecordType, a.BuyerUserID, a.SellerUserID, a.i_ItemID, a.DisputeID, 
    				a.DisputeCreatedTime, a.DisputeState, a.DisputeStatus, a.DisputeExplanation, a.DisputeReason, b.shop_id, a.case_id,b.caseId_type,b.s_cancelTransactionStatus,b.s_UPIStatus';
        $params = array(
            ':param' => $paramArr['param']
        );
        $express = "b.caseId_type = :param";
        
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            if (empty($shopIds)) {
                $shopIds = 0;
            }
            $express .= " and s.shop_id in ({$shopIds})";
        }
        if (! empty($status)) {
            if ($status == 'closed') {
                switch ($paramArr['param']) {
                    case 'UPI':
                        $express .= " and `s_UPIStatus`  in ('CLOSED','CLOSED_FVFCREDIT_NOSTRIKE','CLOSED_FVFCREDIT_STRIKE','CLOSED_NOFVFCREDIT_NOSTRIKE','CLOSED_NOFVFCREDIT_STRIKE','CS_CLOSED','EXPIRED','OTHER')";
                        break;
                    case 'CANCEL_TRANSACTION':
                        $express .= " and `s_cancelTransactionStatus`  in ('CANCELLED','CLOSED','CLOSED_FVFCREDIT','CLOSED_NOFVFCREDIT','CS_CLOSED','EXPIRED','OTHER')";
                        break;
                }
            }
            if ($status == 'handle') {
                switch ($paramArr['param']) {
                    case 'UPI':
                        $express .= " and `s_UPIStatus` not in ('CLOSED','CLOSED_FVFCREDIT_NOSTRIKE','CLOSED_FVFCREDIT_STRIKE','CLOSED_NOFVFCREDIT_NOSTRIKE','CLOSED_NOFVFCREDIT_STRIKE','CS_CLOSED','EXPIRED','OTHER')";
                        break;
                    case 'CANCEL_TRANSACTION':
                        $express .= " and `s_cancelTransactionStatus` not in ('CANCELLED','CLOSED','CLOSED_FVFCREDIT','CLOSED_NOFVFCREDIT','CS_CLOSED','EXPIRED','OTHER')";
                        break;
                }
            }
        }
        if (! empty($itemId)) {
            $express .= ' and a.i_ItemID like :id';
            $params[':id'] = '%' . $itemId . '%';
        }
        if (! empty($cust)) {
            $express .= ' and a.BuyerUserID like :cust';
            $params[':cust'] = '%' . $cust . '%';
        }
        $condition = "{$express} and b.shop_id in ({$paramArr['shop_id']})";
        
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            if (empty($shopIds)) {
                $shopIds = 0;
            }
            $condition .= " and b.shop_id in ({$shopIds})";
        }
        
        $this->dbCommand->reset();
        $this->dbCommand->select($selects, 'SQL_CALC_FOUND_ROWS')
            ->from("{$this->tableName} a")
            ->join("{$this->case} b", 'a.case_id=b.case_id')
            ->join("{$this->shop} s","s.shop_id = b.shop_id")
            ->where($condition, $params);
        $result['list'] = $this->dbCommand->limit($limit, $offset)
            ->order('a.DisputeCreatedTime DESC')
            ->queryAll();
        $result['count'] = $this->dbCommand->setText('select found_rows()')->queryScalar();
        
        $columns = array(
            'shop_id',
            'account',
            'nick_name'
        );
        $conditions = 'status=:status and is_delete=:is_delete';
        $params = array(
            ':status' => 1,
            ':is_delete' => boolConvert::toInt01(false)
        );
        $_shopInfo = ShopDAO::getInstance()->iselect($columns, $conditions, $params);
        $shopInfo = array();
        foreach ($_shopInfo as $_key => $_value) {
            $shopInfo[$_value['shop_id']] = $_value;
        }
        unset($_shopInfo);
        foreach ($result['list'] as &$value) {
            switch ($value['caseId_type']) {
                case 'CANCEL_TRANSACTION':
                    $status = $value['s_cancelTransactionStatus'];
                    break;
                case 'UPI':
                    $status = $value['s_UPIStatus'];
                    break;
            }
            
            if ($status == 'CLOSED' || $status == 'CLOSED_FVFCREDIT_NOSTRIKE' || $status == 'CLOSED_FVFCREDIT_STRIKE' || $status == 'CLOSED_NOFVFCREDIT_NOSTRIKE' || $status == 'CLOSED_NOFVFCREDIT_STRIKE' || $status == 'CS_CLOSED' || $status == 'CANCELLED' || $status == 'CLOSED_FVFCREDIT' || $status == 'CLOSED_NOFVFCREDIT' || $status == 'EXPIRED' || $status == 'OTHER' || $value['DisputeState'] == 'Closed') {
                $value['status'] = 'closed';
            } else {
                $value['status'] = 'processing';
            }
            // 替换别名
            $value['SellerUserID'] = str_ireplace($shopInfo[$value['shop_id']]['account'], $shopInfo[$value['shop_id']]['nick_name'], $value['SellerUserID']);
            
            // guest ItemID hide
            if (Yii::app()->session['userInfo']['user_id'] == 99999) {
                $value['i_ItemID'] = preg_replace('/(\d{8})\d{4}/', '$1****', $value['i_ItemID']);
            }
        }
        $result['page'] = array(
            'page' => $page,
            'pagesize' => $pageSize
        );
        return $result;
    }
    
    /**
     * @desc 上、下一页的id
     * @param caseid  case的id
     * @author lvjianfei
     * @return resultArr 上一页和下一页的ID
     * @date 2015-04-08
     */
    public function getPreNexID($DisputeCreatedTime, $caseid, $paramArr, $shopId)
    {
        // 下一页的id
        $param = array(
            ':param' => $paramArr['param']
        );
        $next = "b.shop_id in ({$shopId}) and a.DisputeCreatedTime<{$DisputeCreatedTime}";
        $next .= ' and a.case_id <> ' . $caseid;
        $next .= ' and a.DisputeReason = :param';
        $nextID = $this->dbCommand->reset()
            ->select('a.case_id nextID')
            ->from("{$this->tableName} a")
            ->where($next, $param)
            ->join("{$this->case} b", 'a.case_id=b.case_id')
            ->order('a.DisputeCreatedTime desc')
            ->limit(1)
            ->queryRow();
        // 上一页id
        $pre = "b.shop_id in ({$shopId}) and a.DisputeCreatedTime>{$DisputeCreatedTime}";
        $pre .= ' and a.case_id <> ' . $caseid;
        $pre .= ' and a.DisputeReason = :param';
        $preID = $this->dbCommand->reset()
            ->select('a.case_id preID')
            ->from("{$this->tableName} a")
            ->join("{$this->case} b", 'a.case_id=b.case_id')
            ->order('a.DisputeCreatedTime asc')
            ->where($pre, $param)
            ->limit(1)
            ->queryRow();
        
        $resultArr = array();
        if (! empty($nextID)) {
            $resultArr = array_merge($resultArr, $nextID);
        }
        if (! empty($preID)) {
            $resultArr = array_merge($resultArr, $preID);
        }
        return $resultArr;
    }

    /**
     * @desc 获取卖家发起case的详细内容
     * @param caseid  case的id
     * @param shopId  当前用户所拥有店铺的id
     * @author lvjianfei
     * @date 2015-04-08
     * @return mixed
     */
    public function getCaseDisputeDetail($caseid, $shopId)
    {
        $selects = 'b.shop_id,a.BuyerUserID,a.DisputeCreatedTime,a.DisputeExplanation,a.DisputeID,a.DisputeReason,
            a.DisputeState,a.DisputeStatus,a.i_ItemID,a.i_Quantity,a.i_SS_CurrentPrice,a.SellerUserID,
            a.TransactionID,a.DisputeModifiedTime,a.i_SS_CP_currencyID';
        $condition = "b.shop_id in ({$shopId}) and a.case_id = {$caseid}";
        $this->dbCommand->reset();
        $result = $this->dbCommand->select($selects)
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
        
        $result['SellerUserID'] = str_ireplace($_shopinfo['account'], $_shopinfo['nick_name'], $result['SellerUserID']);
        
        return $result;
    }
}