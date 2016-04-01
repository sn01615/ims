<?php

/**
 * @desc return处理类
 * @author liaojianwen
 * @date 2015-06-16
 */
class ReturnModel extends BaseModel
{

    /**
     * @desc 覆盖父方法返回ReturnModel对象
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-06-16
     * @return ReturnModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @desc 获取列表
     * @param string $status
     * @param string $itemId
     * @param string $cust
     * @param int $page
     * @param int $pageSize
     * @author liaojianwen
     * @date 2015-06-16
     * @return multitype:
     */
    public function getReturnList($status, $itemId, $cust, $page, $pageSize)
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
        $paramArr['shop_id'] = $shopId;
        
        $res = ReturnDAO::getInstance()->getReturnList($paramArr, $status, $itemId, $cust, $page, $pageSize);
        if (empty($res['list'])) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'no data');
        }
        $result = $this->handleApiFormat(EnumOther::ACK_SUCCESS, $res, '');
        return $result;
    }

    /**
     * @desc 解析returns
     * @param array $returns            
     * @author liaojianwen
     * @date 2015-06-16
     * @return multitype:mixed |boolean
     */
    public function parseReturns(&$returns)
    {
        $dids = array();
        if ($returns['Ack'] == 'Success' && is_array($returns['body'])) {
            foreach ($returns['body'] as $key => &$value) {
                $value['text_json'] = unserialize(base64_decode($value['text_json']));
                if (is_array($value['text_json']) && isset($value['text_json']['Returns'])) {
                    $ret = ReturnDownModel::model()->parseNamespaceXml($value['text_json']['Returns']);
                    $doc = phpQuery::newDocumentXML($ret);
                    phpQuery::selectDocument($doc);
                    $request = pq('ns1_returns');
                    $length = $request->length;
                    for ($i = 0; $i < $length; $i ++) {
                        $_return = $request->eq($i);
                        
                        $returnId_id = $_return->find('ns1_ReturnId>ns1_id')->html();
                        $columns = array(
                            'returnId_id' => $returnId_id,
                            'shop_id' => $value['shop_id'],
                            'return_type' => $_return->find('ns1_ReturnType')->html(),
                            'otherParty_userId' => $_return->find('ns1_otherParty>ns1_userId')->html(),
                            'otherParty_role' => $_return->find('ns1_otherParty>ns1_role')->html(),
                            'responseDue_party_userId' => $_return->find('ns1_responseDue>ns1_party>ns1_userId')->html(),
                            'responseDue_party_role' => $_return->find('ns1_responseDue>ns1_party>ns1_role')->html(),
                            'responseDue_respondByDate' => strtotime($_return->find('ns1_responseDue>ns1_respondByDate')->html()),
                            'rR_comments' => $_return->find('ns1_returnRequest>ns1_comments')->html(),
                            'rR_returnReason_code' => $_return->find('ns1_returnRequest>ns1_returnReason>ns1_code')->html(),
                            'rR_returnReason_description' => $_return->find('ns1_returnRequest>ns1_returnReason>ns1_description')->html(),
                            'status' => $_return->find('ns1_status')->html(),
                            'creationDate' => strtotime($_return->find('ns1_creationDate')->html()),
                            'create_time' => time()
                        );
                        foreach ($columns as $k => $val) {
                            if ($val === false || $val === null) {
                                unset($columns[$k]);
                            }
                        }
                        $_conditions = array(
                            'returnId_id' => $columns['returnId_id']
                        );
                        ReturnDAO::getInstance()->isExists($_conditions, true);
                        if (isset($_conditions['return_request_id']) && $_conditions['return_request_id'] > 0) {
                            $conditions = 'return_request_id=:return_request_id';
                            $ps = array(
                                ':return_request_id' => $_conditions['return_request_id']
                            );
                            unset($columns['returnId_id']);
                            ReturnDAO::getInstance()->iupdate($columns, $conditions, $ps);
                            $return_id = $_conditions['return_request_id'];
                        } else {
                            ReturnDAO::getInstance()->insert($columns);
                            $return_id = ReturnDAO::getInstance()->getLastInsertID();
                        }
                        $conditions = 'return_id=:return_id';
                        $params = array(
                            ':return_id' => $return_id
                        );
                        // 明细
                        if (isset($value['text_json']['ReturnDetail']) && is_array($value['text_json']['ReturnDetail']) && isset($value['text_json']['ReturnDetail'][$returnId_id])) {
                            $det = json_decode($value['text_json']['ReturnDetail'][$returnId_id], true);
                            if (isset($det['ackValue']) && $det['ackValue'] == 'SUCCESS') {
                                $columns_det = array(
                                    'return_id' => $return_id,
                                    'S_buyerLoginName' => isset($det['summary']['buyerLoginName']) ? $det['summary']['buyerLoginName'] : '',
                                    'S_sellerLoginName' => isset($det['summary']['sellerLoginName']) ? $det['summary']['sellerLoginName'] : '',
                                    'S_currentType' => isset($det['summary']['currentType']) ? $det['summary']['currentType'] : '',
                                    'S_state' => isset($det['summary']['state']) ? $det['summary']['state'] : '',
                                    'S_status' => isset($det['summary']['status']) ? $det['summary']['status'] : '',
                                    'S_CI_reason' => isset($det['summary']['creationInfo']['reason']) ? $det['summary']['creationInfo']['reason'] : '',
                                    'S_CI_comments' => isset($det['summary']['creationInfo']['comments']) ? $det['summary']['creationInfo']['comments'] : '',
                                    'S_CI_creationDate' => isset($det['summary']['creationInfo']['creationDate']) ? $det['summary']['creationInfo']['creationDate'] / 1000 : 0,
                                    'S_sTR_estimatedRefundAmount' => isset($det['summary']['sellerTotalRefund']['estimatedRefundAmount']['value']) ? $det['summary']['sellerTotalRefund']['estimatedRefundAmount']['value'] : 0.00,
                                    'S_sTR_currencyId' => isset($det['summary']['sellerTotalRefund']['estimatedRefundAmount']['currencyId']) ? $det['summary']['sellerTotalRefund']['estimatedRefundAmount']['currencyId'] : '',
                                    'S_bTR_estimatedRefundAmount' => isset($det['summary']['buyerTotalRefund']['estimatedRefundAmount']['value']) ? $det['summary']['buyerTotalRefund']['estimatedRefundAmount']['value'] : 0.00,
                                    'S_bTR_currencyId' => isset($det['summary']['buyerTotalRefund']['estimatedRefundAmount']['currencyId']) ? $det['summary']['buyerTotalRefund']['estimatedRefundAmount']['currencyId'] : '',
                                    'S_sRD_activityDue' => isset($det['summary']['sellerResponseDue']['activityDue']) ? $det['summary']['sellerResponseDue']['activityDue'] : '',
                                    'S_sRD_respondByDate' => isset($det['summary']['sellerResponseDue']['respondByDate']) ? $det['summary']['sellerResponseDue']['respondByDate'] / 1000 : 0,
                                    'S_bRD_activityDue' => isset($det['summary']['buyerResponseDue']['activityDue']) ? $det['summary']['buyerResponseDue']['activityDue'] : '',
                                    'S_bRD_respondByDate' => isset($det['summary']['buyerResponseDue']['respondByDate']) ? $det['summary']['buyerResponseDue']['respondByDate'] / 1000 : 0,
                                    'S_eI_eBPCaseId' => isset($det['summary']['escalationInfo']['eBPCaseId']) ? $det['summary']['escalationInfo']['eBPCaseId'] : '',
                                    'S_eI_caseType' => isset($det['summary']['escalationInfo']['caseType']) ? $det['summary']['escalationInfo']['caseType'] : '',
                                    'D_marketplaceId' => isset($det['detail']['marketplaceId']) ? $det['detail']['marketplaceId'] : '',
                                    'D_iD_itemId' => isset($det['detail']['itemDetail']['itemId']) ? $det['detail']['itemDetail']['itemId'] : '',
                                    'D_iD_transactionId' => isset($det['detail']['itemDetail']['transactionId']) ? $det['detail']['itemDetail']['transactionId'] : '',
                                    'D_iD_returnQuantity' => isset($det['detail']['itemDetail']['returnQuantity']) ? $det['detail']['itemDetail']['returnQuantity'] : 0,
                                    'D_iD_itemTitle' => isset($det['detail']['itemDetail']['itemTitle']) ? $det['detail']['itemDetail']['itemTitle'] : '',
                                    'D_iD_itemPicUrl' => isset($det['detail']['itemDetail']['itemPicUrl']) ? $det['detail']['itemDetail']['itemPicUrl'] : '',
                                    'D_iD_transactionDate' => isset($det['detail']['itemDetail']['transactionDate']) ? $det['detail']['itemDetail']['transactionDate'] / 1000 : 0,
                                    'D_iD_itemPrice' => isset($det['detail']['itemDetail']['itemPrice']['value']) ? $det['detail']['itemDetail']['itemPrice']['value'] : 0.00,
                                    'D_iD_currencyId' => isset($det['detail']['itemDetail']['itemPrice']['currencyId']) ? $det['detail']['itemDetail']['itemPrice']['currencyId'] : '',
                                    'D_buyerEmailAddress' => isset($det['detail']['buyerEmailAddress']) ? $det['detail']['buyerEmailAddress'] : '',
                                    'D_sellerEmailAddress' => isset($det['detail']['sellerEmailAddress']) ? $det['detail']['sellerEmailAddress'] : '',
                                    'D_checkoutType' => isset($det['detail']['checkoutType']) ? $det['detail']['checkoutType'] : '',
                                    'D_bA_name' => isset($det['detail']['buyerAddress']['name']) ? $det['detail']['buyerAddress']['name'] : '',
                                    'D_bA_street1' => isset($det['detail']['buyerAddress']['street1']) ? $det['detail']['buyerAddress']['street1'] : '',
                                    'D_bA_street2' => isset($det['detail']['buyerAddress']['street2']) ? $det['detail']['buyerAddress']['street2'] : '',
                                    'D_bA_city' => isset($det['detail']['buyerAddress']['city']) ? $det['detail']['buyerAddress']['city'] : '',
                                    'D_bA_county' => isset($det['detail']['buyerAddress']['county']) ? $det['detail']['buyerAddress']['county'] : '',
                                    'D_bA_stateOrProvince' => isset($det['detail']['buyerAddress']['stateOrProvince']) ? $det['detail']['buyerAddress']['stateOrProvince'] : '',
                                    'D_bA_country' => isset($det['detail']['buyerAddress']['country']) ? $det['detail']['buyerAddress']['country'] : '',
                                    'D_bA_postalCode' => isset($det['detail']['buyerAddress']['postalCode']) ? $det['detail']['buyerAddress']['postalCode'] : '',
                                    'D_bA_any' => isset($det['detail']['buyerAddress']['any']) ? $det['detail']['buyerAddress']['any'] : '',
                                    'D_sA_name' => isset($det['detail']['sellerAddress']['name']) ? $det['detail']['sellerAddress']['name'] : '',
                                    'D_sA_street1' => isset($det['detail']['sellerAddress']['street1']) ? $det['detail']['sellerAddress']['street1'] : '',
                                    'D_sA_street2' => isset($det['detail']['sellerAddress']['street2']) ? $det['detail']['sellerAddress']['street2'] : '',
                                    'D_sA_city' => isset($det['detail']['sellerAddress']['city']) ? $det['detail']['sellerAddress']['city'] : '',
                                    'D_sA_county' => isset($det['detail']['sellerAddress']['county']) ? $det['detail']['sellerAddress']['county'] : '',
                                    'D_sA_stateOrProvince' => isset($det['detail']['sellerAddress']['stateOrProvince']) ? $det['detail']['sellerAddress']['stateOrProvince'] : '',
                                    'D_sA_country' => isset($det['detail']['sellerAddress']['country']) ? $det['detail']['sellerAddress']['country'] : '',
                                    'D_sA_postalCode' => isset($det['detail']['sellerAddress']['postalCode']) ? $det['detail']['sellerAddress']['postalCode'] : '',
                                    'D_sA_any' => isset($det['detail']['sellerAddress']['any']) ? json_encode($det['detail']['sellerAddress']['any']) : '',
                                    'D_rSI_sT_shippingMethod' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['shippingMethod']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['shippingMethod'] : '',
                                    'D_rSI_sT_shippedBy' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['shippedBy']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['shippedBy'] : '',
                                    'D_rSI_sT_trackingNumber' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['trackingNumber']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['trackingNumber'] : '',
                                    'D_rSI_sT_carrierId' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['carrierId']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['carrierId'] : '',
                                    'D_rSI_sT_carrierEnum' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['carrierEnum']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['carrierEnum'] : '',
                                    'D_rSI_sT_carrierName' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['carrierName']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['carrierName'] : '',
                                    'D_rSI_sT_carrierUsed' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['carrierUsed']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['carrierUsed'] : '',
                                    'D_rSI_sT_deliveryStatus' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['deliveryStatus']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['deliveryStatus'] : '',
                                    'D_rSI_sT_toShip_name' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['toShippingAddress']['name']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['toShippingAddress']['name'] : '',
                                    'D_rSI_sT_toShip_street1' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['toShippingAddress']['street1']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['toShippingAddress']['street1'] : '',
                                    'D_rSI_sT_toShip_street2' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['toShippingAddress']['street2']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['toShippingAddress']['street2'] : '',
                                    'D_rSI_sT_toShip_city' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['toShippingAddress']['city']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['toShippingAddress']['city'] : '',
                                    'D_rSI_sT_toShip_county' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['toShippingAddress']['county']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['toShippingAddress']['county'] : '',
                                    'D_rSI_sT_toShip_stateOrProvince' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['toShippingAddress']['stateOrProvince']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['toShippingAddress']['stateOrProvince'] : '',
                                    'D_rSI_sT_toShip_country' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['toShippingAddress']['country']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['toShippingAddress']['country'] : '',
                                    'D_rSI_sT_toShip_postalCode' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['toShippingAddress']['postalCode']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['toShippingAddress']['postalCode'] : '',
                                    'D_rSI_sT_toShip_any' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['toShippingAddress']['any']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['toShippingAddress']['any'] : '',
                                    'D_rSI_sT_fromShip_name' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['fromShippingAddress']['name']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['fromShippingAddress']['name'] : '',
                                    'D_rSI_sT_fromShip_street1' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['fromShippingAddress']['street1']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['fromShippingAddress']['street1'] : '',
                                    'D_rSI_sT_fromShip_street2' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['fromShippingAddress']['street2']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['fromShippingAddress']['street2'] : '',
                                    'D_rSI_sT_fromShip_city' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['fromShippingAddress']['city']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['fromShippingAddress']['city'] : '',
                                    'D_rSI_sT_fromShip_county' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['fromShippingAddress']['county']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['fromShippingAddress']['county'] : '',
                                    'D_rSI_sT_fromShip_stateOrProvince' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['fromShippingAddress']['stateOrProvince']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['fromShippingAddress']['stateOrProvince'] : '',
                                    'D_rSI_sT_fromShip_country' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['fromShippingAddress']['country']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['fromShippingAddress']['country'] : '',
                                    'D_rSI_sT_fromShip_postalCode' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['fromShippingAddress']['postalCode']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['fromShippingAddress']['postalCode'] : '',
                                    'D_rSI_sT_fromShip_any' => isset($det['detail']['returnShipmentInfo']['shipmentTracking']['fromShippingAddress']['any']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['fromShippingAddress']['any'] : '',
                                    'D_rSI_sT_markAsReceived' => boolConvert::toInt01(isset($det['detail']['returnShipmentInfo']['shipmentTracking']['markAsReceived']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['markAsReceived'] : false),
                                    'D_rSI_sT_active' => boolConvert::toInt01(isset($det['detail']['returnShipmentInfo']['shipmentTracking']['active']) ? $det['detail']['returnShipmentInfo']['shipmentTracking']['active'] : false),
                                    'D_rSI_sLC_totalAmount' => isset($det['detail']['returnShipmentInfo']['shippingLabelCost']['totalAmount']['value']) ? $det['detail']['returnShipmentInfo']['shippingLabelCost']['totalAmount']['value'] : 0.00,
                                    'D_rSI_sLC_currencyId' => isset($det['detail']['returnShipmentInfo']['shippingLabelCost']['totalAmount']['currencyId']) ? $det['detail']['returnShipmentInfo']['shippingLabelCost']['totalAmount']['currencyId'] : '',
                                    'D_rSI_payee' => isset($det['detail']['returnShipmentInfo']['payee']) ? $det['detail']['returnShipmentInfo']['payee'] : '',
                                    'D_returnMerchandiseAuthorization' => isset($det['detail']['returnMerchandiseAuthorization']) ? $det['detail']['returnMerchandiseAuthorization'] : '',
                                    'D_closeReason' => isset($det['detail']['closeReason']) ? $det['detail']['closeReason'] : '',
                                    'D_cI_returnCloseReason' => isset($det['detail']['closeInfo']['returnCloseReason']) ? $det['detail']['closeInfo']['returnCloseReason'] : '',
                                    'D_cI_buyerCloseReason' => isset($det['detail']['closeInfo']['buyerCloseReason']) ? $det['detail']['closeInfo']['buyerCloseReason'] : '',
                                    'create_time' => time()
                                );
                                // returndetail表数据更新或插入
                                
                                ReturnDetailDAO::getInstance()->ireplaceinto($columns_det, $conditions, $params);
                                
                                $buyOption = isset($det['summary']['buyerAvailableOption']) ? $det['summary']['buyerAvailableOption'] : array();
                                BuyerOptionDAO::getInstance()->idelete($conditions, $params);
                                foreach ($buyOption as $option) {
                                    $columns_buy_option = array(
                                        'return_id' => $return_id,
                                        'actionType' => isset($option['actionType']) ? $option['actionType'] : '',
                                        'actionURL' => isset($option['actionURL']) ? $option['actionURL'] : '',
                                        'create_time' => time()
                                    );
                                    // buyer_available_option 表数据插入
                                    BuyerOptionDAO::getInstance()->iinsert($columns_buy_option);
                                }
                                
                                $buyOption = isset($det['summary']['sellerAvailableOption']) ? $det['summary']['sellerAvailableOption'] : array();
                                SellerOptionDAO::getInstance()->idelete($conditions, $params);
                                foreach ($buyOption as $option) {
                                    $columns_sell_option = array(
                                        'return_id' => $return_id,
                                        'actionType' => isset($option['actionType']) ? $option['actionType'] : '',
                                        'actionURL' => isset($option['actionURL']) ? $option['actionURL'] : '',
                                        'create_time' => time()
                                    );
                                    // seller_available_option 表数据插入
                                    SellerOptionDAO::getInstance()->iinsert($columns_sell_option);
                                }
                                
                                $return_his = isset($det['detail']['responseHistory']) ? $det['detail']['responseHistory'] : array();
                                ReturnHistoryDAO::getInstance()->idelete($conditions, $params);
                                foreach ($return_his as $history) {
                                    $columns_his = array(
                                        'return_id' => $return_id,
                                        'author' => isset($history['author']) ? $history['author'] : '',
                                        'activity' => isset($history['activity']) ? $history['activity'] : '',
                                        'fromState' => isset($history['fromState']) ? $history['fromState'] : '',
                                        'toState' => isset($history['toState']) ? $history['toState'] : '',
                                        'creationDate' => isset($history['creationDate']) ? $history['creationDate'] / 1000 : 0,
                                        'note' => isset($history['note']) ? $history['note'] : '',
                                        'sellerReturnAddr_name' => isset($history['attributes']['sellerReturnAddress']['name']) ? $history['attributes']['sellerReturnAddress']['name'] : '',
                                        'sellerReturnAddr_street1' => isset($history['attributes']['sellerReturnAddress']['street1']) ? $history['attributes']['sellerReturnAddress']['street1'] : '',
                                        'sellerReturnAddr_street2' => isset($history['attributes']['sellerReturnAddress']['street2']) ? $history['attributes']['sellerReturnAddress']['street2'] : '',
                                        'sellerReturnAddr_city' => isset($history['attributes']['sellerReturnAddress']['city']) ? $history['attributes']['sellerReturnAddress']['city'] : '',
                                        'sellerReturnAddr_county' => isset($history['attributes']['sellerReturnAddress']['county']) ? $history['attributes']['sellerReturnAddress']['county'] : '',
                                        'sellerReturnAddr_stateOrProvince' => isset($history['attributes']['sellerReturnAddress']['stateOrProvince']) ? $history['attributes']['sellerReturnAddress']['stateOrProvince'] : '',
                                        'sellerReturnAddr_country' => isset($history['attributes']['sellerReturnAddress']['country']) ? $history['attributes']['sellerReturnAddress']['country'] : '',
                                        'sellerReturnAddr_postalCode' => isset($history['attributes']['sellerReturnAddress']['postalCode']) ? $history['attributes']['sellerReturnAddress']['postalCode'] : '',
                                        'sellerReturnAddr_any' => isset($history['attributes']['sellerReturnAddress']['any']) ? $history['attributes']['sellerReturnAddress']['any'] : '',
                                        'escalateReason' => isset($history['attributes']['escalateReason']) ? $history['attributes']['escalateReason'] : '',
                                        'trackingNumber' => isset($history['trackingNumber']) ? $history['trackingNumber'] : '',
                                        'carrierUsed' => isset($history['carrierUsed']) ? $history['carrierUsed'] : '',
                                        'partialRefundAmount' => isset($history['partialRefundAmount']) ? $history['partialRefundAmount'] : 0.00,
                                        'rma' => isset($history['attributes']['rma']) ? $history['attributes']['rma'] : '',
                                        'create_time' => time()
                                    );
                                    // return_response_history表数据插入
                                    ReturnHistoryDAO::getInstance()->iinsert($columns_his);
                                }
                                
                                $return_money = isset($det['detail']['moneyMovementInfo']) ? $det['detail']['moneyMovementInfo'] : array();
                                ReturnMoneyMovementDAO::getInstance()->idelete($conditions, $params);
                                foreach ($return_money as $money) {
                                    $columns_mon = array(
                                        'return_id' => $return_id,
                                        'moneyMovementType' => isset($money['moneyMovementType']) ? $money['moneyMovementType'] : '',
                                        'status' => isset($money['status']) ? $money['status'] : '',
                                        'requestedAmount_value' => isset($money['requestedAmount']['value']) ? $money['requestedAmount']['value'] : 0.00,
                                        'requestedAmount_currencyId' => isset($money['requestedAmount']['currencyId']) ? $money['requestedAmount']['currencyId'] : '',
                                        'actualAmount_value' => isset($money['actualAmount']['value']) ? $money['actualAmount']['value'] : 0.00,
                                        'actualAmount_currencyId' => isset($money['actualAmount']['currencyId']) ? $money['actualAmount']['currencyId'] : '',
                                        'creationDate' => isset($money['creationDate']) ? $money['creationDate'] / 1000 : 0,
                                        'externalPaymentTrxnId' => isset($money['externalPaymentTrxnId']) ? $money['externalPaymentTrxnId'] : '',
                                        'externalPaymentTrxnType' => isset($money['externalPaymentTrxnType']) ? $money['externalPaymentTrxnType'] : '',
                                        'create_time' => time()
                                    );
                                    // return_response_history表数据插入
                                    if (isset($columns_mon)) {
                                        ReturnMoneyMovementDAO::getInstance()->iinsert($columns_mon);
                                    }
                                }
                                
                                $estimated_refund = isset($det['detail']['refundInfo']['estimatedRefundDetail']['itemizedRefundDetail']) ? $det['detail']['refundInfo']['estimatedRefundDetail']['itemizedRefundDetail'] : array();
                                EstimatedRefundDAO::getInstance()->idelete($conditions, $params);
                                foreach ($estimated_refund as $refund) {
                                    $columns_est = array(
                                        'return_id' => $return_id,
                                        'refundFeeType' => isset($refund['refundFeeType']) ? $refund['refundFeeType'] : '',
                                        'estimatedAmount' => isset($refund['estimatedAmount']['value']) ? $refund['estimatedAmount']['value'] : 0.00,
                                        'currencyId' => isset($refund['estimatedAmount']['currencyId']) ? $refund['estimatedAmount']['currencyId'] : '',
                                        'overwritableBySeller' => boolConvert::toInt01(isset($refund['overwritableBySeller']) ? $refund['overwritableBySeller'] : false),
                                        'amountEditable' => boolConvert::toInt01(isset($refund['amountEditable']) ? $refund['amountEditable'] : false),
                                        'restockingFeePercentage' => isset($refund['restockingFeePercentage']) ? $refund['restockingFeePercentage'] : 0,
                                        'create_time' => time()
                                    );
                                    // estimated_refund 表数据插入
                                    EstimatedRefundDAO::getInstance()->iinsert($columns_est);
                                }
                                $ship_cost = isset($det['detail']['returnShipmentInfo']['shippingLabelCost']['itemizedReturnShippingCost']) ? $det['detail']['returnShipmentInfo']['shippingLabelCost']['itemizedReturnShippingCost'] : array();
                                ItemizedShipCostDAO::getInstance()->idelete($conditions, $params);
                                foreach ($ship_cost as $cost) {
                                    $columns_cost = array(
                                        'return_id' => $return_id,
                                        'returnShippingCostType' => isset($cost['returnShippingCostType']) ? $cost['returnShippingCostType'] : '',
                                        'amount' => isset($cost['amount']['value']) ? $cost['amount']['value'] : 0.00,
                                        'currencyId' => isset($cost['amount']['currencyId']) ? $cost['amount']['currencyId'] : '',
                                        'create_time' => time()
                                    );
                                    // estimated_refund 表数据插入
                                    ItemizedShipCostDAO::getInstance()->iinsert($columns_cost);
                                }
                                $all_shipment_tracking = isset($det['detail']['returnShipmentInfo']['allShipmentTrackings']) ? $det['detail']['returnShipmentInfo']['allShipmentTrackings'] : array();
                                AllShipmentTrackingDAO::getInstance()->idelete($conditions, $params);
                                foreach ($all_shipment_tracking as $tracking) {
                                    $columns_tracking = array(
                                        'return_id' => $return_id,
                                        'shippingMethod' => isset($tracking['shippingMethod']) ? $tracking['shippingMethod'] : '',
                                        'shippedBy' => isset($tracking['shippedBy']) ? $tracking['shippedBy'] : '',
                                        'carrierId' => isset($tracking['carrierId']) ? $tracking['carrierId'] : '',
                                        'carrierEnum' => isset($tracking['carrierEnum']) ? $tracking['carrierEnum'] : '',
                                        'carrierName' => isset($tracking['carrierName']) ? $tracking['carrierName'] : '',
                                        'carrierUsed' => isset($tracking['carrierUsed']) ? $tracking['carrierUsed'] : '',
                                        'deliveryStatus' => isset($tracking['deliveryStatus']) ? $tracking['deliveryStatus'] : '',
                                        'toShip_name' => isset($tracking['toShippingAddress']['name']) ? $tracking['toShippingAddress']['name'] : '',
                                        'toShip_street1' => isset($tracking['toShippingAddress']['street1']) ? $tracking['toShippingAddress']['street1'] : '',
                                        'toShip_street2' => isset($tracking['toShippingAddress']['street2']) ? $tracking['toShippingAddress']['street2'] : '',
                                        'toShip_city' => isset($tracking['toShippingAddress']['city']) ? $tracking['toShippingAddress']['city'] : '',
                                        'toShip_county' => isset($tracking['toShippingAddress']['county']) ? $tracking['toShippingAddress']['county'] : '',
                                        'toShip_stateOrProvince' => isset($tracking['toShippingAddress']['stateOrProvince']) ? $tracking['toShippingAddress']['stateOrProvince'] : '',
                                        'toShip_country' => isset($tracking['toShippingAddress']['country']) ? $tracking['toShippingAddress']['country'] : '',
                                        'toShip_postalCode' => isset($tracking['toShippingAddress']['postalCode']) ? $tracking['toShippingAddress']['postalCode'] : '',
                                        'toShip_any' => isset($tracking['toShippingAddress']['any']) ? $tracking['toShippingAddress']['any'] : '',
                                        'fromShip_name' => isset($tracking['fromShippingAddress']['name']) ? $tracking['fromShippingAddress']['name'] : '',
                                        'fromShip_street1' => isset($tracking['fromShippingAddress']['street1']) ? $tracking['fromShippingAddress']['street1'] : '',
                                        'fromShip_street2' => isset($tracking['fromShippingAddress']['street2']) ? $tracking['fromShippingAddress']['street2'] : '',
                                        'fromShip_city' => isset($tracking['fromShippingAddress']['city']) ? $tracking['fromShippingAddress']['city'] : '',
                                        'fromShip_county' => isset($tracking['fromShippingAddress']['county']) ? $tracking['fromShippingAddress']['county'] : '',
                                        'fromShip_stateOrProvince' => isset($tracking['fromShippingAddress']['stateOrProvince']) ? $tracking['fromShippingAddress']['stateOrProvince'] : '',
                                        'fromShip_country' => isset($tracking['fromShippingAddress']['country']) ? $tracking['fromShippingAddress']['country'] : '',
                                        'fromShip_postalCode' => isset($tracking['fromShippingAddress']['postalCode']) ? $tracking['fromShippingAddress']['postalCode'] : '',
                                        'fromShip_any' => isset($tracking['fromShippingAddress']['any']) ? $tracking['fromShippingAddress']['any'] : '',
                                        'markAsReceived' => boolConvert::toInt01(isset($tracking['markAsReceived']) ? $tracking['markAsReceived'] : false),
                                        'active' => boolConvert::toInt01(isset($tracking['active']) ? $tracking['active'] : false),
                                        'create_time' => time()
                                    );
                                    // all_shipment_trackings 表数据插入
                                    AllShipmentTrackingDAO::getInstance()->iinsert($columns_tracking);
                                }
                            } else {
                                file_put_contents('parseReturnDetailErr.log', $returnId_id . "\n", FILE_APPEND);
                                file_put_contents('parseReturnDetailErr.log', $value['text_json']['ReturnDetail'][$returnId_id] . "\n", FILE_APPEND);
                            }
                        }
                        if (isset($value['text_json']['ActivityOptions']) && is_array($value['text_json']['ActivityOptions']) && isset($value['text_json']['ActivityOptions'][$returnId_id])) {
                            $activity = ReturnDownModel::model()->parseNamespaceXml($value['text_json']['ActivityOptions'][$returnId_id]);
                            $dat = phpQuery::newDocumentXML($activity);
                            phpQuery::selectDocument($dat);
                            ReturnActivityDAO::getInstance()->begintransaction();
                            try {
                                $return_act = pq('ns1_activityOptions');
                                $act_length = $return_act->length();
                                if ($act_length) {
                                    ReturnActivityDAO::getInstance()->idelete($conditions, $params);
                                    for ($e = 0; $e < $act_length; $e ++) {
                                        $columns_acti = array(
                                            'return_id' => $return_id,
                                            'activityOptions' => $return_act->eq($e)->html(),
                                            'create_time' => time()
                                        );
                                        // return_activity_option表数据插入
                                        ReturnActivityDAO::getInstance()->iinsert($columns_acti);
                                    }
                                }
                                ReturnActivityDAO::getInstance()->commit();
                            } catch (Exception $e) {
                                iMongo::getInstance()->setCollection('ReturnActivityOptionsParseErr')->insert(array(
                                    'getCode' => $e->getCode(),
                                    'getFile' => $e->getFile(),
                                    'getLine' => $e->getLine(),
                                    'getMessage' => $e->getMessage(),
                                    'time' => time()
                                ));
                                ReturnActivityDAO::getInstance()->rollback();
                            }
                        }
                        // 图片下载
                        if (isset($value['text_json']['FileData']) && is_array($value['text_json']['FileData']) && isset($value['text_json']['FileData'][$returnId_id])) {
                            // $return_docs = json_decode($value['text_json']['FileData'][$returnId_id], true);
                            $return_docs = json_decode(FileLog::getInstance()->read(EnumOther::LOG_DIR_RETURN_TEMP_FILE_DATA . $value['text_json']['FileData'][$returnId_id], md5($returnId_id)), true);
                            ReturnDocsDAO::getInstance()->begintransaction();
                            ReturnDocsDAO::getInstance()->idelete($conditions, $params);
                            if (isset($return_docs['files'])) {
                                foreach ($return_docs['files'] as $docs) {
                                    $columns_docs = array(
                                        'return_id' => $return_id,
                                        'docId' => isset($docs['fileId']) ? $docs['fileId'] : '',
                                        'AccountID' => $value['AccountID'],
                                        'usageType' => isset($docs['filePurpose']) ? $docs['filePurpose'] : '',
                                        'docStatus' => isset($docs['fileStatus']) ? $docs['fileStatus'] : '',
                                        'author' => isset($docs['submitter']) ? $docs['submitter'] : '',
                                        'docFormat' => isset($docs['fileFormat']) ? $docs['fileFormat'] : '',
                                        'creationDate' => isset($docs['creationDate']['value']) ? strtotime($docs['creationDate']['value']) : 0,
                                        'create_time' => time()
                                    );
                                    if (! empty($docs['fileData'])) {
                                        $path = BASE_PATH . '/' . Yii::app()->params['picture_url'];
                                        $newPath = $path . gmdate('Y', $columns_docs['creationDate']) . '/';
                                        file_exists($newPath) ? null : mkdir($newPath);
                                        $newPath .= gmdate('m', $columns_docs['creationDate']) . '/';
                                        file_exists($newPath) ? null : mkdir($newPath);
                                        $newPath .= $value['AccountID'] . '/';
                                        file_exists($newPath) ? null : mkdir($newPath);
                                        $newPath .= $returnId_id . '/';
                                        file_exists($newPath) ? null : mkdir($newPath);
                                        $resizePath = $newPath . EnumOther::LOG_DIR_RETURN_RESIZE_FILE_DATA . '/';
                                        $newPath .= EnumOther::LOG_DIR_RETURN_FILE_DATA . '/';
                                        file_exists($newPath) ? null : mkdir($newPath);
                                        file_exists($resizePath) ? null : mkdir($resizePath);
                                        $newPath .= $columns_docs['docId'] . '.' . $columns_docs['docFormat'];
                                        $resizePath .= $columns_docs['docId'] . '.' . $columns_docs['docFormat'];
                                        file_put_contents($newPath, base64_decode($docs['fileData']));
                                        file_put_contents($resizePath, base64_decode($docs['resizedFileData']));
                                        // $columns_docs['path'] = $newPath;
                                        // $columns_docs['resizePath'] = $resizePath;
                                        $conditions_docs = 'docId=:docId';
                                        $params_docs = array(
                                            ':docId' => isset($docs['docId']) ? $docs['docId'] : ''
                                        );
                                        // return_docs 表数据插入
                                        ReturnDocsDAO::getInstance()->iinsert($columns_docs);
                                    }
                                }
                            }
                            ReturnDocsDAO::getInstance()->commit();
                            FileLog::getInstance()->delete(EnumOther::LOG_DIR_RETURN_TEMP_FILE_DATA . $value['text_json']['FileData'][$returnId_id], md5($returnId_id));
                        }
                    }
                } else {
                    iMongo::getInstance()->setCollection('parseReturnErrUnserialize')->insert(array(
                        'text_json' => $value['text_json'],
                        'getCode' => $e->getCode(),
                        'getFile' => $e->getFile(),
                        'getLine' => $e->getLine(),
                        'getMessage' => $e->getMessage(),
                        'time' => time()
                    ));
                }
                $dids[] = $value['down_id'];
            }
            unset($value);
        }
        return $dids;
    }

    /**
     * @desc 获取return 状态
     * @param int $sellerId            
     * @author liaojianwen
     * @date 2015-07-02
     * @return multitype:
     */
    public function getReturnState($sellerId)
    {
        $result = ReturnDAO::getInstance()->getReturnState($sellerId);
        if (! empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'no data');
        }
    }

    /**
     * @desc 获取用户关联的所有return
     * @param string $BuyerUserID            
     * @author liaojianwen
     * @date 2015-09-23
     * @return mixed
     */
    public function getReturnListByUserId($BuyerUserID)
    {
        $columns = array(
            's.nick_name',
            'd.S_buyerLoginName',
            'd.D_iD_itemId',
            'd.S_CI_creationDate',
            'd.S_CI_reason',
            'd.S_state',
            'd.S_status',
            'r.returnId_id',
            'r.return_request_id'
        );
        
        $conditions = "d.S_buyerLoginName = :userId";
        $params = array(
            ':userId' => $BuyerUserID
        );
        $joinArray = array(
            array(
                ReturnDAO::getInstance()->igetproperty('tableName') . ' r',
                'r.return_request_id = d.return_id'
            ),
            array(
                ShopDAO::getInstance()->igetproperty('tableName') . ' s',
                's.shop_id = r.shop_id and s.is_delete=0'
            )
        );
        $result = ReturnDetailDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 'd');
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'no data');
        } else {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
        }
    }

    /**
     * @desc 获取return 图片个数
     * @param string $return_id            
     * @return mixed
     */
    public function getDocCount($return_id)
    {
        $columns = array(
            'count(return_docs_id) num'
        );
        $conditions = "return_id = :returnid";
        $params = array(
            ':returnid' => $return_id
        );
        $result = ReturnDocsDAO::getInstance()->iselect($columns, $conditions, $params);
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'no data');
        } else {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
        }
    }

    /**
     * @desc 通过return_id 获取return关联的图片
     * @param string $return_id            
     * @param string $sellerId            
     * @author liaojianwen
     * @date 2015-10-09
     * @return mixed
     */
    public function getPictureById($return_id, $sellerId)
    {
        if (empty($return_id) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'params error');
        }
        $conditions = 'd.return_id = :return_id and s.seller_id = :seller_id';
        
        $params = array(
            ':return_id' => $return_id,
            ':seller_id' => $sellerId
        );
        $columns = array(
            'r.returnId_id',
            'd.AccountID',
            'd.author',
            'd.creationDate',
            'd.docFormat',
            'd.docId'
        );
        $joinArray = array(
            array(
                ReturnDAO::getInstance()->getTableName() . ' r',
                'r.return_request_id= d.return_id'
            ),
            array(
                ShopDAO::getInstance()->getTableName() . ' s',
                'r.shop_id=s.shop_id'
            )
        );
        $result = ReturnDocsDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 'd');
        $path = Yii::app()->params['picture_url'];
        foreach ($result as &$value) {
            $newPath = $path . gmdate('Y', $value['creationDate']) . '/';
            $newPath .= gmdate('m', $value['creationDate']) . '/';
            $newPath .= $value['AccountID'] . '/';
            $newPath .= $value['returnId_id'] . '/';
            $resizePath = $newPath . EnumOther::LOG_DIR_RETURN_RESIZE_FILE_DATA . '/';
            $newPath .= EnumOther::LOG_DIR_RETURN_FILE_DATA . '/';
            $newPath .= $value['docId'] . '.' . $value['docFormat'];
            $resizePath .= $value['docId'] . '.' . $value['docFormat'];
            $value['path'] = $newPath;
            $value['resizePath'] = $resizePath;
        }
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'no data');
        } else {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
        }
    }

    /**
     * @desc 上传return关联的图片
     * @param string $return_id            
     * @param array $imgUrl            
     * @author liaojianwen
     * @date 2015-10-12
     * @return mixed
     */
    public function submitDocs($return_id, $imgUrl)
    {
        if (empty($return_id) || empty($imgUrl)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'params error');
        }
        $returnId_id = ReturnDAO::getInstance()->findByPk($return_id, array(
            'returnId_id'
        ));
        if (empty($returnId_id)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'data error');
        }
        $columns = 's.token,r.returnId_id';
        $conditions = 'r.return_request_id=:return_id';
        $params = array(
            ':return_id' => $return_id
        );
        $joinArray = array(
            array(
                ShopDAO::getInstance()->getTableName() . ' s',
                's.shop_id=r.shop_id'
            )
        );
        $token = ReturnDAO::getInstance()->iselect($columns, $conditions, $params, false, $joinArray, 'r', 'return_request_id DESC', 1);
        if (empty($token)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'token not find');
        }
        $result = ReturnDownModel::model()->submitFile($token['returnId_id'], $token['token'], $imgUrl);
        $res = json_decode($result, true);
        if ($res['ackValue'] === 'SUCCESS') {
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($res);
            echo "\n\n图片信息：\n";
            var_export($imgUrl);
            echo "return_id:\n";
            var_export($return_id);
            $text = ob_get_clean();
            $subject = "Request 上传图片成功通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::send(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
        } else {
            iMongo::getInstance()->setCollection('subMitDocsErr')->insert(array(
                'type' => 'err',
                'return_id' => $return_id,
                'imgUrl' => $imgUrl,
                'json' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            // ob_start();
            // echo "apiResult：\n";
            // var_export($res);
            // echo "\n\n图片信息：\n";
            // var_export($imgUrl);
            // echo "return_id:\n";
            // var_export($return_id);
            // $text = ob_get_clean();
            // $subject = "Request 上传图片失败通知 [Failure]\n";
            // $to = Yii::app()->params['logmails'];
            // SendMail::send(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'submit error');
        }
    }
}
