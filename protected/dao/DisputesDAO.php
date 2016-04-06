<?php

/**
 * @desc Dispute主表/disputes
 * @author liaojianwen
 * @date 2015-08-18
 */
class DisputesDAO extends BaseDAO
{

    private $shop;
 // shop表名
    
    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-08-18
     * @return DisputesDAO
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
        $this->tableName = 'disputes';
        $this->primaryKey = 'disputes_id';
        $this->created = 'create_time';
        
        $this->shop = 'shop';
    }

    /**
     * @desc 获取卖方发起cancel disputes列表
     * @param $paramArr case类型
     * @param $page 页数
     * @pageSize $pageSize 页码
     * @param status case状态
     * @param $itemId itemID
     * @param $cust 用户id
     * @author liaojianwen
     * @date 2015-08-19
     * @return mixed
     */
    public function getCancelDisputeList($paramArr, $page, $pageSize, $status, $itemId, $cust)
    {
        $userConfig = UserModel::model()->getUserConfigs();
        $shopIds = implode(',', $userConfig['shops']);
        
        $limit = $pageSize;
        $offset = ($page - 1) * $limit;
        $selects = 'a.disputes_id, a.DisputeRecordType, a.BuyerUserID, a.SellerUserID, a.i_ItemID, a.DisputeID,
    				a.DisputeCreatedTime, a.DisputeState, a.DisputeStatus, a.DisputeExplanation, a.DisputeReason, a.shop_id';
        $params = array(
            ':param' => $paramArr['param']
        );
        $express = "a.DisputeReason = :param";
        
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            if (empty($shopIds)) {
                $shopIds = 0;
            }
            $express .= " and s.shop_id in ({$shopIds})";
        }
        
        if (! empty($status)) {
            if ($status == 'closed') {
                $express .= " and `DisputeStatus`  in ('Closed' , 'ClosedFVFCreditNoStrike' , 'ClosedFVFCreditStrike' , 'ClosedNoFVFCreditNoStrike' , 'ClosedNoFVFCreditStrike' , 'FVFCreditReversedAfterClosing' , 'StrikeAppealedAfterClosing' , 'StrikeAppealedAndFVFCreditReversed')";
            }
            if ($status == 'handle') {
                $express .= " and `DisputeStatus` not in ('Closed' , 'ClosedFVFCreditNoStrike' , 'ClosedFVFCreditStrike' , 'ClosedNoFVFCreditNoStrike' , 'ClosedNoFVFCreditStrike' , 'FVFCreditReversedAfterClosing' , 'StrikeAppealedAfterClosing' , 'StrikeAppealedAndFVFCreditReversed')";
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
        $condition = "{$express} and a.shop_id in ({$paramArr['shop_id']})";
        $this->dbCommand->reset()
            ->select($selects, 'SQL_CALC_FOUND_ROWS')
            ->from("{$this->tableName} a")
            ->join(ShopDAO::getInstance()->getTableName() . ' s', 's.shop_id=a.shop_id')
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
            $status = $value['DisputeStatus'];
            
            if ($status == 'Closed' || $status == 'ClosedFVFCreditNoStrike' || $status == 'ClosedFVFCreditStrike' ||
                 $status == 'ClosedNoFVFCreditNoStrike' || $status == 'ClosedNoFVFCreditStrike' || $status == 'FVFCreditReversedAfterClosing' ||
                 $status == 'StrikeAppealedAfterClosing' || $status == 'StrikeAppealedAndFVFCreditReversed' || $value['DisputeState'] == 'Closed') {
                $value['status'] = 'closed';
            } else {
                $value['status'] = 'processing';
            }
            // 替换别名
            $value['SellerUserID'] = str_ireplace($shopInfo[$value['shop_id']]['account'], $shopInfo[$value['shop_id']]['nick_name'], 
                $value['SellerUserID']);
            
            // guest ItemID hide
            if (Yii::app()->session['userInfo']['user_id'] == 99999) {
                $value['i_ItemID'] = preg_replace('/(\d{8})\d{4}/', '$1****', $value['i_ItemID']);
            }
        }
        unset($value);
        $result['page'] = array(
            'page' => $page,
            'pagesize' => $pageSize
        );
        return $result;
    }

    /**
     * @desc 获取卖家发起case的详细内容
     * @param caseid  case的id
     * @param shopId  当前用户所拥有店铺的id
     * @author lvjianfei
     * @date 2015-04-08
     * @return mixed
     */
    public function getCancelDisputeDetail($disputeid, $shopId)
    {
        $selects = 'd.BuyerUserID,d.DisputeCreatedTime,d.DisputeExplanation,d.DisputeID,d.DisputeReason,
            d.DisputeState,d.DisputeStatus,d.i_ItemID,d.i_Quantity,d.i_ss_CurrentPrice,d.SellerUserID,
            d.TransactionID,d.DisputeModifiedTime,d.i_ss_CurrentPrice_currencyID,s.account,s.nick_name';
        $condition = "d.shop_id in ({$shopId}) and d.disputes_id = {$disputeid}";
        $this->dbCommand->reset();
        $result = $this->dbCommand->select($selects)
            ->from("{$this->tableName} d")
            ->join(ShopDAO::getInstance()->getTableName() . ' s', 'd.shop_id=s.shop_id')
            ->where($condition)
            ->limit(1)
            ->queryRow();
        
        // 别名替换
        $result['SellerUserID'] = str_ireplace($result['account'], $result['nick_name'], $result['SellerUserID']);
        unset($result['account']);
        unset($result['nick_name']);
        
        return $result;
    }

    /**
     * @desc 上、下一页的id
     * @param $disputeid  dispute的id
     * @author liaojianwen
     * @return resultArr 上一页和下一页的ID
     * @date 2015-08-20
     */
    public function getPreNexID($DisputeCreatedTime, $disputeid, $paramArr, $shopId)
    {
        // 下一页的id
        $param = array(
            ':param' => $paramArr['param']
        );
        $next = "shop_id in ({$shopId}) and DisputeCreatedTime<{$DisputeCreatedTime}";
        $next .= ' and disputes_id <> ' . $disputeid;
        $next .= ' and DisputeReason = :param';
        $nextID = $this->dbCommand->reset()
            ->select('disputes_id nextID')
            ->from("{$this->tableName}")
            ->where($next, $param)
            ->order('DisputeCreatedTime desc')
            ->limit(1)
            ->queryRow();
        // 上一页id
        $pre = "shop_id in ({$shopId}) and DisputeCreatedTime>{$DisputeCreatedTime}";
        $pre .= ' and disputes_id <> ' . $disputeid;
        $pre .= ' and DisputeReason = :param';
        $preID = $this->dbCommand->reset()
            ->select('disputes_id preID')
            ->from("{$this->tableName}")
            ->order('DisputeCreatedTime asc')
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
}