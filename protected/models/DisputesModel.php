<?php

/**
 * @desc dispute Model
 * @author YangLong
 * @date 2015-08-12
 */
class DisputesModel extends BaseModel
{

    /**
     * @desc 覆盖父方法返回DisputesModel对象
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-08-12
     * @return DisputesModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @desc 生成Disputes下载
     * @author YangLong
     * @date 2015-08-12
     * @return null
     */
    public function generateDisputesDownQueue()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $mongo = new MongoClient('mongodb://127.0.0.1:27017', array(
            'connect' => false
        ));
        MongoQueue::$connection = $mongo;
        MongoQueue::$database = 'ImsMongoQueue';
        
        $shops = MsgDownDAO::getInstance()->getEbShop('disputes');
        foreach ($shops as $shop) {
            $_time = time();
            if (empty($shop['disputes_down_time'])) {
                for ($i = 0; $i < 60; $i ++) {
                    $parameters = array(
                        'queue_type' => 'DisputesDownQueue',
                        'token' => $shop['token'],
                        'start_time' => $_time - ($i + 1) * 3600 * 24 * 30,
                        'end_time' => $_time - $i * 3600 * 24 * 30 + EnumOther::OVARLAP_TIME,
                        'shop_id' => $shop['shop_id'],
                        'seller_id' => $shop['seller_id'],
                        'site_id' => $shop['site_id']
                    );
                    MongoQueue::push('QueueTracerModel', 'trace', $parameters, time(), false, 100 - $i);
                }
            } else {
                $parameters = array(
                    'queue_type' => 'DisputesDownQueue',
                    'token' => $shop['token'],
                    'start_time' => $shop['disputes_down_time'] + 0,
                    'end_time' => $_time + EnumOther::OVARLAP_TIME,
                    'shop_id' => $shop['shop_id'],
                    'seller_id' => $shop['seller_id'],
                    'site_id' => $shop['site_id']
                );
                
                $queueType = 'DisputesDownQueue';
                $startTime = time() - 3600 * 24 * 7;
                $min = MongoQueue::findMinAndRmove($queueType, $startTime, "{$shop['shop_id']}");
                if ($min > 0) {
                    $parameters['start_time'] = $min;
                }
                
                MongoQueue::push('QueueTracerModel', 'trace', $parameters, time(), false, 99);
            }
            
            $columns = array(
                'disputes_down_time' => $_time
            );
            $conditions = 'shop_id=:shop_id';
            $params = array(
                ':shop_id' => $shop['shop_id']
            );
            ShopDAO::getInstance()->iupdate($columns, $conditions, $params);
        }
    }

    /**
     * @desc 运行disputes下载队列
     * @param array $parameters
     * @author YangLong
     * @date 2015-08-13
     * @return boolean
     */
    public function executeDisputesDownQueue($parameters)
    {
        $page = 0;
        while (true) {
            $page ++;
            $xmllist = CaseDownModel::model()->getUserDisputes($parameters['start_time'], $parameters['end_time'], $parameters['token'], 
                $parameters['site_id'], $page);
            
            $doc = phpQuery::newDocumentXML($xmllist);
            phpQuery::selectDocument($doc);
            
            $DisputeArray = pq('DisputeArray>Dispute');
            $length = $DisputeArray->length;
            
            for ($i = 0; $i < $length; $i ++) {
                $Dispute = $DisputeArray->eq($i);
                $DisputeID = $Dispute->find('DisputeID')->html();
                $xmldetails = CaseDownModel::model()->getDispute($DisputeID, $parameters['token'], $parameters['site_id']);
                
                $columns = array(
                    'shop_id' => $parameters['shop_id'],
                    'data_xml' => $xmldetails,
                    'type_flag' => $DisputeID,
                    'create_time' => time()
                );
                
                $result = DisputesDownDAO::getInstance()->iinsert($columns);
            }
            
            $columns = array(
                'shop_id' => $parameters['shop_id'],
                'data_xml' => $xmllist,
                'type_flag' => 'list',
                'create_time' => time()
            );
            
            $result = DisputesDownDAO::getInstance()->iinsert($columns);
            
            if (stripos($xmllist, '<Ack>Failure</Ack>') !== false || $result === false) {
                return false;
            }
            
            if (stripos($xmllist, '<TotalNumberOfPages>' . $page . '</TotalNumberOfPages>') !== false ||
                 stripos($xmllist, '<TotalNumberOfPages>0</TotalNumberOfPages>') !== false) {
                break;
            }
        }
        return true;
    }

    /**
     * @desc 获取已经下载的Disputes数据
     * @param int $taskNumber
     * @author liaojianwen
     * @date 2015-08-18
     * @return Ambigous <string, multitype:, mixed>|boolean
     */
    public function getDownloadDisputes($taskNumber)
    {
        DisputesDownDAO::getInstance()->begintransaction();
        try {
            // 获取符合条件的数据
            $result = DisputesDownDAO::getInstance()->getDownloadDisputes($taskNumber);
            
            if (empty($result)) {
                DisputesDownDAO::getInstance()->rollback();
                return false;
            }
            
            // 拼接ID数组
            $_ids = array();
            foreach ($result as $key => $value) {
                $_ids[] = $value['disputes_down_id'];
            }
            $_ids = implode(',', $_ids);
            
            $columns = array(
                'process_sign' => boolConvert::toInt01(true),
                'picktime' => time()
            );
            $conditions = "disputes_down_id in ({$_ids})";
            DisputesDownDAO::getInstance()->iupdate($columns, $conditions, array()); // 标记为正在处理
            
            DisputesDownDAO::getInstance()->increase('pickcount', "disputes_down_id in ({$_ids})"); // 运行次数+1
            
            DisputesDownDAO::getInstance()->commit();
            return $result;
        } catch (Exception $e) {
            DisputesDownDAO::getInstance()->rollback();
            return false;
        }
    }

    /**
     * @desc 删除已经处理了的disputes原始数据
     * @param string $ids
     * @author liaojianwen
     * @date 2015-08-18
     * @return Ambigous <boolean, number>
     */
    public function deleteDisputesDownData($ids)
    {
        return DisputesDownDAO::getInstance()->deleteByIds($ids);
    }

    /**
     *@desc 解析disputes
     *@param array $disputes
     *@author liaojianwen
     *@date 2015-08-19             
     */
    public function parseDisputes($disputes)
    {
        // try{
        $dids = array();
        if ($disputes['Ack'] == 'Success' && is_array($disputes['body'])) {
            foreach ($disputes['body'] as $key => &$value) {
                if (isset($value['data_xml'])) {
                    $doc = phpQuery::newDocumentXML($value['data_xml']);
                    phpQuery::selectDocument($doc);
                    if (pq('Ack')->html() === 'Success') {
                        $dispute = pq('Dispute');
                        $dispute_length = $dispute->length;
                        $shop_id = $value['shop_id'];
                        $downid = $value['disputes_down_id'];
                        $dids[] = $value['disputes_down_id'];
                        for ($i = 0; $i < $dispute_length; $i ++) {
                            $disputeId = $dispute->eq($i)
                                ->find('DisputeID')
                                ->html();
                            $column_list = array(
                                'DisputeCreatedTime' => strtotime($dispute->eq($i)
                                    ->find('DisputeCreatedTime')
                                    ->html()),
                                'shop_id' => $value['shop_id'],
                                'DisputeCreditEligibility' => $dispute->eq($i)
                                    ->find('DisputeCreditEligibility')
                                    ->html(),
                                'DisputeExplanation' => $dispute->eq($i)
                                    ->find('DisputeExplanation')
                                    ->html(),
                                'DisputeID' => $disputeId,
                                'DisputeModifiedTime' => strtotime($dispute->eq($i)
                                    ->find('DisputeModifiedTime')
                                    ->html()),
                                'DisputeReason' => $dispute->eq($i)
                                    ->find('DisputeReason')
                                    ->html(),
                                'DisputeRecordType' => $dispute->eq($i)
                                    ->find('DisputeRecordType')
                                    ->html(),
                                'DisputeState' => $dispute->eq($i)
                                    ->find('DisputeState')
                                    ->html(),
                                'DisputeStatus' => $dispute->eq($i)
                                    ->find('DisputeStatus')
                                    ->html(),
                                'OrderLineItemID' => $dispute->eq($i)
                                    ->find('OrderLineItemID')
                                    ->html(),
                                'TransactionID' => $dispute->eq($i)
                                    ->find('TransactionID')
                                    ->html(),
                                'OrderLineItemID' => $dispute->eq($i)
                                    ->find('OrderLineItemID')
                                    ->html(),
                                'OtherPartyName' => $dispute->eq($i)
                                    ->find('OtherPartyName')
                                    ->html(),
                                'OtherPartyRole' => $dispute->eq($i)
                                    ->find('OtherPartyRole')
                                    ->html(),
                                'TransactionID' => $dispute->eq($i)
                                    ->find('TransactionID')
                                    ->html(),
                                'UserRole' => $dispute->eq($i)
                                    ->find('UserRole')
                                    ->html(),
                                'create_time' => time()
                            );
                            
                            $_conditions = array(
                                'DisputeID' => $column_list['DisputeID']
                            );
                            DisputesDAO::getInstance()->isExists($_conditions, true);
                            if (isset($_conditions['disputes_id']) && $_conditions['disputes_id'] > 0) {
                                $conditions = 'disputes_id=:disputes_id';
                                $ps = array(
                                    ':disputes_id' => $_conditions['disputes_id']
                                );
                                unset($column_list['DisputeID']);
                                DisputesDAO::getInstance()->iupdate($column_list, $conditions, $ps);
                                $disputes_id = $_conditions['disputes_id'];
                            } else {
                                DisputesDAO::getInstance()->insert($column_list, true);
                                $disputes_id = DisputesDAO::getInstance()->getLastInsertID();
                            }
                            $conditions = 'disputes_id=:disputes_id';
                            $params = array(
                                ':disputes_id' => $disputes_id
                            );
                            $resolution = $dispute->eq($i)->find('DisputeResolution');
                            $resolution_len = $resolution->length;
                            DisputeResolutionDAO::getInstance()->idelete($conditions, $params);
                            for ($j = 0; $j < $resolution_len; $j ++) {
                                $colum_resolution = array(
                                    'disputes_id' => $disputes_id,
                                    'DisputeResolutionReason' => $resolution->eq($j)
                                        ->find('DisputeResolutionReason')
                                        ->html(),
                                    'DisputeResolutionRecordType' => $resolution->eq($j)
                                        ->find('DisputeResolutionRecordType')
                                        ->html(),
                                    'ResolutionTime' => strtotime($resolution->eq($j)
                                        ->find('ResolutionTime')
                                        ->html())
                                );
                                DisputeResolutionDAO::getInstance()->iinsert($colum_resolution);
                            }
                            
                            $msg = $dispute->eq($i)->find('DisputeMessage');
                            $msg_length = $msg->length;
                            DisputeMsgDAO::getInstance()->idelete($conditions, $params);
                            for ($k = 0; $k < $msg_length; $k ++) {
                                $column_msg = array(
                                    'disputes_id' => $disputes_id,
                                    'MessageCreationTime' => strtotime($msg->eq($k)
                                        ->find('MessageCreationTime')
                                        ->html()),
                                    'MessageID' => $msg->eq($k)
                                        ->find('MessageID')
                                        ->html(),
                                    'MessageSource' => $msg->eq($k)
                                        ->find('MessageSource')
                                        ->html(),
                                    'MessageText' => $msg->eq($k)
                                        ->find('MessageText')
                                        ->html()
                                );
                                DisputeMsgDAO::getInstance()->iinsert($column_msg);
                            }
                            $detail = DisputesDownDAO::getInstance()->findByAttributes(array(
                                'type_flag' => $disputeId
                            ), array(
                                'data_xml,disputes_down_id'
                            ), array(
                                'disputes_down_id ASC'
                            ));
                            if (! empty($detail)) {
                                $conditions_det = 'DisputeID =:dispute';
                                $params_det = array(
                                    ':dispute' => $disputeId
                                );
                                $ddoc = phpQuery::newDocumentXML($detail['data_xml']);
                                phpQuery::selectDocument($ddoc);
                                DisputesDAO::getInstance()->begintransaction();
                                try {
                                    if ($ddoc['GetDisputeResponse>Ack']->html() == 'Success') {
                                        $det = $ddoc['GetDisputeResponse>Dispute'];
                                        $column_det = array(
                                            'BuyerUserID' => $det->find('BuyerUserID')->html(),
                                            'DisputeCreatedTime' => strtotime($det->find('DisputeCreatedTime')->html()),
                                            'DisputeCreditEligibility' => $det->find('DisputeCreditEligibility')->html(),
                                            'DisputeExplanation' => $det->find('DisputeExplanation')->html(),
                                            'DisputeID' => $det->find('DisputeID')->html(),
                                            'DisputeModifiedTime' => strtotime($det->find('DisputeModifiedTime')->html()),
                                            'DisputeReason' => $det->find('DisputeReason')->html(),
                                            'DisputeRecordType' => $det->find('DisputeRecordType')->html(),
                                            'DisputeState' => $det->find('DisputeState')->html(),
                                            'DisputeStatus' => $det->find('DisputeStatus')->html(),
                                            'TransactionID' => $det->find('TransactionID')->html(),
                                            'OrderLineItemID' => $det->find('OrderLineItemID')->html(),
                                            'Escalation' => boolConvert::toInt01($det->find('Escalation')->html()),
                                            'i_ItemID' => $det->find('Item>ItemID')->html(),
                                            'i_ld_EndTime' => strtotime($det->find('Item>ListingDetails>EndTime')->html()),
                                            'i_ld_StartTime' => strtotime($det->find('Item>ListingDetails>StartTime')->html()),
                                            'i_Quantity' => $det->find('Item>Quantity')->html(),
                                            'i_ss_ConvertedCurrentPrice' => $det->find('Item>SellingStatus>ConvertedCurrentPrice')->html(),
                                            'i_ss_ConvertedCurrentPrice_currencyID' => $det->find('Item>SellingStatus>ConvertedCurrentPrice')->attr(
                                                'currencyID'),
                                            'i_ss_CurrentPrice' => $det->find('Item>SellingStatus>CurrentPrice')->html(),
                                            'i_ss_CurrentPrice_currencyID' => $det->find('Item>SellingStatus>CurrentPrice')->attr('currencyID'),
                                            'PurchaseProtection' => boolConvert::toInt01($det->find('PurchaseProtection')->html()),
                                            'SellerUserID' => $det->find('SellerUserID')->html()
                                        );
                                        DisputesDAO::getInstance()->iupdate($column_det, $conditions_det, $params_det);
                                    }
                                    DisputesDAO::getInstance()->commit();
                                } catch (Exception $e) {
                                    iMongo::getInstance()->setCollection('DisputesDetailParseErr')->insert(
                                        array(
                                            'getCode' => $e->getCode(),
                                            'getFile' => $e->getFile(),
                                            'getLine' => $e->getLine(),
                                            'getMessage' => $e->getMessage(),
                                            'time' => time()
                                        ));
                                    DisputesDAO::getInstance()->rollback();
                                }
                                unset($column_det);
                                $dids[] = $detail['disputes_down_id'];
                            } else {
                                iMongo::getInstance()->setCollection('MissingDisputesDetail')->insert(
                                    array(
                                        'DisputeID' => $disputeId,
                                        'getCode' => $e->getCode(),
                                        'getFile' => $e->getFile(),
                                        'getLine' => $e->getLine(),
                                        'getMessage' => $e->getMessage(),
                                        'time' => time()
                                    ));
                            }
                            unset($detail);
                        }
                    }
                }
                unset($value);
            }
        }
        return $dids;
        // } catch (Exception $e) {
        // iMongo::getInstance()->setCollection('parseDisputesErrA')->insert(array(
        // 'getCode' => $e->getCode(),
        // 'getFile' => $e->getFile(),
        // 'getLine' => $e->getLine(),
        // 'getMessage' => $e->getMessage(),
        // 'time' => time()
        // ));
        // return false;
        // }
    }

    /**
     * @desc 获取卖家发起的cancel dispute列表
     * @param String  $Type      case类型
     * @param integer $page      页码
     * @param integer $pageSize  分页大小
     * @param string $status     查询条件状态
     * @param integer $itemId    查询条件itemNo
     * @param  stirng $cust      查询条件客户
     * @param string $query 判断是查询还是列表展示/query 为查询
     * @author liaojianwen
     * @date 2015-08-19
     * @reutrn array 列表信息
     */
    public function getCancleDisputeList($Type, $page, $pageSize, $status, $itemId, $cust, $query)
    {
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
        $param = 'TransactionMutuallyCanceled';
        $paramArr['param'] = $param;
        $paramArr['shop_id'] = $shopId;
        
        // 开始查询数据库
        $res = DisputesDAO::getInstance()->getCancelDisputeList($paramArr, $page, $pageSize, $status, $itemId, $cust);
        if (empty($res['list'])) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '获取数据失败');
        }
        
        $result = $this->handleApiFormat(EnumOther::ACK_SUCCESS, $res, '');
        return $result;
    }

    /**
     * @desc 获取卖方发起cancel dispute的详细信息
     * @param $disputeid dispute的id
     * @param $type dispute的类型
     * @author liaojianwen
     * @date 2015-08-20
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function getCancelDisputeDetail($disputeid, $type)
    {
        if ($type == 'cancel') {
            $params = 'TransactionMutuallyCanceled';
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '类型错误,只返回取消订单类型');
        }
        if (empty($disputeid)) {
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
        $res = DisputesDAO::getInstance()->getCancelDisputeDetail($disputeid, $shopId);
        if (isset(Yii::app()->session['switchInfo']['accountId']) && ! empty(Yii::app()->session['switchInfo']['accountId'])) {
            $shopId = Yii::app()->session['switchInfo']['accountId'];
        }
        if (empty($res)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '查询数据库失败');
        }
        $prenextID = DisputesDAO::getInstance()->getPreNexID($res['DisputeCreatedTime'], $disputeid, $paramArr, $shopId);
        $result['list'] = $res;
        $result['prenextID'] = $prenextID;
        
        // guest ItemID hide
        if (Yii::app()->session['userInfo']['user_id'] == 99999) {
            $result['user_id'] = 99999;
        }
        
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
    }

    /**
     * @desc 获取卖方发起cancel dispute的历史对话
     * @param disputeid  dispute的id
     * @author liaojianwen
     * @date 2015-08-20
     */
    public function getCancelDisputeMessage($disputeid)
    {
        if (empty($disputeid)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '返回id是空的');
        }
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
        $result['list'] = DisputeMsgDAO::getInstance()->getCancelDisputeMessage($disputeid, $shopId);
        if (empty($result['list'])) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '查询失败');
        }
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
    }
}
