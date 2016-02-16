<?php

/**
 * @desc case处理类
 * @author YangLong
 * @date 2015-03-26
 */
class CaseModel extends BaseModel
{
    
    /**
     * @desc 覆盖父方法返回CaseModel对象
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-03-26
     * @return CaseModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * @desc 解析Cases数据至数据库
     * @author YangLong
     * @date 2015-03-30
     * @return boolean
     */
    public function parseCases()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $startTime = time();
        label1:
        
        if ((time() - $startTime) > 600) {
            return false;
        }
        
        $_casedatas = CaseDownModel::model()->getDownloadData();
        
        if (empty($_casedatas)) {
            sleep(10);
            goto label1;
        }
        
        foreach ($_casedatas as &$casedata) {
            // 解码
            $casedata['text_json'] = unserialize(base64_decode($casedata['text_json']));
            
            if (is_array($casedata['text_json']) && isset($casedata['text_json']['Cases'])) {
                $doc = phpQuery::newDocumentXML($casedata['text_json']['Cases']);
                phpQuery::selectDocument($doc);
                
                $casesArray = pq('cases>caseSummary');
                $length = $casesArray->length;
                for ($i = 0; $i < $length; $i ++) {
                    $case = $casesArray->eq($i);
                    
                    $columns = array(
                        'shop_id' => $casedata['shop_id'],
                        'caseId_id' => $case->find('>caseId>id')->html(),
                        'caseId_type' => $case->find('>caseId>type')->html(),
                        'user_userId' => $case->find('>user>userId')->html(),
                        'user_role' => $case->find('>user>role')->html(),
                        'otherParty_userId' => $case->find('>otherParty>userId')->html(),
                        'otherParty_role' => $case->find('>otherParty>role')->html(),
                        'i_itemId' => $case->find('>item>itemId')->html(),
                        'i_itemTitle' => $case->find('>item>itemTitle')->html(),
                        'i_transactionId' => $case->find('>item>transactionId')->html(),
                        'caseQuantity' => $case->find('>caseQuantity')->html(),
                        'caseAmount' => $case->find('>caseAmount')->html(),
                        'currencyId' => $case->find('>caseAmount')->attr('currencyId'),
                        'creationDate' => strtotime($case->find('>creationDate')->html()),
                        'lastModifiedDate' => strtotime($case->find('>lastModifiedDate')->html()),
                        'respondByDate' => strtotime($case->find('>respondByDate')->html()),
                        'create_time' => time()
                    );
                    
                    $_status = array(
                        's_cancelTransactionStatus' => 'cancelTransactionStatus',
                        's_EBPINRStatus' => 'EBPINRStatus',
                        's_EBPSNADStatus' => 'EBPSNADStatus',
                        's_INRStatus' => 'INRStatus',
                        's_PaypalINRStatus' => 'PaypalINRStatus',
                        's_PaypalSNADStatus' => 'PaypalSNADStatus',
                        's_returnStatus' => 'returnStatus',
                        's_SNADStatus' => 'SNADStatus',
                        's_UPIStatus' => 'UPIStatus'
                    );
                    
                    // 有意思
                    foreach ($_status as $k => $v) {
                        if ($case->find(">status>{$v}")->html() !== false) {
                            $columns[$k] = $case->find(">status>{$v}")->html();
                        } else {
                            $columns[$k] = '';
                        }
                    }
                    
                    $conditions = 'caseId_id=:caseId_id';
                    $params = array(
                        ':caseId_id' => $columns['caseId_id']
                    );
                    foreach ($columns as $_tempkey => $_tempvalue) {
                        if ($_tempvalue === false || $_tempvalue === null) {
                            unset($columns[$_tempkey]);
                        }
                    }
                    $case_id = CaseDAO::getInstance()->ireplaceinto($columns, $conditions, $params, true);
                    
                    if (is_array($case_id)) {
                        // 异常发邮件
                        $subject = "严重错误：Case数据重复";
                        ob_start();
                        var_dump($case_id);
                        var_dump($params);
                        var_dump($conditions);
                        var_dump($columns);
                        $text = ob_get_clean();
                        $to = Yii::app()->params['logmails'];
                        SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
                        
                        $case_id = array_shift($case_id);
                        $case_id = array_shift($case_id);
                    }
                    
                    // 写用户表
                    $columns = array(
                        'shop_id' => $casedata['shop_id'],
                        'UserID' => $case->find('>otherParty>userId')->html(),
                        'RItemId' => $case->find('>item>itemId')->html(),
                        'getinterval' => 0
                    );
                    $conditions = 'UserID=:UserID';
                    $params = array(
                        ':UserID' => $case->find('>otherParty>userId')->html()
                    );
                    
                    foreach ($columns as $_tempkey => $_tempvalue) {
                        if ($_tempvalue === false || $_tempvalue === null) {
                            unset($columns[$_tempkey]);
                        }
                    }
                    
                    $_upk = EbayUserInfoDAO::getInstance()->ireplaceinto($columns, $conditions, $params, true);
                    
                    if (is_array($_upk)) {
                        // 异常发邮件
                        $subject = "严重错误：ebay_user_info 数据重复";
                        ob_start();
                        var_dump($case_id);
                        var_dump($params);
                        var_dump($conditions);
                        var_dump($columns);
                        $text = ob_get_clean();
                        $to = Yii::app()->params['logmails'];
                        SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
                        
                        $_upk = array_shift($_upk);
                        $_upk = array_shift($_upk);
                    }
                    
                    // 将用户关联到某哦店铺
                    $columns = array(
                        'ebay_user_info_id' => $_upk,
                        'shop_id' => $casedata['shop_id']
                    );
                    $conditions = 'ebay_user_info_id=:ebay_user_info_id and shop_id=:shop_id';
                    $params = array(
                        ':ebay_user_info_id' => $_upk,
                        ':shop_id' => $casedata['shop_id']
                    );
                    EbayUserShopsDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                    
                    // 详情分发
                    if (isset($casedata['text_json']['CaseDetail']) && is_array($casedata['text_json']['CaseDetail']) && isset($casedata['text_json']['CaseDetail'][$case->find('>caseId>id')->html()])) {
                        try {
                            $ddoc = phpQuery::newDocumentXML($casedata['text_json']['CaseDetail'][$case->find('>caseId>id')->html()], null, true);
                            phpQuery::selectDocument($ddoc);
                        } catch (Exception $e) {
                            // 异常发邮件
                            $subject = "严重错误：CaseDetail newDocumentXML 致命错误";
                            ob_start();
                            echo "\n-----------------------------------------------------------\n";
                            echo "error msg:" . $e->getMessage() . "\n";
                            echo 'caseId:' . $case->find('>caseId>id')->html() . "\n";
                            echo "xml:\n" . $casedata['text_json']['CaseDetail'][$case->find('>caseId>id')->html()];
                            echo "\n-----------------------------------------------------------\n";
                            $text = ob_get_clean();
                            $to = Yii::app()->params['logmails'];
                            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
                            continue;
                        }
                        
                        if (pq('ack')->html() == 'Success' && (pq('caseSummary>caseId>type')->html() == 'EBP_INR' || pq('caseSummary>caseId>type')->html() == 'EBP_SNAD')) {
                            
                            $columns = array(
                                'i_globalId' => pq('caseSummary>item>globalId')->html(),
                                'i_transactionDate' => strtotime(pq('caseSummary>item>transactionDate')->html()),
                                'i_transactionPrice' => pq('caseSummary>item>transactionPrice')->html()
                            );
                            $conditions = 'case_id=:case_id';
                            $params = array(
                                ':case_id' => $case_id
                            );
                            foreach ($columns as $k => $val) {
                                if ($val === null || $val === false || $val === 'Invalid Request') {
                                    unset($columns[$k]);
                                }
                            }
                            // case表 item信息更新
                            CaseDAO::getInstance()->iupdate($columns, $conditions, $params);
                            
                            // Appeals
                            $appeals = pq('caseDetail>appeal');
                            for ($j = 0; $j < $appeals->length; $j ++) {
                                $appeal = $appeals->eq($j);
                                $columns = array(
                                    'case_id' => $case_id,
                                    'appeal_id' => $appeal->attr('id'),
                                    'decision' => $appeal->find('>decision')->html(),
                                    'code' => $appeal->find('>decisionReasonDetail>code')->html(),
                                    'content' => $appeal->find('>decisionReasonDetail>content')->html(),
                                    'description' => $appeal->find('>decisionReasonDetail>description')->html()
                                );
                                
                                foreach ($columns as $k => $val) {
                                    if ($val === false || $val === null) {
                                        unset($columns[$k]);
                                    }
                                }
                                
                                $conditions = 'case_id=:case_id and appeal_id=:appeal_id';
                                $params = array(
                                    ':case_id' => $case_id,
                                    ':appeal_id' => $appeal->attr('id')
                                );
                                
                                CaseAppealDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                            }
                            
                            // TODO 将列表页和详情页合并
                            $columns = array(
                                'case_id' => $case_id,
                                'agreedRefundAmount' => $ddoc['caseDetail>agreedRefundAmount']->html(),
                                'bRS_carrierUsed' => $ddoc['caseDetail>buyerReturnShipment>carrierUsed']->html(),
                                'bRS_deliveryDate' => strtotime($ddoc['caseDetail>buyerReturnShipment>deliveryDate']->html()),
                                'bRS_deliveryStatus' => $ddoc['caseDetail>buyerReturnShipment>deliveryStatus']->html(),
                                'bRS_addr_city' => $ddoc['caseDetail>buyerReturnShipment>shippingAddress>city']->html(),
                                'bRS_addr_country' => $ddoc['caseDetail>buyerReturnShipment>shippingAddress>country']->html(),
                                'bRS_addr_name' => $ddoc['caseDetail>buyerReturnShipment>shippingAddress>name']->html(),
                                'bRS_addr_postalCode' => $ddoc['caseDetail>buyerReturnShipment>shippingAddress>postalCode']->html(),
                                'bRS_addr_stateOrProvince' => $ddoc['caseDetail>buyerReturnShipment>shippingAddress>stateOrProvince']->html(),
                                'bRS_addr_street1' => $ddoc['caseDetail>buyerReturnShipment>shippingAddress>street1']->html(),
                                'bRS_addr_street2' => $ddoc['caseDetail>buyerReturnShipment>shippingAddress>street2']->html(),
                                'bRS_shippingCost' => $ddoc['caseDetail>buyerReturnShipment>shippingCost']->html(),
                                'bRS_trackingNumber' => $ddoc['caseDetail>buyerReturnShipment>trackingNumber']->html(),
                                'decision' => $ddoc['caseDetail>decision']->html(),
                                'decisionDate' => strtotime($ddoc['caseDetail>decisionDate']->html()),
                                'decisionReason' => $ddoc['caseDetail>decisionReason']->html(),
                                'dRD_code' => $ddoc['caseDetail>decisionReasonDetail>code']->html(),
                                'dRD_content' => $ddoc['caseDetail>decisionReasonDetail>content']->html(),
                                'dRD_description' => $ddoc['caseDetail>decisionReasonDetail>description']->html(),
                                'detailStatus' => $ddoc['caseDetail>detailStatus']->html(),
                                'dSI_code' => $ddoc['caseDetail>detailStatusInfo>code']->html(),
                                'dSI_content' => $ddoc['caseDetail>detailStatusInfo>content']->html(),
                                'dSI_description' => $ddoc['caseDetail>detailStatusInfo>description']->html(),
                                'FVFCredited' => boolConvert::toInt01($ddoc['caseDetail>FVFCredited']->html()),
                                'globalId' => $ddoc['caseDetail>globalId']->html(),
                                'initialBuyerExpectation' => $ddoc['caseDetail>initialBuyerExpectation']->html(),
                                'iBED_code' => $ddoc['caseDetail>initialBuyerExpectationDetail>code']->html(),
                                'iBED_content' => $ddoc['caseDetail>initialBuyerExpectationDetail>content']->html(),
                                'iBED_description' => $ddoc['caseDetail>initialBuyerExpectationDetail>description']->html(),
                                'notCountedInBuyerProtectionCases' => boolConvert::toInt01($ddoc['caseDetail>notCountedInBuyerProtectionCases']->html()),
                                'openReason' => $ddoc['caseDetail>openReason']->html(),
                                'balanceDue' => $ddoc['caseDetail>paymentDetail>balanceDue']->html(),
                                'returnMerchandiseAuthorization' => $ddoc['caseDetail>returnMerchandiseAuthorization']->html(),
                                'sS_carrierUsed' => $ddoc['caseDetail>sellerShipment>carrierUsed']->html(),
                                'sS_deliveryDate' => strtotime($ddoc['caseDetail>sellerShipment>deliveryDate']->html()),
                                'sS_deliveryStatus' => $ddoc['caseDetail>sellerShipment>deliveryStatus']->html(),
                                'sS_addr_city' => $ddoc['caseDetail>sellerShipment>shippingAddress>city']->html(),
                                'sS_addr_country' => $ddoc['caseDetail>sellerShipment>shippingAddress>country']->html(),
                                'sS_addr_name' => $ddoc['caseDetail>sellerShipment>shippingAddress>name']->html(),
                                'sS_addr_postalCode' => $ddoc['caseDetail>sellerShipment>shippingAddress>postalCode']->html(),
                                'sS_addr_stateOrProvince' => $ddoc['caseDetail>sellerShipment>shippingAddress>stateOrProvince']->html(),
                                'sS_addr_street1' => $ddoc['caseDetail>sellerShipment>shippingAddress>street1']->html(),
                                'sS_addr_street2' => $ddoc['caseDetail>sellerShipment>shippingAddress>street2']->html(),
                                'sS_shippingCost' => $ddoc['caseDetail>sellerShipment>shippingCost']->html(),
                                'sS_trackingNumber' => $ddoc['caseDetail>sellerShipment>trackingNumber']->html(),
                                'create_time' => time()
                            );
                            
                            foreach ($columns as $k => $val) {
                                if ($val === null || $val === false || $val === 'Invalid Request') {
                                    unset($columns[$k]);
                                }
                            }
                            
                            $conditions = 'case_id=:case_id';
                            $params = array(
                                ':case_id' => $case_id
                            );
                            // case detail
                            CaseDetailDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                            
                            for ($j = 0; $j < $ddoc['caseDetail>caseDocumentInfo']->length; $j ++) {
                                // 文件信息插入
                                $columns = array(
                                    'case_id' => $case_id,
                                    'number' => $j + 1,
                                    'name' => $ddoc["caseDetail>caseDocumentInfo:eq({$j})>name"]->html(),
                                    'type' => $ddoc["caseDetail>caseDocumentInfo:eq({$j})>type"]->html(),
                                    'uploadDate' => strtotime($ddoc["caseDetail>caseDocumentInfo:eq({$j})>uploadDate"]->html())
                                );
                                
                                foreach ($columns as $k => $val) {
                                    if ($val === false || $val === null) {
                                        unset($columns[$k]);
                                    }
                                }
                                
                                $conditions = 'case_id=:case_id and number=:number';
                                $params = array(
                                    ':case_id' => $case_id,
                                    ':number' => $j + 1
                                );
                                CaseDocumentInfoDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                            }
                            
                            // payment info
                            for ($j = 0; $j < $ddoc['caseDetail>paymentDetail>moneyMovement']->length; $j ++) {
                                $columns = array(
                                    'case_id' => $case_id,
                                    'id' => $ddoc["paymentDetail>moneyMovement:eq({$j})"]->attr('id'),
                                    'parentId' => $ddoc["paymentDetail>moneyMovement:eq({$j})"]->attr('parentId'),
                                    'amount' => (float) $ddoc["paymentDetail>moneyMovement:eq({$j})>amount"]->html(),
                                    'fromParty_role' => $ddoc["paymentDetail>moneyMovement:eq({$j})>fromParty>role"]->html(),
                                    'fromParty_userId' => $ddoc["paymentDetail>moneyMovement:eq({$j})>fromParty>userId"]->html(),
                                    'paymentMethod' => $ddoc["paymentDetail>moneyMovement:eq({$j})>paymentMethod"]->html(),
                                    'paypalTransactionId' => $ddoc["paymentDetail>moneyMovement:eq({$j})>paypalTransactionId"]->html(),
                                    'status' => $ddoc["paymentDetail>moneyMovement:eq({$j})>status"]->html(),
                                    'toParty_role' => $ddoc["paymentDetail>moneyMovement:eq({$j})>toParty>role"]->html(),
                                    'toParty_userId' => $ddoc["paymentDetail>moneyMovement:eq({$j})>toParty>userId"]->html(),
                                    'transactionDate' => (int) strtotime($ddoc["paymentDetail>moneyMovement:eq({$j})>transactionDate"]->html()),
                                    'type' => $ddoc["paymentDetail>moneyMovement:eq({$j})>type"]->html()
                                );
                                
                                foreach ($columns as $k => $val) {
                                    if ($val === false || $val === null) {
                                        unset($columns[$k]);
                                    }
                                }
                                
                                $conditions = 'case_id=:case_id and id=:id';
                                $params = array(
                                    ':case_id' => $case_id,
                                    ':id' => $ddoc["paymentDetail>moneyMovement:eq({$j})"]->attr('id')
                                );
                                CaseMoneyMovementDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                            }
                            
                            // messages history
                            for ($j = $ddoc['caseDetail>responseHistory']->length - 1; $j >= 0; $j --) {
                                $columns = array(
                                    'case_id' => $case_id,
                                    'number' => $j + 1,
                                    'activity' => $ddoc["caseDetail>responseHistory:eq({$j})>activity"]->html(),
                                    'activityDetial_code' => $ddoc["caseDetail>responseHistory:eq({$j})>activityDetail>code"]->html(),
                                    'activityDetial_content' => $ddoc["caseDetail>responseHistory:eq({$j})>activityDetail>content"]->html(),
                                    'activityDetial_description' => $ddoc["caseDetail>responseHistory:eq({$j})>activityDetail>description"]->html(),
                                    'attr_appealRef' => $ddoc["caseDetail>responseHistory:eq({$j})>attributes>appealRef"]->html(),
                                    'attr_moneyMovementRef' => $ddoc["caseDetail>responseHistory:eq({$j})>attributes>moneyMovementRef"]->html(),
                                    'attr_onholdReason' => $ddoc["caseDetail>responseHistory:eq({$j})>attributes>onholdReason"]->html(),
                                    'attr_oRD_code' => $ddoc["caseDetail>responseHistory:eq({$j})>attributes>onholdReasonDetail>code"]->html(),
                                    'attr_oRD_content' => $ddoc["caseDetail>responseHistory:eq({$j})>attributes>onholdReasonDetail>content"]->html(),
                                    'attr_oRD_description' => $ddoc["caseDetail>responseHistory:eq({$j})>attributes>onholdReasonDetail>description"]->html(),
                                    'author_role' => $ddoc["caseDetail>responseHistory:eq({$j})>author>role"]->html(),
                                    'author_userId' => $ddoc["caseDetail>responseHistory:eq({$j})>author>userId"]->html(),
                                    'creationDate' => (int) strtotime($ddoc["caseDetail>responseHistory:eq({$j})>creationDate"]->html()),
                                    'note' => $ddoc["caseDetail>responseHistory:eq({$j})>note"]->html()
                                );
                                
                                foreach ($columns as $k => $val) {
                                    if ($val === false || $val === null) {
                                        unset($columns[$k]);
                                    }
                                }
                                
                                $conditions = 'case_id=:case_id and number=:number';
                                $params = array(
                                    ':case_id' => $case_id,
                                    ':number' => $j + 1
                                );
                                CaseResponseHistoryDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                                
                                // 发送邮件通知
                                ob_start();
                                echo 'length:';
                                echo $ddoc['caseDetail>responseHistory']->length;
                                echo "\n";
                                echo '$columns:';
                                var_dump($columns);
                                echo "\n";
                                echo '$$conditions:';
                                var_dump($conditions);
                                echo "\n";
                                echo '$$params:';
                                var_dump($params);
                                echo "\n";
                                $text = ob_get_clean();
                                $subject = "responseHistory j out .";
                                $to = Yii::app()->params['logmails'];
                                SendMail::send(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
                            }
                        } elseif ($ddoc['Ack']->html() == 'Success' && $ddoc["Dispute>DisputeID"]->html() !== false) {
                            $columns = array(
                                'case_id' => $case_id,
                                'BuyerUserID' => $ddoc["Dispute>BuyerUserID"]->html(),
                                'DisputeCreatedTime' => strtotime($ddoc["Dispute>DisputeCreatedTime"]->html()),
                                'DisputeCreditEligibility' => $ddoc["Dispute>DisputeCreditEligibility"]->html(),
                                'DisputeExplanation' => $ddoc["Dispute>DisputeExplanation"]->html(),
                                'DisputeID' => $ddoc["Dispute>DisputeID"]->html(),
                                'DisputeModifiedTime' => strtotime($ddoc["Dispute>DisputeModifiedTime"]->html()),
                                'DisputeReason' => $ddoc["Dispute>DisputeReason"]->html(),
                                'DisputeRecordType' => $ddoc["Dispute>DisputeRecordType"]->html(),
                                'DisputeState' => $ddoc["Dispute>DisputeState"]->html(),
                                'DisputeStatus' => $ddoc["Dispute>DisputeStatus"]->html(),
                                'Escalation' => boolConvert::toInt01($ddoc["Dispute>Escalation"]->html()),
                                'i_ItemID' => $ddoc["Dispute>Item>ItemID"]->html(),
                                'i_LD_EndTime' => strtotime($ddoc["Dispute>Item>ListingDetails>StartTime"]->html()),
                                'i_LD_StartTime' => strtotime($ddoc["Dispute>Item>ListingDetails>EndTime"]->html()),
                                'i_Quantity' => $ddoc["Dispute>Item>Quantity"]->html(),
                                'i_SS_CCP_currencyID' => $ddoc["Dispute>Item>SellingStatus>ConvertedCurrentPrice"]->attr('currencyID'),
                                'i_SS_ConvertedCurrentPrice' => (float) $ddoc["Dispute>Item>SellingStatus>ConvertedCurrentPrice"]->html(),
                                'i_SS_CP_currencyID' => $ddoc["Dispute>Item>SellingStatus>CurrentPrice"]->attr('currencyID'),
                                'i_SS_CurrentPrice' => $ddoc["Dispute>Item>SellingStatus>CurrentPrice"]->html(),
                                'i_Site' => $ddoc["Dispute>Item>Site"]->html(),
                                'i_Title' => $ddoc["Dispute>Item>Title"]->html(),
                                'OrderLineItemID' => $ddoc["Dispute>OrderLineItemID"]->html(),
                                'PurchaseProtection' => boolConvert::toInt01($ddoc["Dispute>PurchaseProtection"]->html()),
                                'SellerUserID' => $ddoc["Dispute>SellerUserID"]->html(),
                                'TransactionID' => $ddoc["Dispute>TransactionID"]->html(),
                                'create_time' => time()
                            );
                            
                            foreach ($columns as $k => $val) {
                                if ($val === null || $val === false || $val === 'Invalid Request') {
                                    unset($columns[$k]);
                                }
                            }
                            
                            $conditions = 'DisputeID=:DisputeID';
                            $params = array(
                                ':DisputeID' => $ddoc["Dispute>DisputeID"]->html()
                            );
                            // 纠纷详细信息插入
                            CaseDisputeDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                            
                            for ($j = 0; $j < $ddoc['DisputeMessage']->length; $j ++) {
                                $columns = array(
                                    'case_id' => $case_id,
                                    'MessageCreationTime' => strtotime($ddoc["DisputeMessage:eq({$j})>MessageCreationTime"]->html()),
                                    'MessageID' => (int) $ddoc["DisputeMessage:eq({$j})>MessageID"]->html(),
                                    'MessageSource' => $ddoc["DisputeMessage:eq({$j})>MessageSource"]->html(),
                                    'MessageText' => $ddoc["DisputeMessage:eq({$j})>MessageText"]->html(),
                                    'create_time' => time()
                                );
                                $conditions = 'case_id=:case_id and MessageID=:MessageID';
                                $params = array(
                                    ':case_id' => $case_id,
                                    ':MessageID' => $ddoc["DisputeMessage:eq({$j})>MessageID"]->html()
                                );
                                // 纠纷消息替换式插入
                                CaseDisputeMessageDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                            }
                            
                            $conditions = 'case_id=:case_id';
                            $params = array(
                                ':case_id' => $case_id
                            );
                            // 旧纠纷信息删除
                            CaseDisputeResolutionDAO::getInstance()->idelete($conditions, $params);
                            for ($j = 0; $j < $ddoc['DisputeResolution']->length; $j ++) {
                                $columns = array(
                                    'case_id' => $case_id,
                                    'DisputeResolutionReason' => $ddoc["DisputeResolution:eq({$j})>DisputeResolutionReason"]->html(),
                                    'DisputeResolutionRecordType' => $ddoc["DisputeResolution:eq({$j})>DisputeResolutionRecordType"]->html(),
                                    'ResolutionTime' => strtotime($ddoc["DisputeResolution:eq({$j})>ResolutionTime"]->html()),
                                    'create_time' => time()
                                );
                                
                                foreach ($columns as $k => $val) {
                                    if ($val === false || $val === null) {
                                        unset($columns[$k]);
                                    }
                                }
                                
                                // 纠纷解决信息插入
                                CaseDisputeResolutionDAO::getInstance()->iinsert($columns);
                            }
                        } else {
                            iMongo::getInstance()->setCollection('parseCaseErrOther')->insert(array(
                                'caseId_id' => $doc["cases>caseSummary:eq({$i})>caseId>id"]->html(),
                                'xml' => $casedata['text_json']['CaseDetail'][$case->find('>caseId>id')
                                    ->html()],
                                'time' => time()
                            ));
                        }
                        
                        unset($ddoc);
                    }
                    
                    // ActivityOptions分发
                    if (isset($casedata['text_json']['ActivityOptions']) && is_array($casedata['text_json']['ActivityOptions']) && isset($casedata['text_json']['ActivityOptions'][$doc["cases>caseSummary:eq({$i})>caseId>id"]->html()])) {
                        $apdoc = phpQuery::newDocumentXML($casedata['text_json']['ActivityOptions'][$doc["cases>caseSummary:eq({$i})>caseId>id"]->html()]);
                        phpQuery::selectDocument($apdoc);
                        $conditions = 'case_id=:case_id';
                        $params = array(
                            ':case_id' => $case_id
                        );
                        CaseActivityOptionsDAO::getInstance()->idelete($conditions, $params);
                        for ($j = 0; $j < $doc['activityOptions']->children()->length; $j ++) {
                            $columns = array(
                                'case_id' => $case_id,
                                'activityOption_type' => $doc['activityOptions']->children()->eq($j)->elements[0]->tagName
                            );
                            $columns['buyerPreference'] = $doc['activityOptions']->children()
                                ->eq($j)
                                ->find('>buyerPreference')
                                ->html();
                            $columns['buyerPreference'] = boolConvert::toInt01($columns['buyerPreference']);
                            $columns['customerSupportResponseTimeInHours'] = (int) $doc['activityOptions']->children()
                                ->eq($j)
                                ->find('>customerSupportResponseTimeInHours')
                                ->html();
                            $columns['daysToRefundBuyer'] = (int) $doc['activityOptions']->children()
                                ->eq($j)
                                ->find('>daysToRefundBuyer')
                                ->html();
                            $columns['carrierUsed'] = $doc['activityOptions']->children()
                                ->eq($j)
                                ->find('>carrierUsed')
                                ->html();
                            $columns['shippedDate'] = $doc['activityOptions']->children()
                                ->eq($j)
                                ->find('>shippedDate')
                                ->html();
                            $columns['trackingNumber'] = $doc['activityOptions']->children()
                                ->eq($j)
                                ->find('>trackingNumber')
                                ->html();
                            $columns['create_time'] = time();
                            
                            foreach ($columns as $k => $val) {
                                if ($val === false || $val === null) {
                                    unset($columns[$k]);
                                }
                            }
                            
                            CaseActivityOptionsDAO::getInstance()->iinsert($columns);
                        }
                    }
                }
            }
            CaseDownDAO::getInstance()->deleteByIds($casedata['down_id']);
        }
        if (! empty($casedata)) {
            // 此处睡眠可减小服务器负载
            // TODO
            usleep(100000);
            goto label1;
        }
        unset($casedata);
    }
    
