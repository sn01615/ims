<?php

/**
 * @desc 附加信息获取
 * @author YangLong
 * @date 2015-05-04
 */
class EbayOtherInfoModel extends BaseModel
{
    
    private $compatabilityLevel; // eBay API version
    
    private $devID;
    
    private $appID;
    
    private $certID;
    
    private $serverUrl; // eBay 服务器地址
    
    private $userToken; // token
    
    private $siteToUseID; // site id
    
    /**
     * @desc 覆盖父方法,返回当前类的(单)实例
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-05-04
     * @return EbayOtherInfoModel
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
     * @desc (eBay)根据case表自增ID获取feedback的评价信息
     * @param int $caseid caseid
     * @param int $sellerId 客户ID
     * @author liaojianwen
     * @date 2015-05-20
     * @return mixed
     */
    public function getFeedbackInfo($caseid,$sellerId,$returnid)
    {
        if ($caseid <= 0 && $returnid <=0 ) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '#caseid# or #returnid# can not empty.');
        }
        if($returnid > 0){
            $tokeninfo = ReturnDetailDAO::getInstance()->getItemInfoByReturnId($returnid,$sellerId); 
        } else {
            $columns = array(
                'c.i_itemId',
                'c.i_transactionId'
            );
            $conditions = "c.case_id=:case_id and s.seller_id=:seller_id";
            $params = array(
                ":case_id" => $caseid,
                ':seller_id' => $sellerId
            );
            $joinArray = array(
                array(
                    ShopDAO::getInstance()->igetproperty('tableName') . ' s',
                    "s.shop_id=c.shop_id"
                )
            );
            $tableAlias = 'c';
            $tokeninfo = CaseDAO::getInstance()->iselect($columns, $conditions, $params, false, $joinArray, $tableAlias);
        }
        if ($tokeninfo !== false) {
            $columns = array(
                'CommentType'
            );
            $conditions = 'TransactionID=:TransactionID and Role =:Role';
            $params = array(
                ':TransactionID' => $tokeninfo['i_transactionId'],
                ':Role' => 'Seller'
            );
            $result= EbayFeedbackTransactionDAO::getInstance()->iselect($columns, $conditions, $params, false);
            if (!empty($result)) {
                 return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
             } else {
                 return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'no feedback.'); 
             }
          }
    }
    
    /**
     * @desc 获取客户留言
   	 * @param int $caseid caseid
     * @param int $sellerId 客户ID
     * @author liaojianwen
     * @date 2015-05-21
     * @return mixed
     */
    public function getEbayOrderNote($caseid, $sellerId,$returnid)
    {
        if ($caseid <= 0 && $returnid <= 0) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '#caseid#  or #returnid# can not empty.');
        }
        if($returnid > 0){
             $tokeninfo = ReturnDetailDAO::getInstance()->getItemInfoByReturnId($returnid,$sellerId); 
        } else {
            $columns = array(
               	'c.i_itemId',
                'c.i_transactionId',
                's.site_id',
                's.token'
            );
            $conditions = 'c.case_id=:case_id and s.seller_id=:seller_id';
            $params = array(
                ':case_id' => $caseid,
                ':seller_id' => $sellerId
            );
            $joinArray = array(
                array(
                    ShopDAO::getInstance()->igetproperty('tableName') . ' s',
                    's.shop_id=c.shop_id'
                )
            );
            $tableAlias = 'c';
            $tokeninfo = CaseDAO::getInstance()->iselect($columns, $conditions, $params, false, $joinArray, $tableAlias);
        }
        if ($tokeninfo !== false) {
            $columns = array(
                'ebay_order_note_id',
                'dataxml'
            );
            $conditions = 'transaction_id=:TransactionID and item_id =:ItemID';
            $params = array(
                ':TransactionID' => $tokeninfo['i_transactionId'],
                ':ItemID' => $tokeninfo['i_itemId']
            );
            $dbxml = EbayOrderNoteDAO::getInstance()->iselect($columns, $conditions, $params, false);
            if (empty($dbxml)) {
                $xml = $this->eBayGetOrderNote($tokeninfo['token'],array(array(
                    'TransactionID' =>$tokeninfo['i_transactionId'],
                    'ItemID'=>$tokeninfo['i_itemId']
                )),$tokeninfo['site_id']);
                $icolumns = array(
                    'transaction_id' => $tokeninfo['i_transactionId'],
                    'item_id' => $tokeninfo['i_itemId'],
                    'dataxml' => $xml,
                    'create_time' => time()
                );
            } else {
                $xml = $dbxml['dataxml'];
            }
            $doc = phpQuery::newDocumentXML($xml);
            phpQuery::selectDocument($doc);
            if ($doc['Ack']->html() == 'Success') {
                if (empty($dbxml)) {
                    EbayOrderNoteDAO::getInstance()->iinsert($icolumns);
                }
                $length = $doc['OrderArray>Order>TransactionArray>Transaction']->length;
                $_obj = $doc['OrderArray>Order>TransactionArray>Transaction'];
                for ($i = 0; $i < $length; $i ++) {
                    if($tokeninfo['i_transactionId'] === $_obj['>TransactionID']->html()){
                        $shippingAddress = array(
                       		'name' =>$_obj->eq($i)->find('>Buyer>BuyerInfo>ShippingAddress>Name')->html(),
                            'street1' =>$_obj->eq($i)->find('>Buyer>BuyerInfo>ShippingAddress>Street1')->html(),
                            'street2' =>$_obj->eq($i)->find('>Buyer>BuyerInfo>ShippingAddress>Street2')->html(),
                            'CityName'=>$_obj->eq($i)->find('>Buyer>BuyerInfo>ShippingAddress>CityName')->html(),
                            'StateOrProvince'=>$_obj->eq($i)->find('>Buyer>BuyerInfo>ShippingAddress>StateOrProvince')->html(),
                            'Country'=>$_obj->eq($i)->find('>Buyer>BuyerInfo>ShippingAddress>Country')->html(),
                            'CountryName'=>$_obj->eq($i)->find('>Buyer>BuyerInfo>ShippingAddress>CountryName')->html(),
                            'SKUS' =>$_obj->eq($i)->find('>Variation>SKU')->html(),
                            'SKU'=>$_obj->eq($i)->find('>Item>SKU')->html(),
                            'CurrentPrice'=>$_obj->eq($i)->find('>Item>SellingStatus>CurrentPrice')->html(),
                            'currencyID'=>$_obj->eq($i)->find('>Item>SellingStatus>CurrentPrice')->attr('currencyID'),
                            'TransactionPrice'=>$_obj->eq($i)->find('>TransactionPrice')->html(),
                            'TcurrencyID'=>$_obj->eq($i)->find('>TransactionPrice')->attr('currencyID'),
                            'PaidTime'=>$doc['OrderArray>Order>PaidTime']->html(),
                            'PaymentMethods'=>$doc['OrderArray>Order>PaymentMethods']->html(),
                            'ShippingCarrierUsed'=>$_obj->eq($i)->find('ShippingDetails>ShipmentTrackingDetails>ShippingCarrierUsed')->html(),
                            'ShipmentTrackingNumber'=>$_obj->eq($i)->find('>ShippingDetails>ShipmentTrackingDetails>ShipmentTrackingNumber')->html(),
                            'ActualShippingCost'=>$_obj->eq($i)->find('>ActualShippingCost')->html(),
                            'ActualcurrencyID'=>$_obj->eq($i)->find('>ActualShippingCost')->attr('currencyID'),
                            'ShipedTime'=>strtotime($doc['OrderArray>Order>ShippedTime']->html()),
                            'PaymentMethods'=>$doc['OrderArray>Order>PaymentMethods']->html(),
                            'PaymentTime'=>strtotime($doc['OrderArray>Order>MonetaryDetails>Payments>Payment>PaymentTime']->html()),
                            'OrderLineItemID'=>$_obj->eq($i)->find('>OrderLineItemID')->html(),
                            'ShippingService'=>$doc['OrderArray>Order>ShippingServiceSelected>ShippingService']->html(),
                            'ShippingServiceCost'=>$doc['OrderArray>Order>ShippingServiceSelected>ShippingServiceCost']->html(),
                            'S_currencyID'=>$doc['OrderArray>Order>ShippingServiceSelected>ShippingServiceCost']->attr('currencyID')
                        );
                        
                        $var_length = $_obj['>Variation>VariationSpecifics>NameValueList']->length;
                        $_variation = $_obj['>Variation>VariationSpecifics>NameValueList'];
                        for($k = 0; $k<$var_length; $k++){
                            $variation[] = array(
                                'Name'=>$_variation->eq($k)->find('>Name')->html(),
                                'Value'=>$_variation->eq($k)->find('>Value')->html()
                            );                        
                        }
                        
                        $data = EbayShippingServiceDetailsDAO::getInstance()->findByAttributes(array('ShippingService'=>$shippingAddress['ShippingService']),array('Description'));
                        if(isset($data['Description'])){
                            $shippingAddress['ShippingService'] = $data['Description'];
                        }
                        
                        // @YangLong
                        // guest email hide
                        if (Yii::app()->session['userInfo']['user_id'] == 99999) {
                            $shippingAddress['street1'] = preg_replace('/.{4}(.*)/', '****$1', $shippingAddress['street1']);
                            $shippingAddress['street2'] = preg_replace('/.{4}(.*)/', '****$1', $shippingAddress['street2']);
                        }
                    }
                }
                
                $length_refund = $doc['OrderArray>Order>MonetaryDetails>Refunds>Refund']->length;
                $_refund = $doc['OrderArray>Order>MonetaryDetails>Refunds>Refund'];
                for ($j=0; $j < $length_refund; $j++){
                    $refund[] = array(
                        'RefundStatus'=>$_refund->eq($j)->find('>RefundStatus')->html(),
                        'RefundType'=>$_refund->eq($j)->find('>RefundType')->html(),
                        'RefundTo'=>$_refund->eq($j)->find('>RefundTo')->html(),
                        'RefundTime'=>strtotime($_refund->eq($j)->find('>RefundTime')->html()),
                        'RefundAmount'=>$_refund->eq($j)->find('>RefundAmount')->html(),
                        'RcurrencyID'=>$_refund->eq($j)->find('>RefundAmount')->attr('currencyID'),
                        'ReferenceID'=>$_refund->eq($j)->find('>ReferenceID')->html(),
                        'FeeOrCreditAmount'=>$_refund->eq($j)->find('>FeeOrCreditAmount')->html(),
                        'FcurrencyID'=>$_refund->eq($j)->find('>FeeOrCreditAmount')->attr('currencyID')
                    );
                }
                $result = array(
                    'OrderId' => $doc['OrderArray>Order>OrderID']->html(),
                    'OrderCreateTime'=>strtotime($doc['OrderArray>Order>CreatedTime']->html()),
                    'Note' => $doc['OrderArray>Order>BuyerCheckoutMessage']->html(),
                    'Address' => isset($shippingAddress) ? $shippingAddress : '',
                    'Refund'=> isset($refund)? $refund : '',
                    'variation' => isset($variation)? $variation:''
                );
                return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
            } else {
                return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'call ebay api err.');
            }
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '#msgid# or #caseid# not found.');
        }
    }
    
    /**
     * @desc (eBay)根据msg表自增ID获取Item详细信息保存入数据库并返回有用的部分
     * @param string $msgid msg表AI
     * @param int $sellerId 客户ID
     * @param string $caseid caseid
     * @author YangLong
     * @date 2015-05-04
     * @return mixed
     */
    public function eBayGetItemInfo($msgid, $sellerId, $caseid)
    {
        if ($msgid <= 0 && $caseid <= 0) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '#msgid# or #caseid# can not empty.');
        }
        if ($msgid > 0) {
            $lt = 'm';
            $if = 'ItemID';
            $pk = 'msg_id';
            $pkv = $msgid;
            $dao = MsgDAO::getInstance();
        } else {
            $lt = 'c';
            $if = 'i_itemId';
            $pk = 'case_id';
            $pkv = $caseid;
            $dao = CaseDAO::getInstance();
        }
        $columns = array(
            "{$lt}.{$if}",
            's.site_id',
            's.token'
        );
        $conditions = "{$lt}.{$pk}=:{$pk} and s.seller_id=:seller_id";
        $params = array(
            ":{$pk}" => $pkv,
            ':seller_id' => $sellerId
        );
        $joinArray = array(
            array(
                ShopDAO::getInstance()->igetproperty('tableName') . ' s',
                "s.shop_id={$lt}.shop_id"
            )
        );
        $tableAlias = $lt;
        $tokeninfo = $dao->iselect($columns, $conditions, $params, false, $joinArray, $tableAlias);
        
        if ($tokeninfo !== false) {
            
            if (empty($tokeninfo[$if])) {
                $columns = array(
                    'ItemId'
                );
                $conditions = 'msg_id=:msg_id';
                $params = array(
                    ':msg_id' => $msgid
                );
                $_temp = MsgTextResolveDAO::getInstance()->iselect($columns, $conditions, $params, false);
                $tokeninfo[$if] = $_temp['ItemId'];
            }
            
            $columns = array(
                'ebay_item_info_id',
                'dataxml'
            );
            $conditions = 'ItemID=:ItemID';
            $params = array(
                ':ItemID' => $tokeninfo[$if]
            );
            $dbxml = EbayItemInfoDAO::getInstance()->iselect($columns, $conditions, $params, false);
            if (empty($dbxml)) {
                $xml = $this->eBayGetItem($tokeninfo['token'], $tokeninfo[$if], $tokeninfo['site_id']);
                $icolumns = array(
                    'ItemID' => $tokeninfo[$if],
                    'dataxml' => $xml,
                    'create_time' => time()
                );
            } else {
                $xml = $dbxml['dataxml'];
            }
            $doc = phpQuery::newDocumentXML($xml);
            phpQuery::selectDocument($doc);
            
            if ($doc['Ack']->html() == 'Success') {
                if (empty($dbxml)) {
                    EbayItemInfoDAO::getInstance()->iinsert($icolumns);
                }
                
                $skus = array();
                $variation = $doc['Item>Variations>Variation'];
                $length = $variation->length;
                for ($i = 0; $i < $length; $i ++) {
                    $skus[] = $variation['>SKU']->eq($i)->html();
                }
                if (empty($skus)) {
                    $skus[] = $doc['Item>SKU']->html();
                }
                $result = array(
                    'CurrentPrice' => $doc['Item>ListingDetails>ConvertedStartPrice']->html(),
                    'CurrentPrice_currencyID' => $doc['Item>ListingDetails>ConvertedStartPrice']->attr('currencyID'),
                    'skus' => $skus,
                    'SKU' => $doc['Item>SKU']->html(),
                    'Location' => $doc['Item>Location']->html(),
                    'StartPrice' => $doc['Item>StartPrice']->html(),
                    'StartPrice_currencyID' => $doc['Item>StartPrice']->attr('currencyID'),
                    'PictureDetails' => array(
                        'GalleryURL' => $doc['PictureDetails>GalleryURL']->html()
                    ),
                    'Site' => $doc['Item>Site']->html()
                );
                return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
            } else {
                return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', pq('Errors>LongMessage')->html());
            }
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '#msgid# or #caseid# not found.');
        }
    }
    
    /**
     * @desc 根据ItemID或TransactionID或SKU获取item详细信息
     * @param string $token
     * @param string $ItemID
     * @param number $siteid 站点ID
     * @param string $TransactionID
     * @param string $SKU
     * @param string $VariationSKU
     * @param string $DetailLevel ItemReturnAttributes/ItemReturnDescription/ReturnAll
     * @author YangLong
     * @date 2015-05-04
     * @return string XML
     */
    private function eBayGetItem($token, $ItemID, $siteid = 0, $TransactionID = '', $SKU = '', $VariationSKU = '', $DetailLevel = '')
    {
        $callName = 'GetItem';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<GetItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>' . $token . '</eBayAuthToken>
  </RequesterCredentials>';
        if (! empty($ItemID)) {
            $requestXmlBody .= '
  <ItemID>' . $ItemID . '</ItemID>';
        }
        if (! empty($TransactionID)) {
            $requestXmlBody .= '
  <TransactionID>' . $TransactionID . '</TransactionID>';
        }
        if (! empty($SKU)) {
            $requestXmlBody .= '
  <SKU>' . $SKU . '</SKU>';
        }
        if (! empty($VariationSKU)) {
            $requestXmlBody .= '
  <VariationSKU>' . $VariationSKU . '</VariationSKU>';
        }
        if (! empty($DetailLevel)) {
            $requestXmlBody .= '
  <DetailLevel>' . $DetailLevel . '</DetailLevel>';
        }
        $requestXmlBody .= '
</GetItemRequest>';
        
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/GetItem.html
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/index.html#Limitations
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:919";
        $session->headers[] = "X-EBAY-API-DEV-NAME:{$this->devID}";
        $session->headers[] = "X-EBAY-API-APP-NAME:{$this->appID}";
        $session->headers[] = "X-EBAY-API-CERT-NAME:{$this->certID}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        $session->headers[] = "X-EBAY-API-SITEID:{$siteid}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        return $responseXml;
    }
    
    /**
     * @desc 根据订单号获取订单信息
     * @param string $token token
     * @param array $OrderIDArray 订单号数组
     * @param integer $CreateTimeFrom 创建时间开始
     * @param integer $CreateTimeTo 创建时间结束时间
     * @param string $OrderStatus 订单状态，Applicable values: Active、All、CancelPending、Completed、Inactive、Shipped
     * @param integer $PageNumber 页码
     * @param integer $EntriesPerPage 页大小
     * @param string $ListingType 是否检索Half.com的订单，Applicable values: Half
     * @param integer $ModTimeFrom 订单修改时间开始
     * @param integer $ModTimeTo 订单修改时间结束
     * @param integer $NumberOfDays 在过去N天内寻找订单，不能和ModTimeFrom/ModTimeTo及CreateTimeFrom/CreateTimeTo同时使用，如果使用了该选项会被忽略
     * @param string $OrderRole 根据订单角色过滤，Applicable values: Buyer、CustomCode、Seller
     * @param integer $siteid 站点ID
     * @param string $SortingOrder 排序方式，Applicable values: Ascending、CustomCode、Descending
     * @param string $DetailLevel Applicable values: ReturnAll
     * @param string $IncludeFinalValueFee 是否包含成交费，Final Value Fee (FVF)
     * @author YangLong
     * @date 2015-05-05
     * @return mixed
     */
    public function eBayGetOrders($token, array $OrderIDArray, $CreateTimeFrom, $CreateTimeTo, $OrderStatus = '', $PageNumber = 1,
         $EntriesPerPage = 100, $ListingType = '', $ModTimeFrom = 0, $ModTimeTo = 0, $NumberOfDays = 0, $OrderRole = '',
         $siteid = 0, $SortingOrder = '', $DetailLevel = 'ReturnAll', $IncludeFinalValueFee = 'true')
    {
        $callName = 'GetOrders';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>' . $token . '</eBayAuthToken>
  </RequesterCredentials>';
        if (! empty($CreateTimeFrom)) {
            $requestXmlBody .= '
  <CreateTimeFrom>' . $this->fmtDate($CreateTimeFrom) . '</CreateTimeFrom>';
        }
        if (! empty($CreateTimeTo)) {
            $requestXmlBody .= '
  <CreateTimeTo>' . $this->fmtDate($CreateTimeTo) . '</CreateTimeTo>';
        }
        if (! empty($IncludeFinalValueFee)) {
            $requestXmlBody .= '
  <IncludeFinalValueFee>' . $IncludeFinalValueFee . '</IncludeFinalValueFee>';
        }
        if (! empty($ListingType)) {
            $requestXmlBody .= '
  <ListingType>' . $ListingType . '</ListingType>';
        }
        if (! empty($ModTimeFrom)) {
            $requestXmlBody .= '
  <ModTimeFrom>' . $this->fmtDate($ModTimeFrom) . '</ModTimeFrom>';
        }
        if (! empty($ModTimeTo)) {
            $requestXmlBody .= '
  <ModTimeTo>' . $this->fmtDate($ModTimeTo) . '</ModTimeTo>';
        }
        if (! empty($NumberOfDays)) {
            $requestXmlBody .= '
  <NumberOfDays>' . $NumberOfDays . '</NumberOfDays>';
        }
        if (! empty($OrderIDArray)) {
            $requestXmlBody .= '
  <OrderIDArray>';
            foreach ($OrderIDArray as $OrderID) {
                $requestXmlBody .= '
    <OrderID>' . $OrderID . '</OrderID>';
            }
            $requestXmlBody .= '
  </OrderIDArray>';
        }
        if (! empty($OrderRole)) {
            $requestXmlBody .= '
  <OrderRole>' . $OrderRole . '</OrderRole>';
        }
        if (! empty($OrderStatus)) {
            $requestXmlBody .= '
  <OrderStatus>' . $OrderStatus . '</OrderStatus>';
        }
        $requestXmlBody .= '
  <Pagination>';
        $requestXmlBody .= '
    <EntriesPerPage>' . $EntriesPerPage . '</EntriesPerPage>';
        $requestXmlBody .= '
    <PageNumber>' . $PageNumber . '</PageNumber>';
        $requestXmlBody .= '
  </Pagination>';
        if (! empty($SortingOrder)) {
            $requestXmlBody .= '
  <SortingOrder>' . $SortingOrder . '</SortingOrder>';
        }
        $requestXmlBody .= '
  <DetailLevel>' . $DetailLevel . '</DetailLevel>';
        $requestXmlBody .= '
</GetOrdersRequest>';
        
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/GetOrders.html
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/index.html#Limitations
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:925";
        $session->headers[] = "X-EBAY-API-DEV-NAME:{$this->devID}";
        $session->headers[] = "X-EBAY-API-APP-NAME:{$this->appID}";
        $session->headers[] = "X-EBAY-API-CERT-NAME:{$this->certID}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        $session->headers[] = "X-EBAY-API-SITEID:{$siteid}";
        
        $tryCount = 0;
        
        label1:
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('eBayGetOrdersFailure')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'time' => time(),
                'times' => 1
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('eBayGetOrdersFailure')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'time' => time(),
                'times' => 2
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (! XMLTool::IsXML($responseXml)) {
            iMongo::getInstance()->setCollection('eBayGetOrdersBadXML')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'tryCount' => $tryCount,
                'time' => time()
            ));
            if ($tryCount < 10) {
                $tryCount ++;
                goto label1;
            }
            return false;
        }
        
        if (stripos($responseXml, '<ErrorClassification>SystemError</ErrorClassification>')) {
            if ($tryCount < 15) {
                $tryCount ++;
                sleep(5);
                goto label1;
            }
        }
        
        return $responseXml;
    }
    
    /**
     * @desc 获取用户信息
     * @param string $token
     * @param string $userID
     * @param number $siteid
     * @author YangLong
     * @date 2015-06-12
     * @return mixed
     */
    public function eBayGetUser($token, $UserID, $ItemID = '', $DetailLevel = '', $siteid = 0)
    {
        $callName = 'GetUser';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<GetUserRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>' . $token . '</eBayAuthToken>
  </RequesterCredentials>';
        if (! empty($ItemID)) {
            $requestXmlBody .= '
  <ItemID>' . $ItemID . '</ItemID>';
        }
        if (! empty($UserID)) {
            $requestXmlBody .= '
  <UserID>' . $UserID . '</UserID>';
        }
        if (! empty($DetailLevel)) {
            $requestXmlBody .= '
  <DetailLevel>' . $DetailLevel . '</DetailLevel>';
        }
        $requestXmlBody .= '
</GetUserRequest>';
        
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/GetUser.html
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/index.html#Limitations
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:927";
        $session->headers[] = "X-EBAY-API-DEV-NAME:{$this->devID}";
        $session->headers[] = "X-EBAY-API-APP-NAME:{$this->appID}";
        $session->headers[] = "X-EBAY-API-CERT-NAME:{$this->certID}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        $session->headers[] = "X-EBAY-API-SITEID:{$siteid}";
        
        $tryCount = 0;
        
        label1:
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('eBayGetUserF')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'time' => time(),
                'times' => 1
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('eBayGetUserF')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'time' => time(),
                'times' => 2
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (! XMLTool::IsXML($responseXml)) {
            iMongo::getInstance()->setCollection('eBayGetUserBadXML')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'tryCount' => $tryCount,
                'time' => time()
            ));
            if ($tryCount < 10) {
                $tryCount ++;
                goto label1;
            }
            return false;
        }
        
        return $responseXml;
    }
    
    /**
     * @desc 获取eBay枚举信息
     * @param string $token
     * @param string $DetailName
     * @param number $siteid
     * @author YangLong
     * @date 2015-07-23
     * @return mixed
     */
    public function eBayGeteBayDetails($token, $DetailName, $siteid = 0)
    {
        $callName = 'GeteBayDetails';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<GeteBayDetailsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>' . $token . '</eBayAuthToken>
  </RequesterCredentials>
  <DetailName>' . $DetailName . '</DetailName>
</GeteBayDetailsRequest>';
        
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/GetUser.html
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/index.html#Limitations
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:927";
        $session->headers[] = "X-EBAY-API-DEV-NAME:{$this->devID}";
        $session->headers[] = "X-EBAY-API-APP-NAME:{$this->appID}";
        $session->headers[] = "X-EBAY-API-CERT-NAME:{$this->certID}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        $session->headers[] = "X-EBAY-API-SITEID:{$siteid}";
        
        $tryCount = 0;
        
        label1:
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('eBayGeteBayDetailsFailure')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'time' => time(),
                'times' => 1
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('eBayGeteBayDetailsFailure')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'time' => time(),
                'times' => 2
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (! XMLTool::IsXML($responseXml)) {
            iMongo::getInstance()->setCollection('eBayGeteBayDetailsBadXML')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'tryCount' => $tryCount,
                'time' => time()
            ));
            if ($tryCount < 10) {
                $tryCount ++;
                goto label1;
            }
            return false;
        }
        
        if (stripos($responseXml, '<ErrorClassification>SystemError</ErrorClassification>')) {
            if ($tryCount < 15) {
                $tryCount ++;
                sleep(5);
                goto label1;
            }
        }
        
        iMongo::getInstance()->setCollection('eBayGeteBayDetails')->insert(array(
            'requestXmlBody' => $requestXmlBody,
            'responseXml' => $responseXml,
            'tryCount' => $tryCount,
            'time' => time()
        ));
        
        return $responseXml;
    }
    
    /**
     * @desc 向买家发送催款信息
     * @param string $token
     * @param string $ShippingServiceOptions array array(array('ShippingService'=>$value,'currencyID'=>$value,'ShippingServiceCost'=>$value),...)
     * @param float $AdjustmentAmount
     * @param string $AdjustmentAmountCurrencyID
     * @param string $CheckoutInstructions
     * @param string $OrderLineItemID
     * @param string $OrderID
     * @param int $siteid
     * @author YangLong
     * @date 2015-07-23
     * @return mixed
     */
    public function eBaySendInvoice($token, $ShippingServiceOptions, $AdjustmentAmount, $AdjustmentAmountCurrencyID, $CheckoutInstructions, $OrderLineItemID, $OrderID, $siteid)
    {
        $callName = 'SendInvoice';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<SendInvoiceRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>' . $token . '</eBayAuthToken>
  </RequesterCredentials>';
        if (! empty($AdjustmentAmount)) {
            $requestXmlBody .= '
  <AdjustmentAmount currencyID="' . $AdjustmentAmountCurrencyID . '">' . $AdjustmentAmount . '</AdjustmentAmount>';
        }
        if (! empty($CheckoutInstructions)) {
            $requestXmlBody .= '
  <CheckoutInstructions>' . $CheckoutInstructions . '</CheckoutInstructions>';
        }
        if (! empty($OrderLineItemID)) {
            $requestXmlBody .= '
  <OrderLineItemID>' . $OrderLineItemID . '</OrderLineItemID>';
        }
        if (! empty($OrderID)) {
            $requestXmlBody .= '
  <OrderID>' . $OrderID . '</OrderID>';
        }
        if (is_array($ShippingServiceOptions)) {
            foreach ($ShippingServiceOptions as $value) {
                if (isset($value['ShippingService']) && isset($value['currencyID']) && isset($value['ShippingServiceCost'])) {
                    $requestXmlBody .= '
  <ShippingServiceOptions>
    <ShippingService>' . $value['ShippingService'] . '</ShippingService>
    <ShippingServiceCost currencyID="' . $value['currencyID'] . '">' . $value['ShippingServiceCost'] . '</ShippingServiceCost>
  </ShippingServiceOptions>';
                }
            }
        }
        $requestXmlBody .= '
</SendInvoiceRequest>';
        
        // @see http://developer.ebay.com/Devzone/XML/docs/Reference/ebay/SendInvoice.html
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/index.html#Limitations
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
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
     * @desc 根据交易号、产品ID获取客户评价
     * @param string $token
     * @param array $OrderInfoArray
     * @param number $siteid
     * @author liaojianwen
     * @date 2015-05-21
     * @return mixed
     */
    private function eBayGetOrderNote($token, array $OrderInfoArray, $siteid = 0)
    {
        $callName = 'GetOrderTransactions';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        
  $requestXmlBody  .= '<GetOrderTransactionsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                  <RequesterCredentials>
                    <eBayAuthToken>'.$token.'</eBayAuthToken>
                  </RequesterCredentials>
				<ItemTransactionIDArray>';
                  foreach ($OrderInfoArray as $value){
          				 $requestXmlBody .= '<ItemTransactionID> 
           			   <ItemID>'.$value['ItemID'].'</ItemID>
            			  <TransactionID>'.$value['TransactionID'].'</TransactionID>
            			
            			</ItemTransactionID>';}
 				$requestXmlBody .=' </ItemTransactionIDArray>
     				<DetailLevel>ReturnAll</DetailLevel>
                    </GetOrderTransactionsRequest>';
        
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/GetOrders.html
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/index.html#Limitations
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:919";
        $session->headers[] = "X-EBAY-API-DEV-NAME:{$this->devID}";
        $session->headers[] = "X-EBAY-API-APP-NAME:{$this->appID}";
        $session->headers[] = "X-EBAY-API-CERT-NAME:{$this->certID}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        $session->headers[] = "X-EBAY-API-SITEID:{$siteid}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        return $responseXml;
    }
    
    /**
     * @desc 获取最新一条回复的消息
     * @param int $msgid msg表ID
     * @author YangLong
     * @date 2015-05-22
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function getLastSendMsg($msgid)
    {
        if (empty($msgid)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'msgid can not empty.');
        }
        
        $columns = array(
            'msg_content',
            'copy_to_sender',
            'image_urls',
            'action_username',
            'create_time'
        );
        $conditions = 'msg_id=:msg_id';
        $params = array(
            ':msg_id' => $msgid
        );
        
        $result = MsgReplyLogDAO::getInstance()->iselect($columns, $conditions, $params, false, array(), '', MsgReplyLogDAO::getInstance()->igetproperty('primaryKey') . ' desc');
        
        if (! empty($result['image_urls'])) {
            $result['image_urls'] = json_decode($result['image_urls'], true);
        }
        
        if ($result === false) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        }
    }
    
    /**
     * @desc 生成订单下载队列
     * @author YangLong
     * @date 2015-06-13
     * @return NULL
     */
    public function generateEbayOrdersDownQueue()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $shops = MsgDownDAO::getInstance()->getEbShop('orders');
        foreach ($shops as $key => &$shop) {
            $_time = time();
            $_size = 3600 * 24 * 10;
            if (empty($shop['orders_down_time'])) {
                for ($i = 0; $i < 13; $i ++) {
                    $columns = array(
                        'token' => $shop['token'],
                        'start_time' => $_time - $_size * ($i + 1),
                        'end_time' => $_time - $_size * $i + 60,
                        'shop_id' => $shop['shop_id'],
                        'seller_id' => $shop['seller_id'],
                        'site_id' => $shop['site_id'],
                        'priority' => 1000 - $i,
                        'create_time' => time(),
                        'notes' => ($this->fmtDate($_time - $_size * ($i + 1)) . ' - ' . $this->fmtDate($_time - $_size * $i))
                    );
                    EbayOrdersDownQueueDAO::getInstance()->iinsert($columns);
                }
            } else {
                $columns = array(
                    'token' => $shop['token'],
                    'start_time' => $shop['orders_down_time'] - 60,
                    'end_time' => time() + 60,
                    'shop_id' => $shop['shop_id'],
                    'seller_id' => $shop['seller_id'],
                    'site_id' => $shop['site_id'],
                    'priority' => 1001,
                    'create_time' => time(),
                    'notes' => ($this->fmtDate($shop['orders_down_time'] - 60) . ' - ' . $this->fmtDate($shop['orders_down_time'] + 60) . ' - :)')
                );
                
                $conditions = 'shop_id=:shop_id and start_time>=:start_time';
                $params = array(
                    ':shop_id' => $columns['shop_id'],
                    ':start_time' => $columns['start_time']
                );
                EbayOrdersDownQueueDAO::getInstance()->idelete($conditions, $params);
                
                EbayOrdersDownQueueDAO::getInstance()->iinsert($columns);
            }
            
            $columns = array(
                'orders_down_time' => $_time
            );
            $conditions = 'shop_id=:shop_id';
            $params = array(
                ':shop_id' => $shop['shop_id']
            );
            ShopDAO::getInstance()->iupdate($columns, $conditions, $params);
        }
        unset($shop);
    }
    
    /**
     * @desc 运行订单下载队列
     * @author YangLong
     * @date 2015-06-14
     */
    public function executeEbayOrdersDownQueue()
    {
        DaemonLockTool::lock(__METHOD__ . rand(1, 10));
        
        $startTime = time();
        
        label1:
        
        if (time() - $startTime > 600) {
            return false;
        }
        
        $Queues = EbayOrdersDownQueueDAO::getInstance()->getOrdersDownQueueData();
        if ($Queues !== false) {
            foreach ($Queues as $Queue) {
                $page = 0;
                while (true) {
                    $page ++;
                    
                    if ($Queue['start_time'] > (time() - 3600 * 24 * 90)) {
                        $dataxml = EbayOtherInfoModel::model()->eBayGetOrders($Queue['token'], array(), 0, 0, '', $page, 100, '', $Queue['start_time'], $Queue['end_time']);
                    } else {
                        $dataxml = EbayOtherInfoModel::model()->eBayGetOrders($Queue['token'], array(), $Queue['start_time'], $Queue['end_time'], '', $page, 100, '', 0, 0);
                    }
                    
                    $columns = array(
                        'shop_id' => $Queue['shop_id'],
                        'base64data' => $dataxml,
                        'create_time' => time()
                    );
                    
                    $_pk = EbayOrdersDownQueueDAO::getInstance()->getPk();
                    
                    if ($dataxml === false || stripos($dataxml, '<ErrorCode>10007</ErrorCode>') !== false || stripos($dataxml, '<ErrorCode>21359</ErrorCode>') !== false || stripos($dataxml, '<ErrorCode>21359</ErrorCode>') !== false || stripos($dataxml, '<ErrorCode>16100</ErrorCode>') !== false || stripos($dataxml, '<ErrorCode>518</ErrorCode>') !== false) {
                        // 还原队列
                        $columns = array(
                            'process_sign' => boolConvert::toInt01(false)
                        );
                        $conditions = "{$_pk}=:{$_pk}";
                        $params = array(
                            ":{$_pk}" => $Queue[$_pk]
                        );
                        EbayOrdersDownQueueDAO::getInstance()->iupdate($columns, $conditions, $params);
                        
                        if (stripos($dataxml, '<ErrorCode>518</ErrorCode>') !== false) {
                            sleep(3600 * 24);
                        }
                        if (stripos($dataxml, '<ErrorCode>10007</ErrorCode>') !== false) {
                            sleep(30);
                        }
                        
                        iMongo::getInstance()->setCollection('eBayOrdersDownQueueHy')->insert(array(
                            'dataxml' => $dataxml,
                            'queue_id' => $Queue[$_pk],
                            'time' => time()
                        ));
                    } else {
                        EbayOrdersDownDAO::getInstance()->iinsert($columns);
                    }
                    
                    if (stripos($dataxml, '<Ack>Failure</Ack>') === false) {
                        $conditions = "{$_pk}=:{$_pk}";
                        $params = array(
                            ":{$_pk}" => $Queue[$_pk]
                        );
                        EbayOrdersDownQueueDAO::getInstance()->idelete($conditions, $params);
                        
                        if (stripos($dataxml, '<Ack>Success</Ack>') === false) {
                            iMongo::getInstance()->setCollection('ebayOrdersDQDelLog')->insert(array(
                                'conditions' => $conditions,
                                'params' => $params,
                                'dataxml' => $dataxml,
                                'time' => time()
                            ));
                        }
                    } else {
                        iMongo::getInstance()->setCollection('eBayGetOrdersExeFailure')->insert(array(
                            'dataxml' => $dataxml,
                            'queue_id' => $Queue[$_pk],
                            'time' => time()
                        ));
                    }
                    
                    if (stripos($dataxml, '<HasMoreOrders>true</HasMoreOrders>') === false) {
                        break;
                    }
                }
            }
        } else {
            sleep(5);
            goto label1;
        }
        
        goto label1;
    }
    
    /**
     * @desc 解析订单下载的数据
     * @author YangLong
     * @date 2015-06-14
     * @return null|boolean
     */
    public function parseEbayOrders()
    {
        DaemonLockTool::lock(__METHOD__ . gmdate('h'));
        
        $startTime = time();
        
        label1:
        
        if (time() - $startTime > 600) {
            return false;
        }
        
        $ordersArray = EbayOrdersDownDAO::getInstance()->getOrdersArray();
        if (! empty($ordersArray)) {
            foreach ($ordersArray as $ordersRow) {
                if (empty($ordersRow['base64data'])) {
                    $conditions = EbayOrdersDownDAO::getInstance()->igetproperty('primaryKey') . '=:primaryKey';
                    $params = array(
                        ':primaryKey' => $ordersRow[EbayOrdersDownDAO::getInstance()->igetproperty('primaryKey')]
                    );
                    EbayOrdersDownDAO::getInstance()->idelete($conditions, $params);
                    iMongo::getInstance()->setCollection('base64dataIsEmpty')->insert(array(
                        'time' => time()
                    ));
                    continue;
                }
                $doc = phpQuery::newDocumentXML($ordersRow['base64data']);
                phpQuery::selectDocument($doc);
                if (pq('Ack')->html() == 'Success' || pq('Ack')->html() == 'Warning') {
                    $OrderArray = pq('OrderArray>Order');
                    $length = $OrderArray->length;
                    for ($i = 0; $i < $length; $i ++) {
                        $Order = $OrderArray->eq($i);
                        $columns = array(
                            'shop_id' => $ordersRow['shop_id'],
                            'OrderID' => $Order->find('>OrderID')->html(),
                            'OrderStatus' => $Order->find('>OrderStatus')->html(),
                            'AdjustmentAmount' => $Order->find('>AdjustmentAmount')->html(),
                            'AdjustmentAmount_currencyID' => $Order->find('>AdjustmentAmount')->attr('currencyID'),
                            'AmountPaid' => $Order->find('>AmountPaid')->html(),
                            'AmountPaid_currencyID' => $Order->find('>AmountPaid')->attr('currencyID'),
                            'AmountSaved' => $Order->find('>AmountSaved')->html(),
                            'AmountSaved_currencyID' => $Order->find('>AmountSaved')->attr('currencyID'),
                            'eBayPaymentStatus' => $Order->find('>CheckoutStatus>eBayPaymentStatus')->html(),
                            'LastModifiedTime' => strtotime($Order->find('>CheckoutStatus>LastModifiedTime')->html()),
                            'PaymentMethod' => $Order->find('>CheckoutStatus>PaymentMethod')->html(),
                            'Status' => $Order->find('>CheckoutStatus>Status')->html(),
                            'ShippingDetailsXML' => $Order->find('>ShippingDetails')->htmlOuter(),
                            'SellingManagerSalesRecordNumber' => $Order->find('>ShippingDetails>SellingManagerSalesRecordNumber')->html(),
                            'CreatedTime' => strtotime($Order->find('>CreatedTime')->html()),
                            'SellerEmail' => $Order->find('>SellerEmail')->html(),
                            'AddressID' => $Order->find('>ShippingAddress>AddressID')->html(),
                            'ShippingService' => $Order->find('>ShippingServiceSelected>ShippingService')->html(),
                            'ShippingServiceCost' => $Order->find('>ShippingServiceSelected>ShippingServiceCost')->html(),
                            'ShippingServiceCost_currencyID' => $Order->find('>ShippingServiceSelected>ShippingServiceCost')->attr('currencyID'),
                            'Subtotal' => $Order->find('>Subtotal')->html(),
                            'Subtotal_currencyID' => $Order->find('>Subtotal')->attr('currencyID'),
                            'Total' => $Order->find('>Total')->html(),
                            'Total_currencyID' => $Order->find('>Total')->attr('currencyID'),
                            'BuyerUserID' => $Order->find('>BuyerUserID')->html(),
                            'PaidTime' => strtotime($Order->find('>PaidTime')->html()),
                            'BuyerCheckoutMessage' => $Order->find('>BuyerCheckoutMessage')->html(),
                            'ShippedTime' => strtotime($Order->find('>ShippedTime')->html()),
                            'EIASToken' => $Order->find('>EIASToken')->html(),
                            'PaymentHoldStatus' => $Order->find('>PaymentHoldStatus')->html(),
                            'IsMultiLegShipping' => boolConvert::toInt01($Order->find('>IsMultiLegShipping')->html()),
                            'MonetaryDetailsXML' => $Order->find('>MonetaryDetails')->html(),
                            'SellerUserID' => $Order->find('>SellerUserID')->html(),
                            'SellerEIASToken' => $Order->find('>SellerEIASToken')->html(),
                            'CancelStatus' => $Order->find('>CancelStatus')->html(),
                            'ExtendedOrderID' => $Order->find('>ExtendedOrderID')->html(),
                            'create_time' => time()
                        );
                        $conditions = 'shop_id=:shop_id and OrderID=:OrderID';
                        $params = array(
                            ':shop_id' => $ordersRow['shop_id'],
                            ':OrderID' => $Order->find('>OrderID')->html()
                        );
                        
                        if (empty($params[':OrderID'])) {
                            iMongo::getInstance()->setCollection('OrderWasFalse')->insert(array(
                                'shop_id' => $params[':shop_id'],
                                'OrderID' => $params[':OrderID'],
                                'xml' => $Order->htmlOuter(),
                                'time' => time()
                            ));
                        }
                        
                        // PaymentMethods SET
                        $PaymentMethods = $Order->find('>PaymentMethods');
                        $payMethLength = $PaymentMethods->length;
                        $PaymentMethodsArr = array();
                        for ($j = 0; $j < $payMethLength; $j ++) {
                            $PaymentMethodsArr[] = $PaymentMethods->eq($j)->html();
                        }
                        if (! empty($PaymentMethodsArr)) {
                            $columns['PaymentMethods'] = implode(',', $PaymentMethodsArr);
                        }
                        
                        foreach ($columns as $key => $value) {
                            if ($value === null || $value === false || $value === 'Invalid Request') {
                                unset($columns[$key]);
                            }
                        }
                        
                        $orderpk = EbayOrdersDAO::getInstance()->ireplaceinto($columns, $conditions, $params, true);
                        unset($columns);
                        
                        // 数据有重复
                        if (is_array($orderpk)) {
                            imsTool::clearDuplication('EbayOrdersDAO', $orderpk);
                            $orderpk = array_shift($orderpk);
                            $orderpk = array_shift($orderpk);
                            
                            // 发送邮件通知
                            ob_start();
                            echo "date: ";
                            echo date('Y-m-d H:i:s');
                            echo "\ngmdate: ";
                            echo gmdate('Y-m-d H:i:s');
                            echo "\ntable: ";
                            echo EbayOrdersDAO::getInstance()->getTableName();
                            echo "\npk: ";
                            echo EbayOrdersDAO::getInstance()->getPk();
                            echo "\norderpk:\n";
                            var_dump($orderpk);
                            $text = ob_get_clean();
                            $subject = "[Error.data.duplication][order]Fatal error: 订单数据重复";
                            $to = Yii::app()->params['logmails'];
                            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
                        }
                        
                        // 写 Order TrackingNumber
                        $ShipmentTrackingDetails = $Order->find('>ShippingDetails>ShipmentTrackingDetails');
                        $ilength = $ShipmentTrackingDetails->length;
                        for ($ii = 0; $ii < $ilength; $ii ++) {
                            // 'ebay_orders_id' => $orderpk,
                            $columns = array(
                                'ProcessOrTrackNo' => $ShipmentTrackingDetails->eq($ii)
                                    ->find('>ShipmentTrackingNumber')
                                    ->html()
                            );
                            if (! empty($columns['ProcessOrTrackNo'])) {
                                $conditions = 'ProcessOrTrackNo=:ProcessOrTrackNo';
                                $params = array(
                                    ':ProcessOrTrackNo' => $columns['ProcessOrTrackNo']
                                );
                                Ck1TrackingsDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                                Ck1PackagesDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                            }
                            unset($columns);
                        }
                        unset($ShipmentTrackingDetails);
                        
                        // 写用户表
                        $RItemId = $Order->find('>OrderID')->html();
                        $RItemId = explode('-', $RItemId);
                        $RItemId = $RItemId[0];
                        $columns = array(
                            'shop_id' => $ordersRow['shop_id'],
                            'UserID' => $Order->find('>BuyerUserID')->html(),
                            'RItemId' => $RItemId,
                            'getinterval' => 0,
                            'EIASToken' => $Order->find('>EIASToken')->html()
                        );
                        $conditions = 'UserID=:UserID';
                        $params = array(
                            ':UserID' => $Order->find('>BuyerUserID')->html()
                        );
                        $_upk = EbayUserInfoDAO::getInstance()->ireplaceinto($columns, $conditions, $params, true);
                        
                        // 数据有重复
                        if (is_array($_upk)) {
                            imsTool::clearDuplication('EbayUserInfoDAO', $_upk);
                            $_upk = array_shift($_upk);
                            $_upk = array_shift($_upk);
                            
                            // 发送邮件通知
                            ob_start();
                            echo "date:\n";
                            echo date('Y-m-d H:i:s');
                            echo "\ngmdate:\n";
                            echo gmdate('Y-m-d H:i:s');
                            echo "\n" . 'table:' . "\n";
                            echo EbayUserInfoDAO::getInstance()->getTableName();
                            echo 'pk:' . "\n";
                            echo EbayUserInfoDAO::getInstance()->getPk();
                            echo 'UserID:' . "\n";
                            echo $Order->find('>BuyerUserID')->html() . "\n";
                            echo 'desc: 严重错误:ebay_user_info数据重复。' . "\n";
                            echo '$_upk:' . "\n";
                            var_dump($_upk);
                            $text = ob_get_clean();
                            $subject = "[Error.data.duplication][buyer]Fatal error: data duplication\n";
                            $to = Yii::app()->params['logmails'];
                            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
                        }
                        
                        $columns = array(
                            'ebay_user_info_id' => $_upk,
                            'shop_id' => $ordersRow['shop_id']
                        );
                        $conditions = 'ebay_user_info_id=:ebay_user_info_id and shop_id=:shop_id';
                        $params = array(
                            ':ebay_user_info_id' => $_upk,
                            ':shop_id' => $ordersRow['shop_id']
                        );
                        EbayUserShopsDAO::getInstance()->ireplaceinto($columns, $conditions, $params, false, true);
                        
                        // ShippingAddress
                        $_tempAddrId = $Order->find('>ShippingAddress>AddressID')->html();
                        if (! empty($_tempAddrId)) {
                            $columns = array(
                                'user_id' => $Order->find('>BuyerUserID')->html(),
                                'EIASToken' => $Order->find('>EIASToken')->html(),
                                'AddressID' => $Order->find('>ShippingAddress>AddressID')->html(),
                                'AddressOwner' => $Order->find('>ShippingAddress>AddressOwner')->html(),
                                'CityName' => $Order->find('>ShippingAddress>CityName')->html(),
                                'Country' => $Order->find('>ShippingAddress>Country')->html(),
                                'CountryName' => $Order->find('>ShippingAddress>CountryName')->html(),
                                'ExternalAddressID' => $Order->find('>ShippingAddress>ExternalAddressID')->html(),
                                'Name' => $Order->find('>ShippingAddress>Name')->html(),
                                'Phone' => $Order->find('>ShippingAddress>Phone')->html(),
                                'PostalCode' => $Order->find('>ShippingAddress>PostalCode')->html(),
                                'StateOrProvince' => $Order->find('>ShippingAddress>StateOrProvince')->html(),
                                'Street1' => $Order->find('>ShippingAddress>Street1')->html(),
                                'Street2' => $Order->find('>ShippingAddress>Street2')->html(),
                                'AddressAttributeXML' => $Order->find('>ShippingAddress>AddressAttribute')->html(),
                                'create_time' => time()
                            );
                            $conditions = 'EIASToken=:EIASToken and AddressID=:AddressID';
                            $params = array(
                                ':EIASToken' => $Order->find('>EIASToken')->html(),
                                ':AddressID' => $Order->find('>ShippingAddress>AddressID')->html()
                            );
                            
                            foreach ($columns as $_key => $_value) {
                                if ($_value === null || $_value === false || $_value === 'Invalid Request') {
                                    unset($columns[$_key]);
                                }
                            }
                            
                            EbayUserAddressDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                        }
                        
                        // :)
                        
                        $ExternalTransactions = $Order->find('>ExternalTransaction');
                        $extTransLength = $ExternalTransactions->length;
                        for ($j = 0; $j < $extTransLength; $j ++) {
                            $ExternalTransaction = $ExternalTransactions->eq($j);
                            $columns = array(
                                'ebay_orders_id' => $orderpk,
                                'ExternalTransactionID' => $ExternalTransaction->find('>ExternalTransactionID')->html(),
                                'ExternalTransactionTime' => strtotime($ExternalTransaction->find('>ExternalTransactionTime')->html()),
                                'FeeOrCreditAmount' => $ExternalTransaction->find('>FeeOrCreditAmount')->html(),
                                'FeeOrCreditAmount_currencyID' => $ExternalTransaction->find('>FeeOrCreditAmount')->attr('currencyID'),
                                'PaymentOrRefundAmount' => $ExternalTransaction->find('>PaymentOrRefundAmount')->html(),
                                'PaymentOrRefundAmount_currencyID' => $ExternalTransaction->find('>PaymentOrRefundAmount')->attr('currencyID'),
                                'ExternalTransactionStatus' => $ExternalTransaction->find('>ExternalTransactionStatus')->html(),
                                'create_time' => time()
                            );
                            $conditions = 'ebay_orders_id=:ebay_orders_id and ExternalTransactionID=:ExternalTransactionID';
                            $params = array(
                                ':ebay_orders_id' => $orderpk,
                                ':ExternalTransactionID' => $ExternalTransaction->find('>ExternalTransactionID')->html()
                            );
                            
                            EbayOrderExtTransDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                            unset($columns);
                        }
                        
                        // == == == :)
                        
                        $TransactionArray = $Order->find('TransactionArray>Transaction');
                        $tlength = $TransactionArray->length;
                        for ($ti = 0; $ti < $tlength; $ti ++) {
                            $Transaction = $TransactionArray->eq($ti);
                            $columns = array(
                                'shop_id' => $ordersRow['shop_id'],
                                'ebay_orders_id' => $orderpk,
                                'Buyer_Email' => $Transaction->find('>Buyer>Email')->html(),
                                'Buyer_StaticAlias' => $Transaction->find('>Buyer>StaticAlias')->html(),
                                'Buyer_UserFirstName' => $Transaction->find('>Buyer>UserFirstName')->html(),
                                'Buyer_UserLastName' => $Transaction->find('>Buyer>UserLastName')->html(),
                                'SellingManagerSalesRecordNumber' => $Transaction->find('>ShippingDetails>SellingManagerSalesRecordNumber')->html(),
                                'ShippingDetailsXML' => $Transaction->find('>ShippingDetails')->html(),
                                
                                'ShippingCarrierUsed' => $Transaction->find('>ShippingDetails>ShipmentTrackingDetails>ShippingCarrierUsed')->html(),
                                'ShipmentTrackingNumber' => $Transaction->find('>ShippingDetails>ShipmentTrackingDetails>ShipmentTrackingNumber')->html(),
                                
                                'CreatedDate' => strtotime($Transaction->find('>CreatedDate')->html()),
                                'Item_ItemID' => $Transaction->find('>Item>ItemID')->html(),
                                'Item_Site' => $Transaction->find('>Item>Site')->html(),
                                'Item_Title' => $Transaction->find('>Item>Title')->html(),
                                'Item_SKU' => $Transaction->find('>Item>SKU')->html(),
                                'Item_ConditionID' => $Transaction->find('>Item>ConditionID')->html(),
                                'Item_ConditionDisplayName' => $Transaction->find('>Item>ConditionDisplayName')->html(),
                                
                                'Item_AttributeArrayXML' => $Transaction->find('>Item>AttributeArray')->html(),
                                
                                'QuantityPurchased' => $Transaction->find('>QuantityPurchased')->html(),
                                
                                'PaymentHoldStatus' => $Transaction->find('>Status>PaymentHoldStatus')->html(),
                                'InquiryStatus' => $Transaction->find('>Status>InquiryStatus')->html(),
                                'ReturnStatus' => $Transaction->find('>Status>ReturnStatus')->html(),
                                
                                'TransactionID' => $Transaction->find('>TransactionID')->html(),
                                'TransactionPrice' => $Transaction->find('>TransactionPrice')->html(),
                                'TransactionPrice_currencyID' => $Transaction->find('>TransactionPrice')->attr('currencyID'),
                                'ProductName' => html_entity_decode(html_entity_decode($Transaction->find('>SellingManagerProductDetails>ProductName')->html())),
                                'CustomLabel' => $Transaction->find('>SellingManagerProductDetails>CustomLabel')->html(),
                                
                                // 'EstimatedDeliveryTimeMin' => $Transaction->find('>ShippingServiceSelected>ShippingPackageInfo>EstimatedDeliveryTimeMin')->html(),
                                // 'EstimatedDeliveryTimeMax' => $Transaction->find('>ShippingServiceSelected>ShippingPackageInfo>EstimatedDeliveryTimeMax')->html(),
                                'ShippingServiceSelectedXML' => $Transaction->find('>ShippingServiceSelected')->html(),
                                
                                'FinalValueFee' => $Transaction->find('>FinalValueFee')->html(),
                                'FinalValueFee_currencyID' => $Transaction->find('>FinalValueFee')->attr('currencyID'),
                                'TransactionSiteID' => $Transaction->find('>TransactionSiteID')->html(),
                                'Platform' => $Transaction->find('>Platform')->html(),
                                
                                'TotalTaxAmount' => $Transaction->find('>Taxes>TotalTaxAmount')->html(),
                                'TotalTaxAmount_currencyID' => $Transaction->find('>Taxes>TotalTaxAmount')->attr('currencyID'),
                                'TaxesXML' => $Transaction->find('>Taxes')->html(),
                                
                                'ActualHandlingCost' => $Transaction->find('ActualHandlingCost')->html(),
                                'ActualHandlingCost_currencyID' => $Transaction->find('ActualHandlingCost')->attr('currencyID'),
                                'ActualShippingCost' => $Transaction->find('ActualShippingCost')->html(),
                                'ActualShippingCost_currencyID' => $Transaction->find('ActualShippingCost')->attr('currencyID'),
                                
                                'OrderLineItemID' => $Transaction->find('>OrderLineItemID')->html(),
                                'ExtendedOrderID' => $Transaction->find('>ExtendedOrderID')->html(),
                                
                                'Variation_SKU' => $Transaction->find('>Variation>SKU')->html(),
                                'VariationTitle' => $Transaction->find('>Variation>VariationTitle')->html(),
                                'VariationViewItemURL' => $Transaction->find('>Variation>VariationViewItemURL')->html(),
                                'VariationSpecificsXML' => $Transaction->find('>Variation>VariationSpecifics')->html(),
                                
                                'create_time' => time()
                            );
                            $conditions = 'shop_id=:shop_id and ebay_orders_id=:ebay_orders_id and OrderLineItemID=:OrderLineItemID';
                            $params = array(
                                ':shop_id' => $ordersRow['shop_id'],
                                ':ebay_orders_id' => $orderpk,
                                ':OrderLineItemID' => $Transaction->find('>OrderLineItemID')->html()
                            );
                            
                            if ($params[':OrderLineItemID'] === false) {
                                iMongo::getInstance()->setCollection('OrderLineItemIDEmpty')->insert(array(
                                    'shop_id' => $params[':shop_id'],
                                    'ebay_orders_id' => $params[':ebay_orders_id'],
                                    'OrderLineItemID' => $params[':OrderLineItemID'],
                                    'xml' => $Transaction->html(),
                                    'time' => time()
                                ));
                            }
                            
                            foreach ($params as $key => $value) {
                                if ($params[$key] === false) {
                                    $params[$key] = '';
                                }
                            }
                            
                            foreach ($columns as $key => $value) {
                                if ($value === null || $value === false || $value === 'Invalid Request') {
                                    unset($columns[$key]);
                                }
                            }
                            
                            $_transactionid = EbayOrderTransactionDAO::getInstance()->ireplaceinto($columns, $conditions, $params, true);
                            
                            // 数据异常写日志
                            if (is_array($_transactionid)) {
                                iMongo::getInstance()->setCollection('EbayOrderTransRErr')->insert(array(
                                    'shop_id' => $params[':shop_id'],
                                    'ebay_orders_id' => $params[':ebay_orders_id'],
                                    'OrderLineItemID' => $params[':OrderLineItemID'],
                                    'count' => $_transactionid,
                                    'time' => time()
                                ));
                                imsTool::clearDuplication('EbayOrderTransactionDAO', $_transactionid);
                                $_transactionid = array_shift($_transactionid);
                                $_transactionid = array_shift($_transactionid);
                            }
                            
                            // 写 Transaction TrackingNumber
                            $ShipmentTrackingDetails = $Transaction->find('>ShippingDetails>ShipmentTrackingDetails');
                            $ilength = $ShipmentTrackingDetails->length;
                            for ($ii = 0; $ii < $ilength; $ii ++) {
                                // 'ebay_order_transaction_id' => $_transactionid,
                                $columns = array(
                                    'ProcessOrTrackNo' => $ShipmentTrackingDetails->eq($ii)
                                        ->find('>ShipmentTrackingNumber')
                                        ->html()
                                );
                                if (! empty($columns['ProcessOrTrackNo'])) {
                                    $conditions = 'ProcessOrTrackNo=:ProcessOrTrackNo';
                                    $params = array(
                                        ':ProcessOrTrackNo' => $columns['ProcessOrTrackNo']
                                    );
                                    Ck1TrackingsDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                                    Ck1PackagesDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                                }
                                unset($columns);
                            }
                            unset($ShipmentTrackingDetails);
                            
                            unset($Transaction);
                            unset($columns);
                        }
                        unset($TransactionArray);
                        unset($Order);
                    }
                    
                    $conditions = EbayOrdersDownDAO::getInstance()->igetproperty('primaryKey') . '=:primaryKey';
                    $params = array(
                        ':primaryKey' => $ordersRow[EbayOrdersDownDAO::getInstance()->igetproperty('primaryKey')]
                    );
                    EbayOrdersDownDAO::getInstance()->idelete($conditions, $params);
                    unset($OrderArray);
                } else {
                    iMongo::getInstance()->setCollection('eBayParseOrderXMLFailure')->insert(array(
                        'xml' => $ordersRow['base64data'],
                        'time' => time()
                    ));
                }
                unset($doc);
            }
            
            goto label1;
        } else {
            sleep(5);
            goto label1;
        }
    }
    
    /**
     * @desc 根据ebay userid获取ebay用户信息
     * @param string $userid ebay用户ID
     * @author YangLong
     * @date 2015-06-15
     * @return array
     */
    public function getEbayUserInfo($userid)
    {
        $columns = array(
            'EIASToken',
            'UserID',
            'Email',
            'FeedbackScore',
            'UniqueNegativeFeedbackCount',
            'UniqueNeutralFeedbackCount',
            'UniquePositiveFeedbackCount',
            'PositiveFeedbackPercent',
            'FeedbackRatingStar',
            'RegistrationDate',
            'Site',
            'regaddr_CityName',
            'regaddr_CompanyName',
            'regaddr_Country',
            'regaddr_CountryName',
            'regaddr_Name',
            'regaddr_Phone',
            'regaddr_PostalCode',
            'regaddr_StateOrProvince',
            'regaddr_Street',
            'regaddr_Street1',
            'regaddr_Street2'
        );
        $conditions = 'UserID=:UserID';
        $params = array(
            ':UserID' => $userid
        );
        $result = EbayUserInfoDAO::getInstance()->iselect($columns, $conditions, $params, false);
        if ($result !== false) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
    }
    
    /**
     * @desc 根据OrderID获取相关信息
     * @param string $ItemID ItemID
     * @param string $OrderLineItemID OrderID
     * @author YangLong
     * @date 2015-06-15
     * @return array
     */
    public function getEbayTransactionInfo($ItemID, $OrderLineItemID)
    {
        if (empty($OrderLineItemID) && empty($ItemID)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '');
        }
        $columns = array(
            't.shop_id',
            't.TransactionID',
            't.TransactionPrice',
            't.TransactionPrice_currencyID',
            't.ProductName',
            't.CustomLabel',
            't.EstimatedDeliveryTimeMin',
            't.EstimatedDeliveryTimeMax',
            't.FinalValueFee',
            't.FinalValueFee_currencyID',
            't.TransactionSiteID',
            't.Platform',
            't.TotalTaxAmount',
            't.TotalTaxAmount_currencyID',
            't.TaxesXML',
            't.OrderLineItemID',
            't.ExtendedOrderID',
            't.CreatedDate',
            't.Buyer_Email',
            't.Buyer_StaticAlias',
            't.Buyer_UserFirstName',
            't.Buyer_UserLastName',
            't.SellingManagerSalesRecordNumber',
            't.ShippingDetailsXML',
            't.Item_AttributeArrayXML',
            't.Item_ItemID',
            't.Item_Site',
            't.Item_Title',
            't.Item_SKU',
            't.Item_ConditionID',
            't.Item_ConditionDisplayName',
            't.QuantityPurchased',
            't.PaymentHoldStatus',
            't.InquiryStatus',
            't.ReturnStatus',
            't.Variation_SKU',
            't.VariationViewItemURL',
            't.VariationSpecificsXML',
            't.ActualHandlingCost',
            't.ActualHandlingCost_currencyID',
            't.ActualShippingCost',
            't.ActualShippingCost_currencyID',
            'o.OrderID'
        );
        if (empty($ItemID) && ! empty($OrderLineItemID)) {
            if (strpos($OrderLineItemID, '-')) {
                $conditions = 't.OrderLineItemID=:OrderLineItemID';
                $params = array(
                    ':OrderLineItemID' => $OrderLineItemID
                );
            } else {
                $conditions = 'o.OrderID=:OrderID';
                $params = array(
                    ':OrderID' => $OrderLineItemID
                );
            }
        } elseif (! empty($ItemID) && empty($OrderLineItemID)) {
            $conditions = 't.Item_ItemID=:Item_ItemID';
            $params = array(
                ':Item_ItemID' => $ItemID
            );
        } else {
            $conditions = 't.Item_ItemID=:Item_ItemID and t.OrderLineItemID=:OrderLineItemID';
            $params = array(
                ':Item_ItemID' => $ItemID,
                ':TransactionID' => $OrderLineItemID
            );
        }
        $joinArray = array(
            array(
                EbayOrdersDAO::getInstance()->getTableName() . ' o',
                't.ebay_orders_id=o.ebay_orders_id'
            )
        );
        $result = EbayOrderTransactionDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 't');
        if ($result !== false) {
            foreach ($result as $_key => $_val) {
                $result[$_key]['TaxesXML'] = XML2Array::createArray('<xml>' . $result[$_key]['TaxesXML'] . '</xml>');
                $result[$_key]['Item_AttributeArrayXML'] = XML2Array::createArray('<xml>' . $result[$_key]['Item_AttributeArrayXML'] . '</xml>');
                $result[$_key]['ShippingDetailsXML'] = XML2Array::createArray('<xml>' . $result[$_key]['ShippingDetailsXML'] . '</xml>');
                $result[$_key]['VariationSpecificsXML'] = XML2Array::createArray('<xml>' . $result[$_key]['VariationSpecificsXML'] . '</xml>');
                
                // guest ItemID hide
                if (Yii::app()->session['userInfo']['user_id'] == 99999) {
                    $result[$_key]['Item_ItemID'] = preg_replace('/(\d{8})\d{4}/', '$1****', $result[$_key]['Item_ItemID']);
                    $result[$_key]['Buyer_Email'] = preg_replace('/.{4}(.*)/', '****$1', $result[$_key]['Buyer_Email']);
                    $result[$_key]['Buyer_StaticAlias'] = preg_replace('/.{4}(.*)/', '****$1', $result[$_key]['Buyer_StaticAlias']);
                    $result[$_key]['user_id'] = 99999;
                }
            }
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
    }
    
    /**
     * @desc 根据订单号获取相关信息
     * @param string $OrderLineItemID
     * @author YangLong
     * @date 2015-06-16
     * @return array
     */
    public function getEbayOrderInfo($OrderLineItemID)
    {
        if (empty($OrderLineItemID)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '');
        }
        
        $columns = array(
            'o.ebay_orders_id',
            'o.OrderID',
            'o.OrderStatus',
            'o.AdjustmentAmount',
            'o.AdjustmentAmount_currencyID',
            'o.AmountPaid',
            'o.AmountPaid_currencyID',
            'o.AmountSaved',
            'o.AmountSaved_currencyID',
            'o.eBayPaymentStatus',
            'o.LastModifiedTime',
            'o.PaymentMethod',
            'o.Status',
            'o.ShippingDetailsXML',
            'o.CreatedTime',
            'o.SellerEmail',
//             'o.ShippingAddressXML',
            'o.ShippingService',
            'o.ShippingServiceCost',
            'o.ShippingServiceCost_currencyID',
            'o.Subtotal',
            'o.Subtotal_currencyID',
            'o.Total',
            'o.Total_currencyID',
            'o.BuyerUserID',
            'o.BuyerCheckoutMessage',
            'o.PaidTime',
            'o.ShippedTime',
            'o.EIASToken',
            'o.PaymentHoldStatus',
            'o.SellerUserID',
            'o.CancelStatus',
            'o.ExtendedOrderID',
            'a.AddressID',
            'a.AddressOwner',
            'a.CityName',
            'a.Country',
            'a.CountryName',
            'a.ExternalAddressID',
            'a.Name',
            'a.Phone',
            'a.PostalCode',
            'a.StateOrProvince',
            'a.Street1',
            'a.Street2',
            'a.AddressAttributeXML'
        );
        $conditions = 'o.OrderID=:OrderID';
        $params = array(
            ':OrderID' => $OrderLineItemID
        );
        $joinArray = array(
            array(
                EbayUserAddressDAO::getInstance()->getTableName() . ' a',
                'o.AddressID=a.AddressID',
                'left' => true
            )
        );
        $result = EbayOrdersDAO::getInstance()->iselect($columns, $conditions, $params, false, $joinArray, 'o');
        if ($result !== false) {
            $columns = array(
                'ExternalTransactionID',
                'ExternalTransactionTime',
                'FeeOrCreditAmount',
                'FeeOrCreditAmount_currencyID',
                'PaymentOrRefundAmount',
                'PaymentOrRefundAmount_currencyID',
                'ExternalTransactionStatus'
            );
            $conditions = 'ebay_orders_id=:ebay_orders_id';
            $params = array(
                ':ebay_orders_id' => $result['ebay_orders_id']
            );
            $result['ExtTrans'] = EbayOrderExtTransDAO::getInstance()->iselect($columns, $conditions, $params);
            
            $columns = array(
                'TransactionID',
                'TransactionPrice',
                'TransactionPrice_currencyID',
                'ProductName',
                'ShippingServiceSelectedXML',
                'ShippingDetailsXML',
                'Buyer_Email',
                'Buyer_StaticAlias'
            );
            $conditions = 'ebay_orders_id=:ebay_orders_id';
            $params = array(
                ':ebay_orders_id' => $result['ebay_orders_id']
            );
            $result['Trans'] = EbayOrderTransactionDAO::getInstance()->iselect($columns, $conditions, $params);
            
            $columns = array(
                'Description'
            );
            $conditions = 'ShippingService=:ShippingService';
            $params = array(
                ':ShippingService' => $result['ShippingService']
            );
            $result['ShippingServiceDetails'] = EbayShippingServiceDetailsDAO::getInstance()->iselect($columns, $conditions, $params, false);
            
            foreach ($result['Trans'] as $_key => $_val) {
                $result['Trans'][$_key]['ShippingServiceSelectedXML'] = XML2Array::createArray('<xml>' . $result['Trans'][$_key]['ShippingServiceSelectedXML'] . '</xml>');
                if (! empty($result['Trans'][$_key]['ShippingDetailsXML'])) {
                    $result['Trans'][$_key]['ShippingDetailsXML'] = XML2Array::createArray('<xml>' . $result['Trans'][$_key]['ShippingDetailsXML'] . '</xml>');
                }
                // guest email hide
                if (Yii::app()->session['userInfo']['user_id'] == 99999) {
                    if (isset($result['Trans'][$_key]['ShippingDetailsXML']['xml']['ShipmentTrackingDetails']['ShipmentTrackingNumber'])) {
                        $result['Trans'][$_key]['ShippingDetailsXML']['xml']['ShipmentTrackingDetails']['ShipmentTrackingNumber'] = preg_replace('/(.*).{4}/', '$1****', 
                            $result['Trans'][$_key]['ShippingDetailsXML']['xml']['ShipmentTrackingDetails']['ShipmentTrackingNumber']);
                    }
                }
            }
            
//             $result['ShippingAddressXML'] = XML2Array::createArray('<xml>' . $result['ShippingAddressXML'] . '</xml>');
            if (! empty($result['ShippingDetailsXML'])) {
                $result['ShippingDetailsXML'] = XML2Array::createArray('<xml>' . $result['ShippingDetailsXML'] . '</xml>');
            }
            
            // guest email hide
            if (Yii::app()->session['userInfo']['user_id'] == 99999) {
                $result['BuyerUserID'] = preg_replace('/.{4}(.*)/', '****$1', $result['BuyerUserID']);
                $result['SellerEmail'] = preg_replace('/.{4}(.*)/', '****$1', $result['SellerEmail']);
                $result['Street1'] = preg_replace('/.{4}(.*)/', '****$1', $result['Street1']);
                $result['Street2'] = preg_replace('/.{4}(.*)/', '****$1', $result['Street2']);
                $result['PostalCode'] = preg_replace('/.{4}(.*)/', '****$1', $result['PostalCode']);
                $result['Phone'] = preg_replace('/.{4}(.*)/', '****$1', $result['Phone']);
                $result['user_id'] = 99999;
            }
            
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
    }
    
    /**
     * @desc 根据UserId获取orders
     * @param string $BuyerUserID
     * @author YangLong
     * @date 2015-06-16
     * @return array
     */
    public function getEbayOrdersByUserId($BuyerUserID)
    {
        $columns = array(
            'OrderID',
            'OrderStatus',
            'AdjustmentAmount',
            'AdjustmentAmount_currencyID',
            'AmountPaid',
            'AmountPaid_currencyID',
            'AmountSaved',
            'AmountSaved_currencyID',
            'eBayPaymentStatus',
            'LastModifiedTime',
            'PaymentMethod',
            'Status',
            'CreatedTime',
            'SellerEmail',
//             'ShippingAddressXML',
            'ShippingService',
            'ShippingServiceCost',
            'ShippingServiceCost_currencyID',
            'Subtotal',
            'Subtotal_currencyID',
            'Total',
            'Total_currencyID',
            'BuyerUserID',
            'BuyerCheckoutMessage',
            'PaidTime',
            'ShippedTime',
            'PaymentHoldStatus',
            'SellerUserID',
            'CancelStatus',
            'ExtendedOrderID'
        );
        $conditions = 'BuyerUserID=:BuyerUserID';
        $params = array(
            ':BuyerUserID' => $BuyerUserID
        );
        $result = EbayOrdersDAO::getInstance()->iselect($columns, $conditions, $params, true);
        if ($result !== false) {
            $columns = array(
                'ExternalTransactionID',
                'ExternalTransactionTime',
                'FeeOrCreditAmount',
                'FeeOrCreditAmount_currencyID',
                'PaymentOrRefundAmount',
                'PaymentOrRefundAmount_currencyID',
                'ExternalTransactionStatus'
            );
            $conditions = 'ebay_orders_id=:ebay_orders_id';
            $params = array(
                ':ebay_orders_id' => $result['ebay_orders_id']
            );
            $result['ExtTrans'] = EbayOrderExtTransDAO::getInstance()->iselect($columns, $conditions, $params);
            
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
    }
    
    /**
     * @desc 根据UserId获取orders
     * @param string $BuyerUserID
     * @param string $EIASToken
     * @author YangLong
     * @date 2015-06-16
     * @return array
     */
    public function getEbayTransactionsByUserId($BuyerUserID, $EIASToken)
    {
        if (empty($BuyerUserID) && empty($EIASToken)) {
            return false;
        }
        $columns = array(
            'o.ebay_orders_id',
            'o.BuyerUserID',
            'o.SellerUserID',
            'o.PaymentMethod',
            'o.OrderStatus',
            't.shop_id',
            't.ebay_orders_id',
            't.TransactionID',
            't.TransactionPrice',
            't.TransactionPrice_currencyID',
            't.ProductName',
            't.CustomLabel',
            't.EstimatedDeliveryTimeMin',
            't.EstimatedDeliveryTimeMax',
            't.FinalValueFee',
            't.FinalValueFee_currencyID',
            't.TransactionSiteID',
            't.Platform',
            't.TotalTaxAmount',
            't.TotalTaxAmount_currencyID',
            't.TaxesXML',
            't.OrderLineItemID',
            't.ExtendedOrderID',
            't.CreatedDate',
            't.Buyer_Email',
            't.Buyer_StaticAlias',
            't.Buyer_UserFirstName',
            't.Buyer_UserLastName',
            't.SellingManagerSalesRecordNumber',
            't.Item_AttributeArrayXML',
            't.Item_ItemID',
            't.Item_Site',
            't.Item_Title',
            't.Item_SKU',
            't.Item_ConditionID',
            't.Item_ConditionDisplayName',
            't.QuantityPurchased',
            't.PaymentHoldStatus',
            't.InquiryStatus',
            't.ReturnStatus',
            't.Variation_SKU',
            't.VariationViewItemURL',
            't.VariationSpecificsXML',
            't.ActualHandlingCost',
            't.ActualHandlingCost_currencyID',
            't.ActualShippingCost',
            't.ActualShippingCost_currencyID',
            'f.CommentType',
            'a.AddressID',
            'a.AddressOwner',
            'a.CityName',
            'a.Country',
            'a.CountryName',
            'a.ExternalAddressID',
            'a.Name',
            'a.Phone',
            'a.PostalCode',
            'a.StateOrProvince',
            'a.Street1',
            'a.Street2',
            'a.AddressAttributeXML',
            's.nick_name',
        );
        if (! empty($EIASToken)) {
            $conditions = 'o.EIASToken=:EIASToken';
            $params = array(
                ':EIASToken' => $EIASToken
            );
        } else {
            $conditions = 'o.BuyerUserID=:BuyerUserID';
            $params = array(
                ':BuyerUserID' => $BuyerUserID
            );
        }
        $joinArray = array(
            array(
                EbayOrdersDAO::getInstance()->igetproperty('tableName') . ' o',
                't.ebay_orders_id = o.ebay_orders_id'
            ),
            array(
                ShopDAO::getInstance()->igetproperty('tableName') . ' s',
                's.shop_id = o.shop_id and s.is_delete=0'
            ),
            array(
                EbayFeedbackTransactionDAO::getInstance()->igetproperty('tableName') . ' f',
                't.OrderLineItemID = f.OrderLineItemID',
                'left' => true
            ),
            array(
                EbayUserAddressDAO::getInstance()->igetproperty('tableName') . ' a',
                'o.AddressID = a.AddressID',
                'left' => true
            )
        );
        $result = EbayOrderTransactionDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 't');
        if ($result !== false) {
            foreach ($result as $_key => $_value) {
                $columns = array(
                    'ExternalTransactionID',
                    'ExternalTransactionTime',
                    'FeeOrCreditAmount',
                    'FeeOrCreditAmount_currencyID',
                    'PaymentOrRefundAmount',
                    'PaymentOrRefundAmount_currencyID',
                    'ExternalTransactionStatus'
                );
                $conditions = 'ebay_orders_id=:ebay_orders_id';
                $params = array(
                    ':ebay_orders_id' => $result[$_key]['ebay_orders_id']
                );
                $result[$_key]['ExtTrans'] = EbayOrderExtTransDAO::getInstance()->iselect($columns, $conditions, $params);
                
                $result[$_key]['TaxesXML'] = XML2Array::createArray('<xml>' . $result[$_key]['TaxesXML'] . '</xml>');
                $result[$_key]['VariationSpecificsXML'] = XML2Array::createArray('<xml>' . $result[$_key]['VariationSpecificsXML'] . '</xml>');
                
                $result[$_key]['SellerUserID'] = $result[$_key]['nick_name'];
                
                // guest ItemID hide
                if (Yii::app()->session['userInfo']['user_id'] == 99999) {
                    $result[$_key]['Item_ItemID'] = preg_replace('/(\d{8})\d{4}/', '$1****', $result[$_key]['Item_ItemID']);
                    
                    $result[$_key]['Buyer_Email'] = preg_replace('/.{4}(.*)/', '****$1', $result[$_key]['Buyer_Email']);
                    $result[$_key]['Buyer_StaticAlias'] = preg_replace('/.{4}(.*)/', '****$1', $result[$_key]['Buyer_StaticAlias']);
                    $result[$_key]['BuyerUserID'] = preg_replace('/.{4}(.*)/', '****$1', $result[$_key]['BuyerUserID']);
                    $result[$_key]['Street1'] = preg_replace('/.{4}(.*)/', '****$1', $result[$_key]['Street1']);
                    $result[$_key]['Street2'] = preg_replace('/.{4}(.*)/', '****$1', $result[$_key]['Street2']);
                    $result[$_key]['PostalCode'] = preg_replace('/.{4}(.*)/', '****$1', $result[$_key]['PostalCode']);
                    $result[$_key]['Phone'] = preg_replace('/.{4}(.*)/', '****$1', $result[$_key]['Phone']);
                    $result[$_key]['user_id'] = 99999;
                }
            }
            
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
    }
    
    /**
     * @desc 获取并更新物流服务枚举信息
     * @author YangLong
     * @date 2015-07-23
     * @return null
     */
    public function geteBayDetails()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $columns = array(
            'token'
        );
        $conditions = 'is_delete=:is_delete';
        $params = array(
            ':is_delete' => boolConvert::toInt01(false)
        );
        $token = ShopDAO::getInstance()->iselect($columns, $conditions, $params, false);
        
        $DetailName = 'SiteDetails';
        $xmlSite = $this->eBayGeteBayDetails($token['token'], $DetailName);
        
        $docSite = phpQuery::newDocumentXML($xmlSite);
        phpQuery::selectDocument($docSite);
        
        $SiteDetails = pq('GeteBayDetailsResponse>SiteDetails');
        $lengthSite = $SiteDetails->length;
        
        for ($iS = 0; $iS < $lengthSite; $iS ++) {
            $SiteDetail = $SiteDetails->eq($iS);
            
            $siteId = $SiteDetail->find('SiteID')->html();
            
            $DetailName = 'ShippingServiceDetails';
            $xml = $this->eBayGeteBayDetails($token['token'], $DetailName, $siteId);
            
            $doc = phpQuery::newDocumentXML($xml);
            phpQuery::selectDocument($doc);
            
            $ShippingServiceDetails = pq('GeteBayDetailsResponse>ShippingServiceDetails');
            $length = $ShippingServiceDetails->length;
            for ($i = 0; $i < $length; $i ++) {
                $ShippingServiceDetail = $ShippingServiceDetails->eq($i);
                
                $columns = array(
                    'site_id' => $siteId,
                    'ShippingService' => $ShippingServiceDetail->find('>ShippingService')->html(),
                    'Description' => $ShippingServiceDetail->find('>Description')->html(),
                    'ShippingCarrier' => $ShippingServiceDetail->find('>ShippingCarrier')->html(),
                    'UpdateTime' => strtotime($ShippingServiceDetail->find('>UpdateTime')->html()),
                    'InternationalService' => boolConvert::toStr01($ShippingServiceDetail->find('>InternationalService')->html()),
                    'ValidForSellingFlow' => boolConvert::toStr01($ShippingServiceDetail->find('>ValidForSellingFlow')->html()),
                    'create_time' => time()
                );
                foreach ($columns as $_key => $_value) {
                    if ($columns[$_key] === null || $columns[$_key] === false) {
                        unset($columns[$_key]);
                    }
                }
                $conditions = 'site_id=:site_id and ShippingService=:ShippingService';
                $params = array(
                    ':site_id' => $siteId,
                    ':ShippingService' => $ShippingServiceDetail->find('>ShippingService')->html()
                );
                if ($ShippingServiceDetail->find('>ShippingService')->html() === false) {
                    continue;
                }
                EbayShippingServiceDetailsDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
            }
        }
    }
    
    /**
     * @desc 根据ItemID获取备注信息
     * @param unknown $itemId
     * @author YangLong
     * @date 2015-07-28
     * @return mixed
     */
    public function getItemNotes($itemId)
    {
        if (empty($itemId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
        
        $columns = array(
            'author_name',
            'cust',
            'text',
            'create_time'
        );
        $conditions = 'item_id=:item_id';
        $params = array(
            ':item_id' => $itemId
        );
        $result = ItemNoteDAO::getInstance()->iselect($columns, $conditions, $params);
        
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING, $result, 'not note found.');
        } else {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        }
    }
    
    /**
     * @desc 获取用户地址信息
     * @param string $userId
     * @param string $EIASToken
     * @date 2015-07-30
     * @return mixed
     */
    public function getUserAddress($userId, $EIASToken)
    {
        if (empty($userId) && empty($EIASToken)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'input data err.');
        }
        
        $columns = array(
            'AddressID',
            'AddressOwner',
            'CityName',
            'Country',
            'CountryName',
            'ExternalAddressID',
            'Name',
            'Phone',
            'PostalCode',
            'StateOrProvince',
            'Street1',
            'Street2',
            'AddressAttributeXML'
        );
        if (empty($EIASToken)) {
            $fname = 'user_id';
        } else {
            $fname = 'EIASToken';
        }
        $conditions = "{$fname}=:{$fname}";
        $params = array(
            ":{$fname}" => (empty($EIASToken) ? $userId : $EIASToken)
        );
        $result = EbayUserAddressDAO::getInstance()->iselect($columns, $conditions, $params);
        
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        }
    }
    
    /**
     * @desc 根据ExtTransID获取地址
     * @param string $TransactionID
     * @param string $BuyerID
     * @author YangLong
     * @date 2015-08-07
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function getPaypalAddressByExtTransID($TransactionID, $BuyerID)
    {
        if (empty($TransactionID) && empty($BuyerID)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
        
        if (empty($TransactionID)) {
            $url = Yii::app()->params['ecs_api_url'] . '?r=api/Order/GetPaypalShippingAddress&BuyerID=' . $BuyerID;
        } else {
            $url = Yii::app()->params['ecs_api_url'] . '?r=api/Order/GetPaypalShippingAddress&TransactionID=' . $TransactionID;
        }
        
        $result = getByCurl::get($url);
        $result = json_decode($result, true);
        if (empty($result)) {
            // 异常发邮件
            $subject = "ECS PAYPAL API 调用异常";
            ob_start();
            var_dump($result);
            var_dump($TransactionID);
            var_dump($BuyerID);
            $text = ob_get_clean();
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            
            return $this->handleApiFormat(EnumOther::ACK_WARNING, $result);
        } else {
            if (isset($result['data']['body']) && is_array($result['data']['body'])) {
                foreach ($result['data']['body'] as $key => &$value) {
                    // guest email hide
                    if (Yii::app()->session['userInfo']['user_id'] == 99999) {
                        $value['BuyerEmail'] = preg_replace('/.{4}(.*)/', '****$1', $value['BuyerEmail']);
                        $value['BuyerID'] = preg_replace('/.{4}(.*)/', '****$1', $value['BuyerID']);
                        $value['Street1'] = preg_replace('/.{4}(.*)/', '****$1', $value['Street1']);
                        $value['Street2'] = preg_replace('/.{4}(.*)/', '****$1', $value['Street2']);
                        $value['PostCode'] = preg_replace('/.{4}(.*)/', '****$1', $value['PostCode']);
                        $value['Phone'] = preg_replace('/.{4}(.*)/', '****$1', $value['Phone']);
                        $value['user_id'] = 99999;
                    }
                }
                unset($value);
            }
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        }
    }

    /**
     * @desc 获取eBay用户的注册地址
     * @param string $userId
     * @author YangLong
     * @date 2015-08-11
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function getUserRegAddress($userId)
    {
        if (empty($userId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
        
        $columns = array(
            'regaddr_CityName',
            'regaddr_CompanyName',
            'regaddr_Country',
            'regaddr_CountryName',
            'regaddr_Name',
            'regaddr_Phone',
            'regaddr_PostalCode',
            'regaddr_StateOrProvince',
            'regaddr_Street',
            'regaddr_Street1',
            'regaddr_Street2'
        );
        $conditions = 'UserID=:UserID';
        $params = array(
            ':UserID' => $userId
        );
        $result = EbayUserInfoDAO::getInstance()->iselect($columns, $conditions, $params, false);
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        }
    }
    
    /**
     * @desc 获取eBay Item的listting状态
     * @param string $itemId
     * @author YangLong
     * @date 2015-08-14
     * @return mixed
     */
    public function getItemStatus($itemId)
    {
        if (empty($itemId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
        
        $columns = array(
            'listing_status',
            'end_time'
        );
        $conditions = 'item_id=:item_id';
        $params = array(
            ':item_id' => $itemId
        );
        $result = EbayListingDAO::getInstance()->iselect($columns, $conditions, $params, false);
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        }
    }
    
    /**
     * @desc 获取国家列表
     * @author liaojianwen
     * @date 2015-04-22
     * @modify YangLong 2015-09-20 move to model
     * @return mixed
     */
    public function getCountryList()
    {
        $result = EnumCountry::$countryNameEn;
        if ($result) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '读取国家列表失败');
        }
    }
    
    /**
     * @desc 获取格式化的GMT时间
     * @param int $date
     * @author YangLong
     * @date 2015-02-12
     * @return string
     */
    private function fmtDate($date)
    {
        return gmdate('Y-m-d\TH:i:s\Z',$date);
    }
    
    /**
     * @desc 获取用户信息
     * @param string $token
     * @param int $siteid
     * @author YangLong
     * @date 2015-10-15
     * @return string XML
     */
    public function eBayGetAccount($token, $siteid = 0)
    {
        $callName = 'GetAccount';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<GetAccountRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>' . $token . '</eBayAuthToken>
  </RequesterCredentials>
</GetAccountRequest>';
        
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/GetUser.html
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/index.html#Limitations
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:941";
        $session->headers[] = "X-EBAY-API-DEV-NAME:{$this->devID}";
        $session->headers[] = "X-EBAY-API-APP-NAME:{$this->appID}";
        $session->headers[] = "X-EBAY-API-CERT-NAME:{$this->certID}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        $session->headers[] = "X-EBAY-API-SITEID:{$siteid}";
        
        $tryCount = 0;
        
        label1:
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('eBayGetAccountFailure')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'time' => time(),
                'times' => 1
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('eBayGetAccountFailure')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'time' => time(),
                'times' => 2
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (! XMLTool::IsXML($responseXml)) {
            iMongo::getInstance()->setCollection('eBayGetAccountBadXML')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'tryCount' => $tryCount,
                'time' => time()
            ));
            if ($tryCount < 10) {
                $tryCount ++;
                goto label1;
            }
            return false;
        }
        
        iMongo::getInstance()->setCollection('eBayGetAccount')->insert(array(
            'requestXmlBody' => $requestXmlBody,
            'responseXml' => $responseXml,
            'tryCount' => $tryCount,
            'time' => time()
        ));
        
        return $responseXml;
    }
    
    /**
     * @desc 语义分析消息内容读取接口
     * @param string $ModTimeFrom
     * @param string $ModTimeTo
     * @param string $page
     * @param string $pageSize
     * @author YangLong
     * @date 2015-10-29
     * @return mixed
     */
    public function getMsgEffectText($ModTimeFrom, $ModTimeTo, $page, $pageSize)
    {
        if (empty($ModTimeFrom) || empty($ModTimeTo)) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING, '', 'ModTimeFrom and ModTimeTo cannot empty.');
        }
        
        $ModTimeFrom = strtotime($ModTimeFrom);
        $ModTimeTo = strtotime($ModTimeTo);
        if ($ModTimeFrom === false) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING, '', 'Invalid ModTimeFrom.');
        }
        if ($ModTimeFrom === false) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING, '', 'Invalid ModTimeTo.');
        }
        
        $columns = array(
            'ItemUrl',
            'TitleInfo',
            'effect_content'
        );
        $conditions = 'create_time>=:ModTimeFrom and create_time<=:ModTimeTo';
        $params = array(
            ':ModTimeFrom' => $ModTimeFrom,
            ':ModTimeTo' => $ModTimeTo
        );
        $limit = $pageSize;
        $offset = ($page - 1) * $pageSize;
        $result = MsgTextResolveDAO::getInstance()->iselect($columns, $conditions, $params, true, array(), '', '', $limit, $offset);
        if ($result === false) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        } else {
            /*
             * foreach ($result as $key => &$value) {
             * $value['effect_content'] = strip_tags($value['effect_content'], '<br>');
             * }
             * unset($value);
             */
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        }
    }
    
    /**
     * @desc 发送统计信息
     * @author YangLong
     * @date 2015-12-02
     * @return null
     */
    public function sendTongJi()
    {
        $subject = "ims 统计信息";
        ob_start();
        
        echo 'Seller 数量 （注册用户） :';
        $columns = array(
            'count(*)'
        );
        $conditions = 'pid=:pid';
        $params = array(
            ':pid' => 1
        );
        $sellers = UserDAO::getInstance()->iselect($columns, $conditions, $params, 'queryScalar');
        echo "{$sellers}\n";
        
        echo 'Account 数量（Token） :';
        $columns = array(
            'count(*)'
        );
        $conditions = 'is_delete=:false';
        $params = array(
            ':false' => boolConvert::toStr01(false)
        );
        $tokens = ShopDAO::getInstance()->iselect($columns, $conditions, $params, 'queryScalar');
        echo "{$tokens}\n";
        
        echo 'Message IN （下载量）（24h内）:';
        $columns = array(
            'count(*)'
        );
        $conditions = 'ReceiveDate>:ReceiveDate';
        $params = array(
            ':ReceiveDate' => time() - 3600 * 24
        );
        $msgins = MsgDAO::getInstance()->iselect($columns, $conditions, $params, 'queryScalar');
        echo "{$msgins}\n";
        
        echo 'Message OUT （通过我们系统的回复量）（24h内）:';
        $columns = array(
            'count(*)'
        );
        $conditions = 'create_time>:create_time';
        $params = array(
            ':create_time' => time() - 3600 * 24
        );
        $msgouts = MsgReplyLogDAO::getInstance()->iselect($columns, $conditions, $params, 'queryScalar');
        echo "{$msgouts}\n";
        
        echo 'Message OUT （通过我们系统的发送量）（24h内）:';
        $columns = array(
            'count(*)'
        );
        $conditions = 'create_time>:create_time';
        $params = array(
            ':create_time' => time() - 3600 * 24
        );
        $msgouts = MsgCreateLogDAO::getInstance()->iselect($columns, $conditions, $params, 'queryScalar');
        echo "{$msgouts}\n";
        
        $text = ob_get_clean();
        $to = Yii::app()->params['tongji'];
        SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
    }
    
}
