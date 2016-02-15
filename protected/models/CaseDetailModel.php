<?php

/**
 * @desc case详细页处理类
 * @author lvjianfei
 * @date 2015-04-02
 */
class CaseDetailModel extends BaseModel
{
    
    /**
     * @desc 覆盖父方法返回CaseModel对象
     * @param string $className 需要实例化的类名
     * @author lvjianfei
     * @date 2015-04-02
     * @return CaseDetailModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
     /**
     * @desc 获取case详细信息
     * @param integer $caseid case的id
     * @param String $type case的类型
     * @author lvjianfei
     * @date 2015-04-02
     * @return array 详情页数据
     */
    public function getCaseDetail($caseid, $type)
    {
        switch ($type) {
            case EnumOther::CASE_INR:
                $param = 'EBP_INR';
                break;
            case EnumOther::CASE_SNAD:
                $param = 'EBP_SNAD';
                break;
        }
        if (empty($caseid)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '信息不存在');
        }
        $paramArr['param'] = $param;
        // 获取客户所有店铺
        $shopParam = array();
        $shopParam['seller_id'] = Yii::app()->session['userInfo']['seller_id'];
        $shopParam['is_delete'] = boolConvert::toInt01(false);
        $shopParam['status'] = 1;
        $shopArr = ShopDAO::getInstance()->findAllByAttributes($shopParam, array(
            'shop_id'
        ));
        if (empty($shopArr)) {
            $result = $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户还未注册店铺');
            return $result;
        }
        foreach ($shopArr as $value) {
            $shopidArr[] = $value['shop_id'];
        }
        $shopidArr = implode(',', $shopidArr);
        $result['list'] = CaseDetailDAO::getInstance()->getCaseDetail($caseid, $shopidArr);
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '查询数据库失败');
        }
        
        if (isset(Yii::app()->session['switchInfo']['accountId']) && ! empty(Yii::app()->session['switchInfo']['accountId'])) {
            $shopidArr = Yii::app()->session['switchInfo']['accountId'];
        }
        
        $res = CaseDAO::getInstance()->getPreNexID($result['list']['creationDate'], $caseid, $paramArr, $shopidArr);
        $result['prenextID'] = $res;
        
        // guest ItemID hide
        if (Yii::app()->session['userInfo']['user_id'] == 99999) {
            $result['user_id'] = 99999;
        }
        
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
    }
    
    /**
     * @desc 获取case处理过程的历史对话信息
     * @param integer $caseid case的id
     * @author lvjianfei
     * @date 2015-4-2
     * @return array 对话历史消息
     */
    public function getCaseResponseHistory($caseid)
    {
        if (empty($caseid)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '信息不存在');
        }
        //获取客户所有店铺
        $shopParam = array();
        $shopParam['seller_id'] = Yii::app()->session['userInfo']['seller_id'];
        $shopParam['is_delete'] = boolConvert::toInt01(false);
        $shopParam['status'] 	= 1;
        $shopArr = ShopDAO::getInstance()->findAllByAttributes($shopParam, array('shop_id'));
    	if (empty($shopArr)) {
            $res= $this->handleApiFormat(EnumOther::ACK_FAILURE,'','用户还未注册店铺');
            return $res;
        }
        foreach ($shopArr as $value) {
            $shopidArr[] = $value['shop_id'];
        }
        $shopidArr = implode(',', $shopidArr);
        $result['list'] = CaseHistoryDAO::getInstance()->getCaseHistory($caseid,$shopidArr);
        foreach($result['list'] as &$value){
              $value['note_md5'] = md5(trim($value['note']));
              $value['activityDetial_description_md5'] = md5(trim($value['activityDetial_description']));
        }
        if ($result['list'] == false) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '信息不存在');
        }
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
    }
    
    /**
     * @desc 添加case备注
     * @param integer $caseid case的id
     * @param string $text 备注内容
     * @param  int SellerId
     * @author lvjianfei,liaojianwen
     * @date 2015-04-03
     * @modify 2015-05-25
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function addItemNote($text,$caseId,$sellerId)
    {
        if ($caseId <= 0) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '`caseid` can not empty.');
        }
        $columns = array(
            'c.i_itemId',
            'c.user_userId',
            'c.user_role',
            'c.otherParty_userId',
            'c.otherParty_role'
        );    
        $conditions = 'c.case_id=:case_id and s.seller_id=:seller_id';
        $params = array(
            ':case_id' => $caseId,
            ':seller_id' => $sellerId
        );
        $joinArray = array(
            array(
                ShopDAO::getInstance()->igetproperty('tableName') . ' s',
                's.shop_id=c.shop_id and s.is_delete=0'
            )
        );
        $tableAlias = 'c';
        $tokeninfo = CaseDAO::getInstance()->iselect($columns, $conditions, $params, false, $joinArray, $tableAlias);
        if ($tokeninfo !== false) {
            $cust = '';
            if($tokeninfo['user_role'] ==='BUYER'){
                $cust = $tokeninfo['user_userId'];
            } elseif ($tokeninfo['otherParty_role'] ==='BUYER'){
                $cust = $tokeninfo['otherParty_userId'];
            }
            
            $itemnote = ItemNoteDAO::getInstance();
            $username = isset(Yii::app()->session['userInfo']['username']) ? Yii::app()->session['userInfo']['username'] : 0;
            if ($username !== 0) {
                $param['author_name'] = $username;
            } else {
                return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户未登陆');
            }
            $param['text'] = $text;
            $param['create_time'] = time();
            $param['item_id'] = $tokeninfo['i_itemId'];
            $param['cust'] = $cust;
            $result = $itemnote->insert($param);
            if ($result === false) {
                return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
            } else {
                return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
            }
        }
    }
    
    /**
     * @desc 获取case备注列表
     * @param integer $caseid case的id
     * @param string $type 判断是case还是msg中备注
     * @author lvjianfei,liaojianwen
     * @date 2015-04-03
     * @modify 2015-04-21
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function getItemNoteList($itemId, $type, $dealId)
    {
        // 获取客户所有店铺
        $shopParam = array();
        $shopParam['seller_id'] = Yii::app()->session['userInfo']['seller_id'];
        $shopParam['is_delete'] = boolConvert::toInt01(false);
        $shopParam['status'] = 1;
        $shopArr = ShopDAO::getInstance()->findAllByAttributes($shopParam, array(
            'shop_id'
        ));
        if (empty($shopArr)) {
            $result = $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户还未注册店铺');
            return $result;
        }
        foreach ($shopArr as $value) {
            $shopidArr[] = $value['shop_id'];
        }
        $shopidArr = implode(',', $shopidArr);
        $result['list'] = ItemNoteDAO::getInstance()->getItemNote($itemId, $shopidArr, $type, $dealId);
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
    }
    
    /**
     * @desc 买家发送消息
     * @param int $caseId Case表主键
     * @param string $caseType Case类型
     * @param string $role 作者角色
     * @param string $responseText 消息内容
     * @param int $sellerId 客户ID
     * @param string $caseId_id Case ID
     * @author liaojianwen,YangLong
     * @date 2015-04-16
     * @return mixed
     */
    public function addResponse($caseId, $caseType, $role, $responseText, $sellerId, $caseId_id)
    {
        if (empty($caseId) || empty($caseType) || empty($role) || empty($responseText) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
        }
        $token = CaseDAO::getInstance()->lawfulCaseID($caseId, $sellerId);
        if ($token === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'CaseID Error.');
        } else {
            $columns = array(
                'number'
            );
            $conditions = 'case_id=' . $caseId;
            $params = array();
            $number = CaseResponseHistoryDAO::getInstance()->iselect($columns, $conditions, $params, 'queryScalar', array(), '', 'number desc');
            empty($number) ? $number = 1 : $number ++;
            
            CaseHistoryDAO::getInstance()->begintransaction();
            try {
                $param['case_id'] = $caseId;
                $param['number'] = $number;
                $param['creationDate'] = time();
                $param['note'] = $responseText;
                $param['activityDetial_description'] = 'Seller offered another solution.';
                $param['author_role'] = $role;
                $result = CaseHistoryDAO::getInstance()->insert($param);
                
                $siteid = $token['site_id'];
                $disputeActivity = 'SellerComment'; // TODO 待确认
                $columns = array(
                    'upload_type' => __FUNCTION__,
                    'upload_data' => serialize(compact('caseId_id', 'caseType', 'responseText', 'siteid', 'disputeActivity')),
                    'token' => $token['token'],
                    'create_time' => time()
                );
                $result2 = CaseUploadQueueDAO::getInstance()->iinsert($columns);
                // 以下部分是插入我的操作日志表的代码
                $username = isset(Yii::app()->session['userInfo']['username']) ? Yii::app()->session['userInfo']['username'] : 0;
                if ($username !== 0) {
                    $paramArr['handle_user'] = $username;
                } else {
                    return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户未登陆');
                }
                $paramArr['case_id'] = $caseId;
                $paramArr['caseType'] = $caseType;
                $paramArr['responseText'] = $responseText;
                $paramArr['create_time'] = time();
                $paramArr['handle_type'] = __FUNCTION__;
                $result3 = CaseHandleLogDAO::getInstance()->insert($paramArr);
                if ($result === false || $result2 === false || $result3 === false) {
                    CaseHistoryDAO::getInstance()->rollback();
                    return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
                } else {
                    CaseHistoryDAO::getInstance()->commit();
                    return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
                }
            } catch (Exception $e) {
                CaseHistoryDAO::getInstance()->rollback();
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
            }
        }
    }
    
    /**
     * @desc 处理case回复物流单号与承运商
     * @param int $caseId case表主键
     * @param string $caseType case类型
     * @param string $role case操作者角色
     * @param string $carrier 承运商
     * @param string $trackingNum 物流单号
     * @param string $responseText 回复内容
     * @param int sellerId 客户ID
     * @param int $caseId_id caseID
     * @return array
     * @author liaojianwen
     * @date 2015-04-17
     */
    public function addTrackingInfo($caseId, $caseType, $role, $carrier, $trackingNum, $responseText, $sellerId, $caseId_id)
    {
        if (empty($caseId) || empty($caseType) || empty($role) || empty($carrier) || empty($trackingNum)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
        }
        $token = CaseDAO::getInstance()->lawfulCaseID($caseId, $sellerId);
        if ($token === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'CaseID Error.');
        } else {
            CaseHistoryDAO::getInstance()->begintransaction();
            try {
                $param['case_id'] = $caseId;
                $param['creationDate'] = time();
                $param['note'] = $responseText;
                $param['activityDetial_description'] = 'Seller provided tracking information for shipment.';
                $param['author_role'] = $role;
                $result = CaseHistoryDAO::getInstance()->insert($param);
                $columns = array(
                    'upload_type' => __FUNCTION__,
                    'upload_data' => serialize(compact('caseId_id', 'caseType', 'role', 'carrier', 'trackingNum', 'responseText')),
                    'token' => $token['token'],
                    'create_time' => time()
                );
                $result2 = CaseUploadQueueDAO::getInstance()->iinsert($columns);
                // 以下部分是插入我的操作日志表的代码
                $username = isset(Yii::app()->session['userInfo']['username']) ? Yii::app()->session['userInfo']['username'] : 0;
                if ($username !== 0) {
                    $paramArr['handle_user'] = $username;
                } else {
                    return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户未登陆');
                }
                $paramArr['case_id'] = $caseId;
                $paramArr['caseType'] = $caseType;
                $paramArr['responseText'] = $responseText;
                $paramArr['create_time'] = time();
                $paramArr['handle_type'] = __FUNCTION__;
                $paramArr['trackingNum'] = $trackingNum;
                $paramArr['carrier'] = $carrier;
                $result3 = CaseHandleLogDAO::getInstance()->insert($paramArr);
                if ($result === false || $result2 === false || $result3 === false) {
                    CaseHistoryDAO::getInstance()->rollback();
                    return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
                } else {
                    CaseHistoryDAO::getInstance()->commit();
                    return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
                }
            } catch (Exception $e) {
                CaseHistoryDAO::getInstance()->rollback();
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
            }
        }
    }
    
    /**
     * @desc 处理case 提供承运商，发货日期
     * @param int $caseId case表主键
     * @param string $caseType case类型
     * @param string $role case操作者角色
     * @param string $carrier 承运商
     * @param string $shipdate 发货日期
     * @param string $responseText 回复内容
     * @param int $sellerId 客户ID
     * @param int $caseId_id caseID
     * @return array
     * @author liaojianwen
     * @date 2015-04-17
     */
    public function addShippingInfo($caseId, $caseType, $role, $carrier, $shipdate, $responseText, $sellerId, $caseId_id)
    {
        if (empty($caseId) || empty($caseType) || empty($role) || empty($carrier) || empty($shipdate)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
        }
        $token = CaseDAO::getInstance()->lawfulCaseID($caseId, $sellerId);
        if ($token === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'CaseID Error.');
        } else {
            CaseHistoryDAO::getInstance()->begintransaction();
            try {
                $param['case_id'] = $caseId;
                $param['creationDate'] = time();
                $param['note'] = $responseText;
                $param['activityDetial_description'] = 'Seller provided shipping information.';
                $param['author_role'] = $role;
                $result = CaseHistoryDAO::getInstance()->insert($param);
                $columns = array(
                    'upload_type' => __FUNCTION__,
                    'upload_data' => serialize(compact('caseId_id', 'caseType', 'role', 'carrier', 'shipdate', 'responseText')),
                    'token' => $token['token'],
                    'create_time' => time()
                );
                $result2 = CaseUploadQueueDAO::getInstance()->iinsert($columns);
                //以下部分是插入我的操作日志表的代码
        		$username = isset(Yii::app()->session['userInfo']['username']) ? Yii::app()->session['userInfo']['username'] : 0;
        		if ($username !== 0) {
        		    $paramArr['handle_user'] = $username;
        		} else {
         		    return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户未登陆');
        		}
        		$paramArr['case_id'] = $caseId;
        		$paramArr['caseType'] = $caseType;
        		$paramArr['responseText'] = $responseText;
        		$paramArr['create_time'] = time();
        		$paramArr['handle_type'] = __FUNCTION__;
        		$paramArr['shipdate'] = $shipdate;
        		$paramArr['carrier'] = $carrier;
        		$result3 = CaseHandleLogDAO::getInstance()->insert($paramArr);
                if ($result === false || $result2 === false || $result3 === false) {
                    CaseHistoryDAO::getInstance()->rollback();
                    return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
                } else {
                    CaseHistoryDAO::getInstance()->commit();
                    return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
                }
            } catch (Exception $e) {
                CaseHistoryDAO::getInstance()->rollback();
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
            }
        }
    }
    
    /**
     * @desc case全额退款
     * @param int $caseId  case表主键
     * @param string $caseType case类型
     * @param string $role  case操作者角色
     * @param string $responseText 回复内容
     * @param string $sellerId 客户ID
     * @param int $caseId_id caseID
     * @return array
     * @author liaojianwen
     * @date 2015-04-17
     */
    public function fullRefund($caseId, $caseType, $role, $responseText, $sellerId, $caseId_id)
    {
        if (empty($caseId) || empty($caseType) || empty($role)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
        }
        $token = CaseDAO::getInstance()->lawfulCaseID($caseId, $sellerId);
        if ($token === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'CaseID Error.');
        } else {
            CaseHistoryDAO::getInstance()->begintransaction();
            try {
                $param['case_id'] = $caseId;
                $param['creationDate'] = time();
                $param['note'] = $responseText;
                $param['activityDetial_description'] = 'Seller issued full refund to buyer.';
                $param['author_role'] = $role;
                $result = CaseHistoryDAO::getInstance()->insert($param);
                $columns = array(
                    'upload_type' => __FUNCTION__,
                    'upload_data' => serialize(compact('caseId_id', 'caseType', 'role', 'responseText')),
                    'token' => $token['token'],
                    'create_time' => time()
                );
                $result2 = CaseUploadQueueDAO::getInstance()->iinsert($columns);
                // 以下部分是插入我的操作日志表的代码
                $username = isset(Yii::app()->session['userInfo']['username']) ? Yii::app()->session['userInfo']['username'] : 0;
                if ($username !== 0) {
                    $paramArr['handle_user'] = $username;
                } else {
                    return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户未登陆');
                }
                $paramArr['case_id'] = $caseId;
                $paramArr['caseType'] = $caseType;
                $paramArr['responseText'] = $responseText;
                $paramArr['create_time'] = time();
                $paramArr['handle_type'] = __FUNCTION__;
                $result3 = CaseHandleLogDAO::getInstance()->insert($paramArr);
                if ($result === false || $result2 === false || $result3 === false) {
                    CaseHistoryDAO::getInstance()->rollback();
                    return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
                } else {
                    CaseHistoryDAO::getInstance()->commit();
                    return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
                }
            } catch (Exception $e) {
                CaseHistoryDAO::getInstance()->rollback();
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
            }
        }
    }
    
    /**
     * @desc case 部分退款
     * @param int $caseId case表主键
     * @param string $caseType case类型
     * @param string $role  case操作者角色
     * @param string $responseText 回复内容
     * @param int $amount 退款金额
     * @param int $sellerId  客户ID
     * @param int $caseId_id caseID
     * @return array
     * @author liaojianwen
     * @date 2015-04-17
     */
    public function partialRefund($caseId, $caseType, $role, $responseText, $amount, $sellerId, $caseId_id)
    {
        if (empty($caseId) || empty($caseType) || empty($role) || empty($amount)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
        }
        $token = CaseDAO::getInstance()->lawfulCaseID($caseId, $sellerId);
        if ($token === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'CaseID Error.');
        } else {
            CaseHistoryDAO::getInstance()->begintransaction();
            try {
                $param['case_id'] = $caseId;
                $param['creationDate'] = time();
                $param['note'] = $responseText;
                $param['activityDetial_description'] = 'Seller issued partial refund to buyer.';
                $param['author_role'] = $role;
                $result = CaseHistoryDAO::getInstance()->insert($param);
                $columns = array(
                    'upload_type' => __FUNCTION__,
                    'upload_data' => serialize(compact('caseId_id', 'caseType', 'role', 'amount', 'responseText')),
                    'token' => $token['token'],
                    'create_time' => time()
                );
                $result2 = CaseUploadQueueDAO::getInstance()->iinsert($columns);
                //以下部分是插入我的操作日志表的代码
        		$username = isset(Yii::app()->session['userInfo']['username']) ? Yii::app()->session['userInfo']['username'] : 0;
        		if ($username !== 0) {
        		    $paramArr['handle_user'] = $username;
        		} else {
         		    return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户未登陆');
        		}
        		$paramArr['case_id'] = $caseId;
        		$paramArr['caseType'] = $caseType;
        		$paramArr['responseText'] = $responseText;
        		$paramArr['create_time'] = time();
        		$paramArr['handle_type'] = __FUNCTION__;
        		$paramArr['amount'] = $amount;
        		$result3 = CaseHandleLogDAO::getInstance()->insert($paramArr);
                if ($result === false || $result2 === false || $result3 === false) {
                    CaseHistoryDAO::getInstance()->rollback();
                    return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
                } else {
                    CaseHistoryDAO::getInstance()->commit();
                    return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
                }
            } catch (Exception $e) {
                CaseHistoryDAO::getInstance()->rollback();
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
            }
        }
    }
    
    /**
     * @desc 退款兼退货
     * @param int $caseId  case表主键
     * @param string $caseType case类型
     * @param string  $role   case操作者角色
     * @param string $responseText 回复内容
     * @param int $sellerId 客户ID
     * @param int $caseId_id caseID
     * @return array
     * @author liaojianwen
     * @date 2015-04-17
     */
    public function returnItemRefund($caseId, $caseType, $role, $responseText, $sellerId, $caseId_id, $address)
    {
        if (empty($caseId) || empty($caseType) || empty($role) || empty($responseText)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
        }
        $token = CaseDAO::getInstance()->lawfulCaseID($caseId, $sellerId);
        if ($token === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'CaseID Error.');
        } else {
            CaseHistoryDAO::getInstance()->begintransaction();
            try {
                $country = $address['country'];
                $state = $address['state'];
                $city = $address['city'];
                $street = $address['street'];
                $street2 = $address['street2'];
                $contractName = $address['contractName'];
                $postcode = $address['postcode'];
                $merchantAuth = $address['merchantAuth'];
                $param['case_id'] = $caseId;
                $param['creationDate'] = time();
                $param['note'] = $responseText;
                $param['activityDetial_description'] = 'Seller provide return address and issued a refund.';
                $param['author_role'] = $role;
                $result = CaseHistoryDAO::getInstance()->insert($param);
                $columns = array(
                    'upload_type' => __FUNCTION__,
                    'upload_data' => serialize(compact('caseId_id', 'caseType', 'role', 'responseText', 'country', 'state', 'city', 'street', 'street2', 'contractName', 'postcode', 'merchatAuth')),
                    'token' => $token['token'],
                    'create_time' => time()
                );
                $result2 = CaseUploadQueueDAO::getInstance()->iinsert($columns);
                // 以下部分是插入我的操作日志表的代码
                $username = isset(Yii::app()->session['userInfo']['username']) ? Yii::app()->session['userInfo']['username'] : 0;
                if ($username !== 0) {
                    $paramArr['handle_user'] = $username;
                } else {
                    return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户未登陆');
                }
                $paramArr['case_id'] = $caseId;
                $paramArr['caseType'] = $caseType;
                $paramArr['responseText'] = $responseText;
                $paramArr['create_time'] = time();
                $paramArr['handle_type'] = __FUNCTION__;
                $result3 = CaseHandleLogDAO::getInstance()->insert($paramArr);
                if ($result === false || $result2 === false || $result3 === false) {
                    CaseHistoryDAO::getInstance()->rollback();
                    return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
                } else {
                    CaseHistoryDAO::getInstance()->commit();
                    return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
                }
            } catch (Exception $e) {
                CaseHistoryDAO::getInstance()->rollback();
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
            }
        }
    }
    
    /**
     * @desc 升级Case(eBay介入)
     * @param int $caseId case表主键
     * @param string $caseType case类型
     * @param string $role case操作者角色
     * @param string $responseText  回复内容
     * @param string $reason 选择ebay介入原因
     * @param int $sellerId  客户ID
     * @param int $caseId_id caseID
     * @return array
     * @author liaojianwen
     * @date 2015-04-17
     */
    public function ebayHelp($caseId, $caseType, $role, $responseText, $reason, $sellerId, $caseId_id)
    {
        if (empty($caseId) || empty($caseType) || empty($role) || empty($reason)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
        }
        $token = CaseDAO::getInstance()->lawfulCaseID($caseId, $sellerId);
        if ($token === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'CaseID Error.');
        } else {
            CaseHistoryDAO::getInstance()->begintransaction();
            try {
                $param['case_id'] = $caseId;
                $param['creationDate'] = time();
                $param['note'] = $responseText;
                $param['activityDetial_description'] = 'Seller ask ebay help';
                $param['author_role'] = $role;
                $result = CaseHistoryDAO::getInstance()->insert($param);
                $columns = array(
                    'upload_type' => __FUNCTION__,
                    'upload_data' => serialize(compact('caseId_id', 'caseType', 'role', 'reason', 'responseText')),
                    'token' => $token['token'],
                    'create_time' => time()
                );
                $result2 = CaseUploadQueueDAO::getInstance()->iinsert($columns);
                //以下部分是插入我的操作日志表的代码
        		$username = isset(Yii::app()->session['userInfo']['username']) ? Yii::app()->session['userInfo']['username'] : 0;
        		if ($username !== 0) {
        		    $paramArr['handle_user'] = $username;
        		} else {
         		    return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户未登陆');
        		}
        		$paramArr['case_id'] = $caseId;
        		$paramArr['caseType'] = $caseType;
        		$paramArr['responseText'] = $responseText;
        		$paramArr['create_time'] = time();
        		$paramArr['handle_type'] = __FUNCTION__;
        		$paramArr['reason'] = $reason;
        		$result3 = CaseHandleLogDAO::getInstance()->insert($paramArr);
                if ($result === false || $result2 === false || $result3 === false) {
                    CaseHistoryDAO::getInstance()->rollback();
                    return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
                } else {
                    CaseHistoryDAO::getInstance()->commit();
                    return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
                }
            } catch (Exception $e) {
                CaseHistoryDAO::getInstance()->rollback();
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
            }
        }
    }
    
    /**
     * @desc 获取我的处理记录
     * @param int caseid case的id
     * @return array
     * @author lvjianfei
     * @date 2015-04-20
     */
    public function getCaseHandleLog($caseid){
    	if(empty($caseid)){
    		return $this->handleApiForMat(EnumOther::ACK_FAILURE,'','获取caseid失败');
    	}
    	$result['list'] = CaseHandleLogDAO::getInstance()->getCaseHandleLog($caseid);
    	return $this->handleApiFormat(EnumOther::ACK_SUCCESS,$result,'');
    }
    
    /**
     * @desc 生成提供退货地址的case的队列
     * @param $caseId case 主键
     * @param $caseType case 类型
     * @param $addr 地址
     * @param $rma RMA
     * @param $caseId_id  caseID
     * @param $sellerId
     * @author liaojianwen
     * @date 2015-07-07
     */
    public function provideReturnInfo($caseId,$caseType,$addr,$rma,$caseId_id,$sellerId)
    {
         if (empty($caseId) || empty($caseType) || empty($addr['name']) || empty($addr['street1']) || empty($addr['street2']) || empty($addr['city']) || empty($addr['state'])
             || empty($addr['country']) || empty($addr['postalCode'])) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
        }
        $token = CaseDAO::getInstance()->lawfulCaseID($caseId, $sellerId);
        if ($token === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'CaseID Error.');
        } else {
            CaseHistoryDAO::getInstance()->begintransaction();
            try {
                $name = $addr['name'];
                $street1 = $addr['street1'];
                $street2 = $addr['street2'];
                $city = $addr['city'];
                $stateOrProvince = $addr['state'];
                $country = $addr['country'];
                $postalCode = $addr['postalCode'];
                $returnMerchandiseAuthorization = $rma;
                $param['case_id'] = $caseId;
                $param['creationDate'] = time();
                $param['activityDetial_description'] = 'Seller provided return address.';
                $param['author_role'] = 'SELLER';
                $result = CaseHistoryDAO::getInstance()->insert($param);
                $columns = array(
                    'upload_type' => __FUNCTION__,
                    'upload_data' => serialize(compact('caseId_id', 'caseType', 'name', 'street1', 'street2','city','stateOrProvince','country','postalCode','returnMerchandiseAuthorization')),
                    'token' => $token['token'],
                    'create_time' => time()
                );
                $result2 = CaseUploadQueueDAO::getInstance()->iinsert($columns);
                //以下部分是插入我的操作日志表的代码
        		$username = isset(Yii::app()->session['userInfo']['username']) ? Yii::app()->session['userInfo']['username'] : 0;
        		if ($username !== 0) {
        		    $paramArr['handle_user'] = $username;
        		} else {
         		    return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户未登陆');
        		}
        		$paramArr['case_id'] = $caseId;
        		$paramArr['caseType'] = $caseType;
        		$paramArr['create_time'] = time();
        		$paramArr['handle_type'] = __FUNCTION__;
        		$result3 = CaseHandleLogDAO::getInstance()->insert($paramArr);
                if ($result === false || $result2 === false || $result3 === false) {
                    CaseHistoryDAO::getInstance()->rollback();
                    return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
                } else {
                    CaseHistoryDAO::getInstance()->commit();
                    return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
                }
             } catch (Exception $e) {
                CaseHistoryDAO::getInstance()->rollback();
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
            }
        }
    
    }
    
    /**
     * @desc 向ebay 申诉
     * @param string $caseId
     * @param string $caseType
     * @param string $appealReason
     * @param string $responseText
     * @param string $caseId_id
     * @param string $sellerId
     * @author liaojianwen
     * @date 2015-07-22
     */
    public function appealEbay($caseId,$caseType,$appealReason,$responseText,$caseId_id,$sellerId)
    {
      if (empty($caseId) || empty($caseType) || empty($appealReason) || empty($caseId_id)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
        }
      $token = CaseDAO::getInstance()->lawfulCaseID($caseId, $sellerId);
      if ($token === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'CaseID Error.');
      } else {
           CaseHistoryDAO::getInstance()->begintransaction();
           try {
             $param['case_id'] = $caseId;
             $param['creationDate'] = time();
             $param['note'] = $responseText;
             $param['activityDetial_description'] = 'Seller appeal for help';
             $param['author_role'] = 'SELLER';
             $result = CaseHistoryDAO::getInstance()->insert($param);
             $columns = array(
                    'upload_type' => __FUNCTION__,
                    'upload_data' => serialize(compact('caseId_id', 'caseType', 'appealReason', 'responseText')),
                    'token' => $token['token'],
                    'create_time' => time()
                );
             $result2 = CaseUploadQueueDAO::getInstance()->iinsert($columns);
             //以下部分是插入我的操作日志表的代码
             $username = isset(Yii::app()->session['userInfo']['username']) ? Yii::app()->session['userInfo']['username'] : 0;
             if ($username !== 0) {
        		  $paramArr['handle_user'] = $username;
             } else {
         		  return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户未登陆');
             }
        	 $paramArr['case_id'] = $caseId;
        	 $paramArr['caseType'] = $caseType;
        	 $paramArr['responseText'] = $responseText;
        	 $paramArr['create_time'] = time();
        	 $paramArr['handle_type'] = __FUNCTION__;
        	 $paramArr['reason'] = $appealReason;
        	 $result3 = CaseHandleLogDAO::getInstance()->insert($paramArr);
             if ($result === false || $result2 === false || $result3 === false) {
                CaseHistoryDAO::getInstance()->rollback();
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
             } else {
                CaseHistoryDAO::getInstance()->commit();
                return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
             }
           } catch (Exception $e) {
                CaseHistoryDAO::getInstance()->rollback();
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
          }
      }
    
    
    }
    /**
     * @desc 添加case
     * @param array $orderInfo  
     * @param string $sellerId
     * @author liaojianwen
     * @date 2015-07-30
     */
    public function addDispute($orderInfo,$sellerId)
    {
      foreach($orderInfo as $info){
          if (array_search($info['DisputeReason'], DisputeExplanationCodeType2::$type) === false) {
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'DisputeReason Error.');
            }
            
            if ($info['DisputeReason'] == DisputeExplanationCodeType2::$type[0] && array_search($info['DisputeExplanation'], DisputeExplanationCodeType2::$BuyerHasNotPaid) === false) {
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'UPI DisputeExplanation Error.');
            }
            
            if ($info['DisputeReason'] == DisputeExplanationCodeType2::$type[1] && array_search($info['DisputeExplanation'], DisputeExplanationCodeType2::$TransactionMutuallyCanceled) === false) {
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'CANCELED DisputeExplanation Error.');
            }
            
            $token = ShopDAO::getInstance()->getValuesByPk($info['shopid'], array(
                'token',
                'site_id'
            ));
            if (empty($token)) {
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'shopId Error.');
            }
            
            CaseUploadQueueDAO::getInstance()->begintransaction();
            try {
                $columns = array(
                    'upload_type' => __FUNCTION__,
                    'upload_data' => serialize(array(
                        'DisputeReason' => $info['DisputeReason'],
                        'DisputeExplanation' => $info['DisputeExplanation'],
                        'ItemID' => $info['ItemID'],
                        'TransactionID' => $info['TransactionID'],
                        'OrderLineItemID' => $info['OrderLineItemID'],
                        'siteId' => $token['site_id']
                    )),
                    'token' => $token['token'],
                    'create_time' => time()
                );
                $result = CaseUploadQueueDAO::getInstance()->iinsert($columns);
                
                
                if ($result === false) {
                    CaseUploadQueueDAO::getInstance()->rollback();
                    iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                        'type' => 'Err',
                        'orderArr' => $orderInfo,
                        'xml' => $result,
                        'time' => time()
                    ));
                    return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'insert database failure.');
                } else {
                    CaseUploadQueueDAO::getInstance()->commit();
                    iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                        'type' => 'Success',
                        'orderArr' => $orderInfo,
                        'xml' => $result,
                        'time' => time()
                    ));

                }
            } catch (Exception $e) {
                CaseUploadQueueDAO::getInstance()->rollback();
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'insert database failure.');
            }
      }
      return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
    }
   
    
     /**
     * @desc 查找处理人、处理方式
     * @param $caseid
     * @param $sellerId
     * @author liaojianwen
     * @date 2015-07-15
     */
    public function getCaseOperator($caseid,$sellerId)
    {
        if (empty($caseid) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '');
        }
        
        $result = CaseHandleLogDAO::getInstance()->getCaseOperator($caseid);
        if(empty($result)){
            return $this->handleApiForMat(EnumOther::ACK_FAILURE,'','operation is not exists');
        }
        return  $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');   
    }
}