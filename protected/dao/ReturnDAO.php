<?php

/**
 * @desc return_request主表/return
 * @author liaojianwen
 * @date 2015-06-16
 */
class ReturnDAO extends BaseDAO
{
    
    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-06-16
     * @return ReturnDAO
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
        $this->tableName = 'return_request';
        $this->detail = 'return_request_detail';
        $this->primaryKey = 'return_request_id';
        $this->created = 'create_time';
        
        $this->shop = 'shop';
    }
    
    /**
     * @desc  获取return list
     * @param unknown $paramArr
     * @param unknown $status 状态
     * @param unknown $itemId item id
     * @param unknown $cust 客户
     * @param unknown $page
     * @param unknown $pageSize
     * @author liaojianwen
     * @date 2015-07-02
     * @return unknown
     */
    public function getReturnList($paramArr, $status, $itemId, $cust, $page, $pageSize)
    {
        $userConfig = UserModel::model()->getUserConfigs();
        $shopIds = implode(',', $userConfig['shops']);
        
        $limit = $pageSize;
        $offset = ($page - 1) * $pageSize;
        $selects = 'return_request_id,returnId_id,D_iD_itemId item_id,shop.nick_name nick_name,S_buyerLoginName,S_sellerLoginName,otherParty_userId,otherParty_role,
        responseDue_party_userId,responseDue_party_role,responseDue_respondByDate,creationDate,S_state,S_CI_reason,return_request.status status,S_sRD_respondByDate';
        $express = "shop.shop_id in ({$paramArr['shop_id']})";
        
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            if (empty($shopIds)) {
                $shopIds = 0;
            }
            $express .= " and shop.shop_id in ({$shopIds})";
        }
        
        if (! empty($status)) {
            $express .= " and S_state like :status";
            $params[':status'] = '%' . $status . '%';
        }
        if (! empty($itemId)) {
            $express .= " and D_iD_itemId like :itemId";
            $params[':itemId'] = '%' . $itemId . '%';
        }
        
        // @todo 状态不明确
        if (! empty($cust)) {
            $express .= " and S_buyerLoginName like :cust";
            $params[':cust'] = '%' . $cust . '%';
        }
        $this->dbCommand->reset()
            ->select($selects, 'SQL_CALC_FOUND_ROWS')
            ->from($this->tableName)
            ->join($this->shop, "{$this->shop}.shop_id = {$this->tableName}.shop_id")
            ->join($this->detail, "{$this->detail}.return_id = {$this->tableName}.return_request_id");
        if (empty($params)) {
            $this->dbCommand->where($express);
        } else {
            $this->dbCommand->where($express, $params);
        }
        
        $result['list'] = $this->dbCommand->order("{$this->tableName}.CreationDate DESC")
            ->limit($limit, $offset)
            ->queryALL();
        foreach ($result['list'] as $key => &$value) {
            
            // guest ItemID hide
            if (Yii::app()->session['userInfo']['user_id'] == 99999) {
                $value['item_id'] = preg_replace('/(\d{8})\d{4}/', '$1****', $value['item_id']);
            }
        }
        unset($value);
        $result['count'] = $this->dbCommand->reset()
            ->setText('select found_rows()')
            ->queryScalar();
        $result['page'] = array(
            'page' => $page,
            'pagesize' => $pageSize
        );
        return $result;
    }
    
    /**
     * @desc 上、下一页的id
     * @param $creationDate 
     * @param $return_id  return_request的id
     * @param  string $status
     * @param string $itemId
     * @param string $cust
     * @author liaojianwen
     * @return resultArr 上一页和下一页的ID
     * @date 2015-06-19
     */
    public function getPreNextID($creationDate, $return_id, $shopid,$status,$itemId,$cust)
    {
        // 上一页的id
        $pre = "creationDate>{$creationDate} and shop_id in ({$shopid['shop_id']})";
        $next = "creationDate<{$creationDate} and shop_id in ({$shopid['shop_id']})";
        $params = array();
       if(!empty($status)){
            $pre .=" and S_state like :status";
            $next .=" and S_state like :status";
            $params[':status'] = '%' . $status . '%';
        }
        if(!empty($itemId)){
            $pre .=" and D_iD_itemId like :itemId";
            $next .=" and D_iD_itemId like :itemId";
            $params[':itemId'] = '%' . $itemId . '%';
        }        
        
        //@todo 状态不明确
        if(!empty($cust)){
            $pre .= " and S_buyerLoginName like :cust";
            $next .= " and S_buyerLoginName like :cust";
            $params[':cust'] = '%' . $cust . '%';
        }
        
        $preID = $this->dbCommand->reset()
            ->select('return_request_id preID')
            ->from("{$this->tableName} r")
            ->join("{$this->detail} d","r.return_request_id = d.return_id")
            ->where($pre,$params)
            ->order('creationDate asc')
            ->limit(1)
            ->queryRow();
        // 下一页id
//        $next = "creationDate<{$creationDate} and shop_id in ({$shopid})";
        $nextID = $this->dbCommand->reset()
            ->select('return_request_id nextID')
            ->from("{$this->tableName} r")
            ->join("{$this->detail} d","r.return_request_id = d.return_id")
            ->where($next,$params)
            ->order('creationDate desc')
            ->limit(1)
            ->queryRow();
//            print_r($this->dbCommand->getText());die;
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
     * @desc 判断某个return_id(return_requst表主键)是否合法,不合法返回false，一般返回数组，里面包含对应token等
     * @param int $caseId Case表自增ID
     * @param int $sellerId 二级用户ID
     * @author liaojianwen
     * @date 2015-06-30
     * @return mixed
     */
    public function lawfulReturnID($returnid, $sellerId)
    {
        $conditions = 'r.return_request_id=:returnid and s.seller_id=:seller_id';
        $params = array(
            ':returnid' => $returnid,
            ':seller_id' => $sellerId
        );
        return $this->dbCommand->select('r.shop_id,r.returnId_id,s.token,s.site_id')
            ->from("{$this->tableName} r")
            ->join("{$this->shop} s", 'r.shop_id = s.shop_id')
            ->where($conditions, $params)
            ->limit(1)
            ->queryRow();
    }
    
    /**
     * @desc 获取状态
     * @param $sellerId
     * @author liaojianwen
     * @date 2015-07-02
     */
    public function getReturnState($sellerId)
    {
        $userConfig = UserModel::model()->getUserConfigs();
        $shopIds = implode(',', $userConfig['shops']);
        
        $conditions = "s.seller_id = {$sellerId}";
        
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            if (empty($shopIds)) {
                $shopIds = 0;
            }
            $conditions .= " and s.shop_id in ({$shopIds})";
        }
        
        $result = $this->dbCommand->reset()
            ->select('S_state', 'distinct')
            ->from("{$this->tableName} r")
            ->join("{$this->shop} s", "s.shop_id = r.shop_id")
            ->join("{$this->detail} d", "d.return_id = r.return_request_id")
            ->where($conditions)
            ->queryAll();
        return $result;
    }
}