<?php
/**
 * @desc 订单催款处理类
 * @author liaojianwen
 * @date 2015-10-30
 */
class InvoicesModel extends BaseModel
{
    private $compatabilityLevel; // eBay API version
    
    private $devID;
    
    private $appID;
    
    private $certID;
    
    private $serverUrl; // eBay 服务器地址
    
    private $userToken; // token
    
    private $siteToUseID; // site id
    
    
    /**
     * @desc 覆盖父方法返回InvoicesModel对象
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-10-30
     * @return InvoicesModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-03-27
     */
    public function __construct()
    {
        $this->compatabilityLevel = 911; // eBay API version
        if (Yii::app()->params['ebay_api_production']) {
            $this->devID = Yii::app()->params['devIDinfo']['devID'];
            $this->appID = Yii::app()->params['devIDinfo']['appID'];
            $this->certID = Yii::app()->params['devIDinfo']['certID'];
            // $this->serverUrl = 'https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
            // $paypalEmailAddress = 'PRODUCTION_PAYPAL_EMAIL_ADDRESS';
        } else {
            $this->devID = 'cfb73f1d-48f3-4bdf-aa79-07ed14b1f677';
            $this->appID = 'dfda6a3e-7727-43ee-b871-81c9937cb350';
            $this->certID = 'abc4cf49-6531-4555-b16b-bcee34b5aca3';
            // $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
            // $paypalEmailAddress = 'SANDBOX_PAYPAL_EMAIL_ADDRESS';
        }
    }
    
    
    /**
     * @desc 订单催款
     * @param string $token
     * @param array $serviceOptions 物流服务数组
     * @param string $text 
     * @param string $orderID
     * @param string $orderlineItemID
     * @param int $adjustAmount
     * @param string $currencyID
     * @param boolean $isSendMail
     * @author liaojianwen
     * @date 2015-10-30
     * @return mixed
     */
    public function sendInvoices($token,$serviceOptions,$text,$orderID='',$orderlineItemID='',$adjustAmount,$currencyID,$isSendMail=false,$siteid)
    {
        $callName = 'SendInvoice';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .='<SendInvoiceRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $requestXmlBody .= '<RequesterCredentials><eBayAuthToken>'.$token.'</eBayAuthToken></RequesterCredentials>';
        if(!empty($orderID)){
            $requestXmlBody .='<OrderID>'.$orderID.'</OrderID>';
        }elseif(!empty($orderlineItemID)){
            $requestXmlBody .='<OrderLineItemID>'.$orderlineItemID.'</OrderLineItemID>';
        }else{
            return false;
        }
        if(!empty($adjustAmount) && !empty($currencyID)){
            $requestXmlBody .='<AdjustmentAmount currencyID="'.$currencyID.'">'.$adjustAmount.'</AdjustmentAmount>';
        }
        if (!empty($text)){
            $requestXmlBody .='<CheckoutInstructions>'.$text.'</CheckoutInstructions>'; 
        }
        if($isSendMail){
            $requestXmlBody .='<EmailCopyToSeller>'.$isSendMail.'</EmailCopyToSeller>';
        }
        foreach ($serviceOptions as $service) {
            $requestXmlBody .= '<ShippingServiceOptions>
        <ShippingService>' . $service['serviceOption'] . '</ShippingService>
        <ShippingServiceCost currencyID="' . $service['currencyID'] . '">' . $service['serviceValue'] . '</ShippingServiceCost>
      </ShippingServiceOptions>';
        }
        $requestXmlBody .='</SendInvoiceRequest>';
        $session = new eBaySession($this->serverUrl);
        
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:945";
        $session->headers[] = "X-EBAY-API-DEV-NAME:{$this->devID}";
        $session->headers[] = "X-EBAY-API-APP-NAME:{$this->appID}";
        $session->headers[] = "X-EBAY-API-CERT-NAME:{$this->certID}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        $session->headers[] = "X-EBAY-API-SITEID:{$siteid}";
        
        $tryCount = 0;
        
        label1:
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('eBaySendInvoiceFailure')->insert(array(
            'requestXmlBody' => $requestXmlBody,
            'responseXml' => $responseXml,
            'time' => time(),
            'times' => 1
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('eBaySendInvoiceFailure')->insert(array(
            'requestXmlBody' => $requestXmlBody,
            'responseXml' => $responseXml,
            'time' => time(),
            'times' => 2
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (! XMLTool::IsXML($responseXml)) {
            iMongo::getInstance()->setCollection('eBaySendInvoiceBadXML')->insert(array(
            'requestXmlBody' => $requestXmlBody,
            'responseXml' => $responseXml,
            'tryCount' => $tryCount,
            'time' => time()
            ));
            if ($tryCount < 2) {
                $tryCount ++;
                goto label1;
            }
            return false;
        }
        
        if (stripos($responseXml, '<ErrorClassification>SystemError</ErrorClassification>')) {
            if ($tryCount < 3) {
                $tryCount ++;
                sleep(5);
                goto label1;
            }
        }
        
        iMongo::getInstance()->setCollection('eBaySendInvoice')->insert(array(
        'requestXmlBody' => $requestXmlBody,
        'responseXml' => $responseXml,
        'tryCount' => $tryCount,
        'time' => time()
        ));
        
        return $responseXml;
    }
    
    /**
     * @desc 获取订单信息列表
     * @param int $page
     * @param int $pageSize
     * @param string $cust
     * @param string $sellerId
     * @author liaojianwen
     * @date 2015-10-31
     * @return mixed
     */
    public function getUpaidOrderList($page,$pageSize,$cust,$sellerId)
    {
        if(empty($sellerId)){
           return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'sellerId can not be null.');
        }
        // 获取店铺信息
        $parr = array();
        $parr['seller_id'] = $sellerId;
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
        $result = EbayOrdersDAO::getInstance()->getUpaidOrderList($page,$pageSize,$cust,$shopId);
        if(empty($result['list'])){
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'no data found');
        }
        return  $this->handleApiFormat(EnumOther::ACK_SUCCESS,$result);
    }
    
    /**
     * @desc 获取客户地址
     * @param string $buyer_id
     * @author liaojianwen
     * @date 2015-10-31
     * @return mixed
     */
    public function getOrderAddr($buyer_id)
    {
        if (empty($buyer_id)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'BuyerUserID is missing');
        }
        $result = EbayUserAddressDAO::getInstance()->getOrderAddr($buyer_id);
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'shopping Address is missing');
        }
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
    }

    /**
     * @desc 获取订单明细
     * @param string $order_Id
     * @author liaojianwen
     * @date 2015-10-31
     */
    public function getOrderTransaction($order_Id)
    {
        if(empty($order_Id)){
            return $this->handleApiFormat(EnumOther::ACK_FAILURE,'','the orderID is missing');
        }
        $result = EbayOrdersDAO::getInstance()->getOrderTransaction($order_Id);
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'the transaction detail is missing');
        }
        $flag = 0; // 0 为没有选择物流服务，1为国内，2为国外物流服务
        foreach ($result as &$value) {
            $specifics = array();
            $service = array();
            $shippingService = array();
            $InternalShippingService = array();
            $doc = phpQuery::newDocumentXML($value['VariationSpecificsXML']);
            phpQuery::selectDocument($doc);
            $varition = pq('NameValueList ');
            $length = $varition->length;
            for ($i = 0; $i < $length; $i ++) {
                $name = $varition->eq($i)
                    ->find('Name')
                    ->html();
                $res = $varition->eq($i)
                    ->find('Value')
                    ->html();
                $specifics[$name] = $res;
            }
            $value['VariationSpecifics'] = $specifics;
            
            $ddoc = phpQuery::newDocumentXML($value['ShippingDetailsXML']);
            phpQuery::selectDocument($ddoc);
            $_option = pq('ShippingServiceOptions');
            $shipping_length = $_option->length;
            for ($j = 0; $j < $shipping_length; $j ++) {
                $service['ShippingService'] = $_option->eq($j)
                    ->find('ShippingService')
                    ->html();
                $service['ShippingServiceCost'] = $_option->eq($j)
                    ->find('ShippingServiceCost')
                    ->html();
                $service['ShippingServiceCurrencyID'] = $_option->eq($j)
                    ->find('ShippingServiceCost')
                    ->attr('currencyID');
                $service['ShippingServicePriority'] = $_option->eq($j)
                    ->find('ShippingServicePriority')
                    ->html();
                $service['ExpeditedService'] = $_option->eq($j)
                    ->find('ExpeditedService')
                    ->html();
                $description = EbayShippingServiceDetailsDAO::getInstance()->findByAttributes(array(
                    'ShippingService' => $service['ShippingService']
                ), array(
                    'Description'
                ));
                $service['description'] = $description['Description'];
                array_push($shippingService, $service);
                if ($service['ShippingService'] === $value['ShippingService']) {
                    $flag = 1;
                }
            }
            $value['ShippingServiceOptions'] = $shippingService;
            
            $I_option = pq('InternationalShippingServiceOption');
            $Int_length = $I_option->length;
            $service = array();
            for ($k = 0; $k < $Int_length; $k ++) {
                $service['ShippingService'] = $I_option->eq($k)
                    ->find('ShippingService')
                    ->html();
                $service['ShippingServiceCost'] = $I_option->eq($k)
                    ->find('ShippingServiceCost')
                    ->html();
                $service['ShippingServiceCurrencyID'] = $I_option->eq($k)
                    ->find('ShippingServiceCost')
                    ->attr('currencyID');
                $description = EbayShippingServiceDetailsDAO::getInstance()->findByAttributes(array(
                    'ShippingService' => $service['ShippingService']
                ), array(
                    'Description'
                ));
                $service['description'] = $description['Description'];
                array_push($InternalShippingService, $service);
                if ($service['ShippingService'] === $value['ShippingService']) {
                    $flag = 2;
                }
            }
            $value['options'] = $flag;
            $value['InternationalShippingServiceOption'] = $InternalShippingService;
        }
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
    }
    
    /**
     * @desc 获取订单物流服务
     * @param string $ebay_orders_id
     * @param string $sellerId
     * @author liaojianwen
     * @date 2015-11-2
     * @return mixed
     */
    public function getEbayShipService($site_id,$flag)
    {
        if (empty($site_id)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'order_id  can not be null');
        }
        $result = EbayShippingServiceDetailsDAO::getInstance()->getEbayShipService($site_id, $flag);
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'the shippingService detail is missing');
        }
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
    }

    /**
     * @desc给客户发催款信息
     * @param string $orderID            
     * @param array $serviceOptions            
     * @param string $text            
     * @param float $adjustAmount            
     * @param string $currencyID            
     * @param boolen $isSendMe            
     * @param string $sellerId            
     * @author liaojianwen
     * @date 2015-11-03
     */
    public function ebaySendInvoices($orderID, $serviceOptions, $text, $adjustAmount, $currencyID, $isSendMe, $sellerId, $ebay_orders_id)
    {
        if (empty($orderID) || empty($serviceOptions)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'orderId or shippingServiceOptions can not be null!');
        }
        $token = ShopDAO::getInstance()->getInvoicesToken($orderID, $sellerId);
        if (empty($token)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'token can not be found');
        }
        $result = $this->sendInvoices($token['token'], $serviceOptions, $text, $orderID, '', $adjustAmount, $currencyID, $isSendMe, $token['site_id']);
        if (stripos($result, '<Ack>Success</Ack>')) {
            $criteria = array(
                'ebay_orders_id' => $ebay_orders_id
            );
            $selections = 'send_count';
            $res = InvoicesCountDAO::getInstance()->findByAttributes($criteria, $selections);
            if (empty($res)) {
                $columns = array(
                    'ebay_orders_id' => $ebay_orders_id,
                    'send_count' => 1,
                    'last_send_time' => time(),
                    'create_time' => time()
                );
                InvoicesCountDAO::getInstance()->iinsert($columns);
            } else {
                $count = $res['send_count'];
                $count += 1;
                $params = array(
                    'send_count' => $count,
                    'last_send_time' => time()
                );
                InvoicesCountDAO::getInstance()->update($criteria, $params);
            }
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
        } else {
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n订单ID：\n";
            var_export($result);
            echo "\n\n发送内容：\n";
            var_export($text);
            echo "\n\n物流服务：\n";
            var_export($serviceOptions);
            echo "\n\n折扣信息：\n";
            var_export($adjustAmount);
            var_export($currencyID);
            $msg = ob_get_clean();
            $subject = "UrgePay 订单催款发送失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $msg, $to);
            $doc = phpQuery::newDocumentXML($result);
            phpQuery::selectDocument($doc);
            $short_message = pq('ShortMessage')->html();
            $long_message = pq('LongMessage')->html();
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', $short_message);
        }
    }
}