    /**
     * @desc 获取Case列表
     * @param String  $Type      case类型
     * @param integer $page      页码
     * @param integer $pageSize  分页大小
     * @param string $status     查询条件状态
     * @param integer $itemId    查询条件itemNo
     * @param  stirng $cust      查询条件客户
     * @param string $query   判断是查询还是列表展示/query 为查询
     * @author lvjianfei liaojianwen
     * @date 2015-04-02
     * @return array 列表信息
     */
    public function getCaseList($Type, $page, $pageSize, $status, $itemId, $cust, $query)
    {
        switch ($Type) {
            case EnumOther::CASE_INR:
                $param = 'EBP_INR';
                break;
            case EnumOther::CASE_SNAD:
                $param = 'EBP_SNAD';
                break;
        }
        // $express = "caseId_type = :param";
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
        $res = CaseDAO::getInstance()->getCaseList($paramArr, $page, $pageSize, $status, $itemId, $cust, $query);
        if (empty($res['list'])) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '查询不到数据');
        }
        $result = $this->handleApiFormat(EnumOther::ACK_SUCCESS, $res, '');
        return $result;
    }
    
    /**
     * @desc 新增Case
     * @param int $shopId 店铺ID
     * @param string $DisputeReason 纠纷类型
     * @param string $DisputeExplanation 纠纷原因
     * @param string $ItemID IiemID
     * @param string $TransactionID TransactionID
     * @param string $OrderLineItemID OrderLineItemID
     * @author YangLong
     * @date 2015-04-22
     * @return boolean
     */
    public function addDispute($shopId, $DisputeReason, $DisputeExplanation, $ItemID, $TransactionID, $OrderLineItemID = '')
    {
        if (array_search($DisputeReason, DisputeExplanationCodeType2::$type) === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'DisputeReason Error.');
        }
        
        if ($DisputeReason == DisputeExplanationCodeType2::$type[0] && array_search($DisputeExplanation, DisputeExplanationCodeType2::$BuyerHasNotPaid) === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'UPI DisputeExplanation Error.');
        }
        
        if ($DisputeReason == DisputeExplanationCodeType2::$type[1] && array_search($DisputeExplanation, DisputeExplanationCodeType2::$TransactionMutuallyCanceled) === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'CANCELED DisputeExplanation Error.');
        }
        
        $token = ShopDAO::getInstance()->getValuesByPk($shopId, array(
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
                    'DisputeReason' => $DisputeReason,
                    'DisputeExplanation' => $DisputeExplanation,
                    'ItemID' => $ItemID,
                    'TransactionID' => $TransactionID,
                    'OrderLineItemID' => $OrderLineItemID,
                    'siteId' => $token['site_id']
                )),
                'token' => $token['token'],
                'create_time' => time()
            );
            $result = CaseUploadQueueDAO::getInstance()->iinsert($columns);
            
            if ($result === false) {
                CaseUploadQueueDAO::getInstance()->rollback();
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'insert database failure.');
            } else {
                CaseUploadQueueDAO::getInstance()->commit();
                return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
            }
        } catch (Exception $e) {
            CaseUploadQueueDAO::getInstance()->rollback();
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'insert database failure.');
        }
    }
    
    /**
     * @desc 获取用户的case列表
     * @author liaojianwen
     * @param string $BuyerUserID
     * @date 2015-09-22
     * @return boolean|Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function getCaseListByUserId($BuyerUserID)
    {
        if (empty($BuyerUserID)) {
            return false;
        }
        $columns = array(
            's.nick_name',
            'c.i_itemId',
            'd.openReason',
            'DisputeReason',
            'c.creationDate',
            'c.s_cancelTransactionStatus',
            'c.s_EBPINRStatus',
            'c.s_EBPSNADStatus',
            'c.s_INRStatus',
            's_UPIStatus',
            'c.case_id',
            'c.caseId_id',
            'c.caseId_type'
        );
        
        $conditions = "(user_userId = :userId or otherParty_userId = :userId) and openReason !='Unknown'";
        $params = array(
            ':userId' => $BuyerUserID
        );
        $joinarray = array(
            array(
                ShopDAO::getInstance()->igetproperty('tableName') . ' s',
                's.shop_id = c.shop_id'
            ),
            array(
                CaseDetailDAO::getInstance()->igetproperty('tableName') . ' d',
                'c.case_id = d.case_id',
                'left'=>true
            ),
            array(
                CaseDisputeDAO::getInstance()->igetproperty('tableName') . ' f',
                'c.case_id = f.case_id',
                'left'=>true
            )
        );
        $result = CaseDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinarray, 'c');
        foreach($result as &$value){
            switch ($value['caseId_type']) {
                case 'CANCEL_TRANSACTION':
                    $value['status'] = $value['s_cancelTransactionStatus'];
                    break;
                case 'UPI':
                    $value['status'] = $value['s_UPIStatus'];
                    break;
                case 'EBP_INR':
                    $value['status'] = $value['s_EBPINRStatus'];
                    break;
                case 'EBP_SNAD':
                    $value['status'] = $value['s_EBPSNADStatus'];
                    break;
            }
            
        }
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '');
        }
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
    }
}