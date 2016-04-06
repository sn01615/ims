<?php

/**
 * @desc Case主表/case
 * @author YangLong
 * @date 2015-03-31
 */
class CaseDAO extends BaseDAO
{

    private $shop;
    // shop表名
    
    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-03-31
     * @return CaseDAO
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
        $this->tableName = 'case';
        $this->detail = 'case_detail';
        $this->primaryKey = 'case_id';
        $this->created = 'create_time';
        
        $this->shop = 'shop';
    }

    /**
     * @desc 获取买家发起case列表
     * @param String  $Type      case类型
     * @param integer $page      页码
     * @param integer $pageSize  分页大小
     * @param string $status  查询条件状态
     * @param integer $itemId 查询条件itemNo
     * @param  stirng $cust 查询条件客户
     * @param string $query 判断是查询还是列表展示/query 为查询
     * @author lvjianfei,liaojianwen,YangLong
     * @date 2015-04-02
     * @return array 列表信息
     */
    public function getCaseList($paramArr, $page, $pageSize, $status, $itemId, $cust, $query)
    {
        $userConfig = UserModel::model()->getUserConfigs();
        $shopIds = implode(',', $userConfig['shops']);
        
        $limit = $pageSize;
        $offset = ($page - 1) * $limit;
        $selection = 'c.shop_id,c.case_id,caseId_id,caseId_type,user_userId,user_role,i_itemId,i_transactionId,creationDate,
            s_cancelTransactionStatus,s_EBPINRStatus,s_EBPSNADStatus,s_INRStatus , s_PaypalINRStatus,
            s_PaypalSNADStatus,s_returnStatus,s_SNADStatus,s_UPIStatus,otherParty_userId,otherParty_role';
        $params = array(
            ':param' => $paramArr['param']
        );
        $express = "caseId_type = :param";
        
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            if (empty($shopIds)) {
                $shopIds = 0;
            }
            $express .= " and s.shop_id in ({$shopIds})";
        }
        
        if (! empty($status)) {
            if ($status == 'closed') {
                switch ($paramArr['param']) {
                    case 'EBP_INR':
                        $express .= " and `s_EBPINRStatus`  in ('CASE_CLOSED_CS_RESPONDED','CLOSED','CS_CLOSED','EXPIRED','PAID','YOU_CONTACTED_CS_ABOUT_CLOSED_CASE')";
                        break;
                    case 'EBP_SNAD':
                        $express .= " and `s_EBPSNADStatus`  in ('CASE_CLOSED_CS_RESPONDED','CLOSED','CS_CLOSED','EXPIRED','PAID','YOU_CONTACTED_CS_ABOUT_CLOSED_CASE')";
                        break;
                }
            }
            if ($status == 'handle') {
                switch ($paramArr['param']) {
                    case 'EBP_INR':
                        $express .= " and `s_EBPINRStatus` not in ('CASE_CLOSED_CS_RESPONDED','CLOSED','CS_CLOSED','EXPIRED','PAID','YOU_CONTACTED_CS_ABOUT_CLOSED_CASE')";
                        break;
                    case 'EBP_SNAD':
                        $express .= " and `s_EBPSNADStatus` not in ('CASE_CLOSED_CS_RESPONDED','CLOSED','CS_CLOSED','EXPIRED','PAID','YOU_CONTACTED_CS_ABOUT_CLOSED_CASE')";
                        break;
                }
            }
        }
        
        if (! empty($itemId)) {
            $express .= ' and i_itemId like :itemid';
            $params[':itemid'] = '%' . $itemId . '%';
        }
        if (! empty($cust)) {
            // $express .= " and CASE WHEN user_role='BUYER' THEN user_userId like :cust WHEN user_role='SELLER' THEN otherParty_userId like :cust END";
            $express .= " and (user_userId like :cust or otherParty_userId like :cust) ";
            // TO\DO 选择更高效那个
            $params[':cust'] = '%' . $cust . '%';
        }
        
        $condition = "{$express} and s.shop_id in ({$paramArr['shop_id']}) and openReason !='Unknown'";
        $this->dbCommand->reset()
            ->select($selection, 'SQL_CALC_FOUND_ROWS')
            ->from($this->tableName . ' c')
            ->join($this->detail . ' cd', "c.case_id = cd.case_id")
            ->join(ShopDAO::getInstance()->getTableName() . ' s', 'c.shop_id=s.shop_id')
            ->where($condition, $params)
            ->order("creationDate desc");
        $result['list'] = $this->dbCommand->limit($limit, $offset)->queryAll();
        $result['count'] = $this->dbCommand->reset()
            ->setText('select found_rows()')
            ->queryScalar();
        
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
                case 'EBP_INR':
                    $status = $value['s_EBPINRStatus'];
                    break;
                case 'EBP_SNAD':
                    $status = $value['s_EBPSNADStatus'];
                    break;
            }
            
            if ($status == 'CASE_CLOSED_CS_RESPONDED' || $status == 'CLOSED' || $status == 'CS_CLOSED' || $status == 'EXPIRED' ||
                 $status == 'PAID' || $status == 'YOU_CONTACTED_CS_ABOUT_CLOSED_CASE') {
                $value['status'] = 'closed';
            } else {
                $value['status'] = 'processing';
            }
            
            // 替换别名
            $value['user_userId'] = str_ireplace($shopInfo[$value['shop_id']]['account'], $shopInfo[$value['shop_id']]['nick_name'], 
                $value['user_userId']);
            $value['otherParty_userId'] = str_ireplace($shopInfo[$value['shop_id']]['account'], $shopInfo[$value['shop_id']]['nick_name'], 
                $value['otherParty_userId']);
            
            // guest ItemID hide
            if (Yii::app()->session['userInfo']['user_id'] == 99999) {
                $value['i_itemId'] = preg_replace('/(\d{8})\d{4}/', '$1****', $value['i_itemId']);
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
     * @desc 上、下一页的id
     * @param caseid  case的id
     * @author lvjianfei
     * @return resultArr 上一页和下一页的ID
     * @date 2015-04-08
     */
    public function getPreNexID($creationDate, $caseid, $paramArr, $shopid)
    {
        // 上一页的id
        $params = array(
            ':param' => $paramArr['param']
        );
        $pre = "caseId_type = :param and creationDate>{$creationDate} and shop_id in ({$shopid}) and openReason !='Unknown'";
        $nextID = $this->dbCommand->reset()
            ->select('case.case_id preID')
            ->from($this->tableName)
            ->join($this->detail, "`{$this->tableName}`.case_id = {$this->detail}.case_id")
            ->where($pre, $params)
            ->order('creationDate asc')
            ->limit(1)
            ->queryRow();
        // 下一页id
        $next = "caseId_type = :param and creationDate<{$creationDate} and shop_id in ({$shopid}) and openReason !='Unknown'";
        $preID = $this->dbCommand->reset()
            ->select('case.case_id nextID')
            ->from($this->tableName)
            ->join($this->detail, "`{$this->tableName}`.case_id = {$this->detail}.case_id")
            ->where($next, $params)
            ->order('creationDate desc')
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
     * @desc 判断某个case_id(case表主键)是否合法,不合法返回false，一般返回数组，里面包含对应token等
     * @param int $caseId Case表自增ID
     * @param int $sellerId 二级用户ID
     * @author YangLong
     * @date 2015-04-16
     * @return mixed
     */
    public function lawfulCaseID($caseId, $sellerId)
    {
        $conditions = 'c.case_id=:case_id and s.seller_id=:seller_id';
        $params = array(
            ':case_id' => $caseId,
            ':seller_id' => $sellerId
        );
        return $this->dbCommand->select('c.shop_id,s.token,s.site_id')
            ->from("{$this->tableName} c")
            ->join("{$this->shop} s", 'c.shop_id = s.shop_id')
            ->where($conditions, $params)
            ->limit(1)
            ->queryRow();
    }

    /**
     * @desc 通过caseid获取caseID
     * @param string  $caseid case 主键
     * @param string $shopidArr
     * @author liaojianwen
     * @date 2015-07-07
     */
    public function getCaseId($caseid, $shopidArr)
    {
        $conditions = "shop_id in ({$shopidArr}) and case_id={$caseid}";
        return $this->dbCommand->reset()
            ->select('caseId_id')
            ->from($this->tableName)
            ->where($conditions)
            ->queryScalar();
    }
}