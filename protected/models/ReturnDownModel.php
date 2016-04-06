<?php

/**
 * @desc ReturnDownModel
 * @author YangLong
 * @date 2015-06-12
 */
class ReturnDownModel extends BaseModel
{

    private $compatabilityLevel;
 // eBay API version
    private $devID;

    private $appID;

    private $certID;

    private $serverUrl;
 // eBay 服务器地址
    private $userToken;
 // token
    private $siteToUseID;
 // site id
    
    /**
     * @desc 返回当前类的实例
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-06-12
     * @return ReturnDownModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-06-12
     */
    public function __construct()
    {
        $this->compatabilityLevel = '1.1.0'; // eBay API version
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
     * @desc 获取已经下载的Returns数据
     * @param int $taskNumber
     * @author liaojianwen
     * @date 2015-06-16
     * @return Ambigous <string, multitype:, mixed>|boolean
     */
    public function getDownloadData($taskNumber)
    {
        ReturnDownDAO::getInstance()->begintransaction();
        try {
            // 获取符合条件的数据
            $result = ReturnDownDAO::getInstance()->getDownloadData($taskNumber);
            
            if (empty($result)) {
                ReturnDownDAO::getInstance()->rollback();
                return false;
            }
            
            // 拼接ID数组
            $_ids = array();
            foreach ($result as $key => $value) {
                $_ids[] = $value['down_id'];
            }
            $_ids = implode(',', $_ids);
            
            $columns = array(
                'status' => boolConvert::toInt01(true),
                'lastruntime' => time()
            );
            $conditions = "down_id in ({$_ids})";
            ReturnDownDAO::getInstance()->iupdate($columns, $conditions, array()); // 标记为正在处理
            
            ReturnDownDAO::getInstance()->increase('runcount', "down_id in ({$_ids})"); // 运行次数+1
            
            ReturnDownDAO::getInstance()->commit();
            return $result;
        } catch (Exception $e) {
            ReturnDownDAO::getInstance()->rollback();
            return false;
        }
    }

    /**
     * @desc 删除已经处理了的return原始数据 
     * @param string $ids            
     * @author liaojianwen
     * @date 2015-06-16
     * @return Ambigous <boolean, number>
     */
    public function deleteReturnDownData($ids)
    {
        return ReturnDownDAO::getInstance()->deleteByIds($ids);
    }

    /**
     * @desc 生成Return_request下载队列
     * @author liaojianwen
     * @date 2015-06-14
     * @return boolean
     */
    public function generateReturnDownQueue()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $shops = MsgDownDAO::getInstance()->getEbShop('return');
        ReturnDownQueueDAO::getInstance()->begintransaction();
        try {
            foreach ($shops as $key => $shop) {
                $_time = time();
                if (empty($shop['return_down_time'])) {
                    $shop['return_down_time'] = $_time - EnumOther::RETURN_MAX_DOWNLOAD_DATE;
                    for ($i = 0; $i < 19; $i ++) {
                        $fromDate = $_time - ($i + 1) * 30 * 24 * 3600;
                        $toDate = $_time - $i * 30 * 24 * 3600;
                        if ($toDate < $shop['return_down_time']) {
                            break;
                        }
                        $this->makeReturnDownQueue($fromDate, $toDate, $shop, 20 - $i);
                    }
                } elseif ($_time - $shop['return_down_time'] > 50) {
                    $fromDate = $shop['return_down_time'] - EnumOther::OVARLAP_TIME;
                    $toDate = $_time;
                    $this->makeReturnDownQueue($fromDate, $toDate, $shop, 25);
                }
                
                $columns = array(
                    'return_down_time' => $_time
                );
                $conditions = 'shop_id=:shop_id';
                $params = array(
                    ':shop_id' => $shop['shop_id']
                );
                ShopDAO::getInstance()->iupdate($columns, $conditions, $params);
            }
            
            ReturnDownQueueDAO::getInstance()->commit();
            return true;
        } catch (Exception $e) {
            ReturnDownQueueDAO::getInstance()->rollback();
            throw new Exception('generateReturnDownQueue error.');
            return false;
        }
    }

    /**
     * @desc 生成Return_request下载队列
     * @param int $fromDate
     * @param int $toDate
     * @param array $shop
     * @param int $priority
     * @author liaojianwen
     * @date 2015-06-14
     * @return Ambigous <mixed, boolean, string>
     */
    private function makeReturnDownQueue($fromDate, $toDate, $shop, $priority)
    {
        $params = array(
            'seller_id' => $shop['seller_id'],
            'shop_id' => $shop['shop_id'],
            'AccountID' => $shop['AccountID'],
            'site_id' => $shop['site_id'],
            'token' => $shop['token'],
            'start_time' => $fromDate,
            'end_time' => $toDate,
            'priority' => $priority
        );
        return ReturnDownQueueDAO::getInstance()->insert($params);
    }

    /**
     * @desc 运行下载队列
     * @author liaojianwen
     * @date 2015-06-14
     * @return boolean
     */
    public function executeReturnDownQueue()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $startTime = time();
        
        label1:
        
        if (time() - $startTime > 595) {
            return false;
        }
        
        $pagesize = 200;
        $Queues = ReturnDownQueueDAO::getInstance()->getDownQueueData(EnumOther::RETURN_EXECUTESIZE);
        if ($Queues !== false) {
            foreach ($Queues as $key => $Queue) {
                $page = 0;
                while (true) {
                    $page ++;
                    
                    $xmldata = array();
                    $xmldata['Returns'] = $this->getUserReturns($Queue['start_time'], $Queue['end_time'], '', '', '', $Queue['token'], 
                        $Queue['site_id'], $page, $pagesize);
                    $doc = phpQuery::newDocumentXML($xmldata['Returns']);
                    phpQuery::selectDocument($doc);
                    if (pq('ack') == 'Failure') {
                        continue 2;
                    }
                    
                    $length = pq('returns')->length;
                    
                    if (! $length) {
                        ReturnDownQueueDAO::getInstance()->deleteByPk($Queue['return_request_queue_id']);
                        break;
                    }
                    
                    for ($i = 0; $i < $length; $i ++) {
                        $return_id = pq('returns')->eq($i)
                            ->find('ReturnId>id')
                            ->html();
                        if ($return_id !== false) {
                            $runcount = 0;
                            label:
                            $xmldata['ReturnDetail'][$return_id] = $this->getReturnDetailInfo($return_id, $Queue['token']);
                            if (empty($xmldata['ReturnDetail'][$return_id])) {
                                $runcount ++;
                                if ($runcount > 3) {
                                    goto label2;
                                }
                                goto label;
                            }
                            label2:
                            // $xmldata['ActivityOptions'][$return_id] = $this->getActivityOptions($return_id, $Queue['token'], $Queue['site_id']);
                            // $xmldata['FileData'][$return_id] = $this->getFileData($return_id, $Queue['token']);
                            FileLog::getInstance()->write(
                                EnumOther::LOG_DIR_RETURN_TEMP_FILE_DATA . gmdate('/Y/m/d/') . EnumOther::LOG_DIR_RETURN_TEMP_DOWN_TAG, 
                                md5($return_id), $this->getFileData($return_id, $Queue['token']));
                            $xmldata['FileData'][$return_id] = gmdate('/Y/m/d/') . EnumOther::LOG_DIR_RETURN_TEMP_DOWN_TAG;
                        }
                    }
                    $columns = array(
                        'seller_id' => $Queue['seller_id'],
                        'shop_id' => $Queue['shop_id'],
                        'AccountID' => $Queue['AccountID'],
                        'text_json' => base64_encode(serialize($xmldata)),
                        'create_time' => time()
                    );
                    ReturnDownDAO::getInstance()->begintransaction();
                    try {
                        $lid = ReturnDownDAO::getInstance()->iinsert($columns);
                        if ($lid !== false) {
                            ReturnDownQueueDAO::getInstance()->deleteByPk($Queue['return_request_queue_id']);
                            ReturnDownDAO::getInstance()->commit();
                        } else {
                            ReturnDownDAO::getInstance()->rollback();
                        }
                    } catch (Exception $e) {
                        ReturnDownDAO::getInstance()->rollback();
                    }
                    if ($page >= pq('paginationOutput>totalPages')->html()) {
                        continue 2;
                    }
                    if (pq('errorMessage>error>errorId') > 0) {
                        // file_put_contents('getUserReturnsErr.log', "{$Queue['return_request_queue_id']}:\n{$doc}\n\n", FILE_APPEND);
                        iMongo::getInstance()->setCollection('getUserRetrunsErrA')->insert(
                            array(
                                'xml' => $xmldata['Returns'],
                                'time' => time()
                            ));
                        break;
                    }
                }
            }
            
            goto label1;
        } else {
            DaemonLockTool::lock(__METHOD__ . 'one');
            sleep(15);
            goto label1;
        }
    }

    /**
     * @desc 命名空间的冒号(：)变换为下划线(_)
     * @author liaojianwen
     * @date 2015-06-15
     * @param $xmlstr
     */
    public function parseNamespaceXml($xmlstr)
    {
        $xmlstr = preg_replace('/\sxmlns="(.*?)"/', ' _xmlns="${1}"', $xmlstr);
        $xmlstr = preg_replace('/<(\/)?(\w+):(\w+)/', '<${1}${2}_${3}', $xmlstr);
        $xmlstr = preg_replace('/(\w+):(\w+)="(.*?)"/', '${1}_${2}="${3}"', $xmlstr);
        return $xmlstr;
    }

    /**
     * @desc after-sale API 获取return明细
     * @param string $return_id 
     * @param string $token 
     * @author liaojianwen
     * @date 2015-06-26
     */
    public function getReturnDetailInfo($return_id, $token)
    {
        $url = "https://api.ebay.com/sell/order/v1/return/{$return_id}";
        $result = $this->curlOption($url, $type = "GET", '', $token);
        return $result;
    }

    /**
     * @desc 获取物流信息
     * @param string $return_id 
     * @param string $tracking_no 物流单号
     * @param string $carrier 物流承运商
     * @param $string $token 
     */
    public function getReturnTrackingInfo($return_id, $tracking_no, $carrier, $token)
    {
        $carrier = urlencode($carrier);
        $url = "https://api.ebay.com/sell/order/v1/return/{$return_id}/trackingHistory";
        $data = "trackingNumber={$tracking_no}&carrierUsed={$carrier}";
        $result = $this->curlOption($url, $type = "GET", $data, $token);
        return $result;
    }

    /**
     * @desc 确认return
     * @param string $return_id
     * @param string $token
     * @author liaojianwen
     * @date 2015-06-29
     */
    public function acceptReturn($return_id, $token)
    {
        $url = "https://api.ebay.com/sell/order/v1/return/{$return_id}/authorize";
        $param = array(
            'decision' => 'APPROVE'
        );
        $data = json_encode($param);
        $result = $this->curlOption($url, $type = "POST", $data, $token);
        return $result;
    }

    /**
     * @desc 拒绝request
     * @author liaojianwen
     * @date 2015-08-10
     * @param string $return_id
     * @param string $comments
     * @param string $token
     * @return mixed
     */
    public function declineRequest($return_id, $comments, $token)
    {
        $url = "https://api.ebay.com/sell/order/v1/return/{$return_id}/authorize";
        $param = array(
            'comments' => $comments,
            'decision' => 'DECLINE'
        );
        $data = json_encode($param);
        $result = $this->curlOption($url, $type = "POST", $data, $token);
        return $result;
    }

    /**
     * @desc return 部分退款
     * @param string $return_id
     * @param deciml $amount
     * @param string $currencyId
     * @param string $token
     * @author liaojianwen
     * @date 2015-06-29
     */
    public function issuePartialRefund($return_id, $amount, $currencyId, $comments, $token)
    {
        $url = "https://api.ebay.com/sell/order/v1/return/{$return_id}/authorize";
        $params = array(
            'comments' => $comments,
            'decision' => 'OFFER_PARTIAL_REFUND',
            'partialRefundAmount' => array(
                'value' => $amount,
                'currencyId' => $currencyId
            )
        );
        
        $data = json_encode($params);
        $result = $this->curlOption($url, $type = "POST", $data, $token);
        return $result;
    }

    /**
     * 提供RMA 确认退货地址
     * 
     * @param string $return_id            
     * @param string $RMA            
     * @param string $token            
     */
    public function provideRMA($return_id, $RMA, $returnAddr, $token)
    {
        $url = "https://api.ebay.com/sell/order/v1/return/{$return_id}/authorize";
        if (empty($returnAddr['any'])) {
            $returnAddr['any'] = array();
        } else {
            $returnAddr['any'] = json_decode($returnAddr['any'], true);
        }
        
        $params = array(
            'decision' => 'PROVIDE_RMA',
            'returnMerchandiseAuthorization' => $RMA,
            'sellerReturnAddress' => $returnAddr
        );
        $data = json_encode($params);
        $result = $this->curlOption($url, $type = "POST", $data, $token);
        return $result;
    }

    /**
     * @desc return全额退款
     * @param string $return_id returnID
     * @param string $token
     * @param string $comments
     * @param array $itemRefundDetail//退款明细
     * @author liaojianwen
     * @date 2015-06-29
     */
    public function issueReturnRefund($return_id, $itemRefundDetail, $token, $comments = "Refund")
    {
        $url = "https://api.ebay.com/sell/order/v1/return/{$return_id}/issueRefund";
        // $itemRefundDetail = EstimatedRefundDAO::getInstance()->getItemizedRefundDetail($return_id);
        $params = array(
            'comments' => $comments,
            'refundDetail' => $itemRefundDetail
        );
        $data = json_encode($params);
        $result = $this->curlOption($url, $type = "POST", $data, $token);
        return $result;
    }

    /**
     * @desc 标记为已收
     * @param string $return_id returnID
     * @param string $token
     * @param string $comments
     */
    public function markAsReceived($return_id, $token, $comments = '')
    {
        $url = "https://api.ebay.com/sell/order/v1/return/{$return_id}/markAsReceived";
        if (! empty($comments)) {
            $params = array(
                'comments' => $comments
            );
            $data = json_encode($params);
        } else {
            $data = '';
        }
        $result = $this->curlOption($url, $type = "POST", $data, $comments);
        return $result;
    }

    /**
     * @desc 换货时提供物流信息
     * @param string $return_id returnID
     * @param string $token
     * @param string $comments
     * @param string $carrierEnum///AUSTRALIA_POST、CANADA_POST、COLLECT_PLUS、DEUTSCHE_POST、DHL、FEDEX、HERMES、OTHER、PARCEL_FORCE、ROYAL_MAIL、UNKNOWN、UPS、USPS
     * @param string $carrierName
     * @param string $shippedDate
     * @param string $trackingNumber
     */
    public function markAsShipped($return_id, $token, $comments = '', $carrierEnum, $carrierName, $shippedDate, $trackingNumber)
    {
        $url = "https://api.ebay.com/sell/order/v1/return/{$return_id}/markAsShipped";
        if (empty($carrierName)) {
            $params = array(
                'comments' => $comments,
                'trackingNumber' => $trackingNumber,
                'carrierEnum' => $carrierEnum
            );
        } else 
            if (empty($shippedDate)) {
                $params = array(
                    'comments' => $comments,
                    'trackingNumber' => $trackingNumber,
                    'carrierEnum' => $carrierEnum,
                    'carrierName' => $carrierName
                );
            } else {
                $params = array(
                    'comments' => $comments,
                    'carrierEnum' => $carrierEnum,
                    'carrierName' => $carrierName,
                    'shippedDate' => $shippedDate * 1000
                );
            }
        
        $data = json_encode($params);
        $result = $this->curlOption($url, $type = "POST", $data, $comments);
        return $result;
    }

    /**
     * @desc This call is used by the seller to indicate that the buyer’s refund for the returned item has been sent
     * @param string $return_id
     * @param string $token
     * @param string $comments
     * @param  array $itemRefundDetail 退款明细
     * @author liaojianwen
     * @date 2015-06-29
     */
    public function markRefundSent($return_id, $token, $comments = "Refund", $itemRefundDetail)
    {
        $url = "https://api.ebay.com/sell/order/v1/return/{$return_id}/markRefundSent";
        $params = array(
            'comments' => $comments,
            'refundDetail' => $itemRefundDetail
        );
        $data = json_encode($params);
        $result = $this->curlOption($url, $type = "POST", $data, $token);
        return $result;
    }

    /**
     * @desc send message to customer
     * @param string $return_id
     * @param string $comments
     * @param string $token
     * @author liaojianwen
     * @date 2015-06-29
     */
    public function sendMessage($return_id, $comments, $token)
    {
        $url = "https://api.ebay.com/sell/order/v1/return/{$return_id}/sendMessage";
        $params = array(
            'message' => $comments
        );
        $data = json_encode($params);
        $result = $this->curlOption($url, $type = "POST", $data, $token);
        return $result;
    }

    /**
     * @desc  ebay 介入
     * @param $return_id returnID
     * @param $comments
     * @param $reason
     * @param $token
     * @author liaojianwen
     * @date 2015-06-29
     */
    public function askEbayHelp($return_id, $comments, $reason, $token)
    {
        $url = "https://api.ebay.com/sell/order/v1/return/{$return_id}/escalate";
        $params = array(
            'comment' => $comments,
            'reason' => $reason
        );
        $data = json_encode($params);
        $result = $this->curlOption($url, $type = "POST", $data, $token);
        return $result;
    }

    /**
     * @desc  获取return 中的图片
     * @param string $return_id
     * @param string $token
     * @author liaojianwen
     * @date 2015-10-08
     */
    public function getFileData($return_id, $token)
    {
        $url = "https://api.ebay.com/post-order/v2/return/{$return_id}/files";
        $result = $this->curlOption($url, $type = "GET", '', $token);
        return $result;
    }

    /**
     * @desc 上传return
     * @param string $return_id
     * @param string $token
     * @param array $imgUrl
     */
    public function submitFile($return_id, $token, $imgUrl)
    {
        $url = "https://api.ebay.com/post-order/v2/return/{$return_id}/file/upload";
        $uploadData = array();
        foreach ($imgUrl as $img) {
            $urlStr = base64_encode(file_get_contents($img));
            array_push($uploadData, $urlStr);
        }
        $filedata = array(
            'data' => $uploadData,
            'filePurpose' => 'ITEM_RELATED'
        );
        $filedata = json_encode($filedata);
        // $result = $this->curlOption($url, $type = "POST", $filedata, $token);
        print_r($filedata);
        die();
        // return $result;
    }

    /**
     * @desc 连接函数
     * @param string $url
     * @param string $type
     * @param json  $data//get=> 字符串, post => json
     * @param string $token
     * @author liaojianwen
     * @date 2015-06-29
     */
    private function curlOption($url, $type = "GET", $data, $token)
    {
        $headers = array(
            "Authorization:TOKEN {$token}",
            "Content-Type:application/json",
            "X-EBAY-CMARKETPLACE-ID:EBAY-US"
        )
        ;
        $connection = curl_init();
        if ($type === "GET") {
            if (! empty($data)) {
                $url .= "?{$data}";
            }
        }
        curl_setopt($connection, CURLOPT_URL, $url);
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
        if ($type === "POST") {
            curl_setopt($connection, CURLOPT_POST, 1);
            if (! empty($data)) {
                curl_setopt($connection, CURLOPT_POSTFIELDS, $data);
            }
        }
        $response = curl_exec($connection);
        curl_close($connection);
        return $response;
    }

    /**
     * @desc 获取状态信息
     * @param string $returnId
     * @param string $token
     * @param number $siteid
     * @author YangLong
     * @date 2015-06-12
     * @return mixed
     */
    public function getActivityOptions($returnId, $token, $siteid = 0)
    {
        $callName = 'getActivityOptions';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/returns/v1/ReturnManagementService';
        } else {
            $this->serverUrl = 'https://svcs.sandbox.ebay.com/services/returns/v1/ReturnManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/return-management/CallRef/getActivityOptions.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<getActivityOptionsRequest xmlns="http://www.ebay.com/marketplace/returns/v1/services">
  <ReturnId>
    <id>' . $returnId . '</id>
  </ReturnId>
</getActivityOptionsRequest>';
        
        // @see http://developer.ebay.com/Devzone/return-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        $session->headers[] = "X-EBAY-SOA-SERVICE-VERSION:{$this->compatabilityLevel}";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        // TODO change condition
        if (stripos($responseXml, '<ns1:ack>Failure</ns1:ack>')) {
            iMongo::getInstance()->setCollection($callName)->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'times' => 1
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<ns1:ack>Failure</ns1:ack>')) {
            iMongo::getInstance()->setCollection($callName)->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'times' => 2
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        return $responseXml;
    }

    /**
     * @desc 获取return详情
     * @param string $returnId
     * @param string $token
     * @param number $siteid
     * @author YangLong
     * @date 2015-06-12
     * @return mixed
     */
    public function getReturnDetail($returnId, $token, $siteid = 0)
    {
        $callName = 'getReturnDetail';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/returns/v1/ReturnManagementService';
        } else {
            $this->serverUrl = 'https://svcs.sandbox.ebay.com/services/returns/v1/ReturnManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/return-management/CallRef/getReturnDetail.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<getReturnDetailRequest xmlns="http://www.ebay.com/marketplace/returns/v1/services">
  <ReturnId>
    <id>' . $returnId . '</id>
  </ReturnId>
</getReturnDetailRequest>';
        
        // @see http://developer.ebay.com/Devzone/return-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        $session->headers[] = "X-EBAY-SOA-SERVICE-VERSION:{$this->compatabilityLevel}";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        // TODO change condition
        if (stripos($responseXml, '<ns1:ack>Failure</ns1:ack>')) {
            iMongo::getInstance()->setCollection($callName)->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'times' => 1
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<ns1:ack>Failure</ns1:ack>')) {
            iMongo::getInstance()->setCollection($callName)->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'times' => 2
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        return $responseXml;
    }

    /**
     * @desc A seller can use this call to return metadata about eBay-Managed returns. This call is not applicable to a buyer.
     * @param string $metadataEntryCodeArray array('REFUND_DUE_UPON_ITEM_ARRIVAL','RETURN_REASONS','RMA_DUE_UPON_RETURN_START')
     * @param string $token
     * @param number $siteid
     * @author YangLong
     * @date 2015-06-12
     * @return mixed
     */
    public function getReturnMetadata($metadataEntryCodeArray, $token, $siteid = 0)
    {
        $callName = 'getReturnMetadata';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/returns/v1/ReturnManagementService';
        } else {
            $this->serverUrl = 'https://svcs.sandbox.ebay.com/services/returns/v1/ReturnManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/return-management/CallRef/getReturnMetadata.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<getReturnMetadataRequest xmlns="http://www.ebay.com/marketplace/returns/v1/services">';
        foreach ($metadataEntryCodeArray as $value) {
            $requestXmlBody .= '
  <metadataEntryCode>' . $value . '</metadataEntryCode>';
        }
        $requestXmlBody .= '
</getReturnMetadataRequest>';
        
        // @see http://developer.ebay.com/Devzone/return-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        $session->headers[] = "X-EBAY-SOA-SERVICE-VERSION:{$this->compatabilityLevel}";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        // TODO change condition
        if (stripos($responseXml, '<ns1:ack>Failure</ns1:ack>')) {
            iMongo::getInstance()->setCollection($callName)->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'times' => 1
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<ns1:ack>Failure</ns1:ack>')) {
            iMongo::getInstance()->setCollection($callName)->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'times' => 2
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        return $responseXml;
    }

    /**
     * @desc 获取18个月以内的returns
     * @param int $fromDate
     * @param int $toDate
     * @param string $itemId
     * @param string $transactionId
     * @param string $orderId
     * @param string $token
     * @param number $siteid
     * @param number $page
     * @param number $number
     * @param array $ReturnStatus
     * @param string $sortType
     * @param string $sortOrderType
     * @author YangLong
     * @date 2015-06-12
     * @return mixed
     */
    public function getUserReturns($fromDate, $toDate, $itemId, $transactionId, $orderId, $token, $siteid = 0, $page = 1, $number = 200, 
        $ReturnStatus = array(), $sortType = '', $sortOrderType = '')
    {
        $callName = 'getUserReturns';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/returns/v1/ReturnManagementService';
        } else {
            $this->serverUrl = 'https://svcs.sandbox.ebay.com/services/returns/v1/ReturnManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/return-management/CallRef/getUserReturns.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<getUserReturnsRequest xmlns="http://www.ebay.com/marketplace/returns/v1/services">
  <creationDateRangeFilter>';
        if (! empty($fromDate)) {
            $requestXmlBody .= '
    <fromDate>' . $this->fmtDate($fromDate) . '</fromDate>';
        }
        if (! empty($toDate)) {
            $requestXmlBody .= '
    <toDate>' . $this->fmtDate($toDate) . '</toDate>';
        }
        $requestXmlBody .= '
  </creationDateRangeFilter>
  <itemFilter>';
        if (! empty($itemId)) {
            $requestXmlBody .= '
    <itemId>' . $itemId . '</itemId>';
        }
        if (! empty($transactionId)) {
            $requestXmlBody .= '
    <transactionId>' . $transactionId . '</transactionId>';
        }
        $requestXmlBody .= '
  </itemFilter>';
        if (! empty($orderId)) {
            $requestXmlBody .= '
  <orderId>' . $orderId . '</orderId>';
        }
        
        if (false) {
            $requestXmlBody .= '
  <otherUserFilter>
    <role></role>
    <userId></userId>
    <userLoginName></userLoginName>
  </otherUserFilter>';
        }
        
        $requestXmlBody .= '
  <paginationInput>
    <entriesPerPage>' . $number . '</entriesPerPage>
    <pageNumber>' . $page . '</pageNumber>
  </paginationInput>
  <ReturnStatusFilter>';
        foreach ($ReturnStatus as $value) {
            $requestXmlBody .= '
    <ReturnStatus>' . $value . '</ReturnStatus>';
        }
        $requestXmlBody .= '
  </ReturnStatusFilter>';
        if (! empty($sortOrderType)) {
            $requestXmlBody .= '
  <sortOrderType>' . $sortOrderType . '</sortOrderType>';
        }
        if (! empty($sortType)) {
            $requestXmlBody .= '
  <sortType>' . $sortType . '</sortType>';
        }
        $requestXmlBody .= '
</getUserReturnsRequest>';
        
        // @see http://developer.ebay.com/Devzone/return-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        $session->headers[] = "X-EBAY-SOA-SERVICE-VERSION:{$this->compatabilityLevel}";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        // TODO change condition
        if (stripos($responseXml, '<ns1:ack>Failure</ns1:ack>')) {
            iMongo::getInstance()->setCollection($callName)->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'times' => 1
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<ns1:ack>Failure</ns1:ack>')) {
            iMongo::getInstance()->setCollection($callName)->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'times' => 2
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        return $responseXml;
    }

    /**
     * @desc 退款
     * @param string $returnId
     * @param array $itemizedRefundArray array(
     *                                      array(
     *                                          'amount'=>11,
     *                                          'refundFeeType'=>11,
     *                                          'currencyId' => 'xx'
     *                                      ),
     *                                      array(
     *                                          'amount' => 22,
     *                                          'refundFeeType' => 22,
     *                                          'currencyId' => 'xx'
     *                                      )
     *                                   )
     * @param number $totalAmount
     * @param string $comments
     * @param string $token
     * @param number $siteid
     * @author YangLong
     * @date 2015-06-12
     * @return mixed
     */
    public function issueRefund($returnId, $itemizedRefundArray, $totalAmount, $comments, $token, $siteid = 0)
    {
        $callName = 'issueRefund';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/returns/v1/ReturnManagementService';
        } else {
            $this->serverUrl = 'https://svcs.sandbox.ebay.com/services/returns/v1/ReturnManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/return-management/CallRef/issueRefund.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<issueRefundRequest xmlns="http://www.ebay.com/marketplace/returns/v1/services">';
        if (! empty($comments)) {
            $requestXmlBody .= '
  <comments>' . $comments . '</comments>';
        }
        $requestXmlBody .= '
  <refundDetail>';
        
        foreach ($itemizedRefundArray as $value) {
            if (isset($value['amount']) && isset($value['refundFeeType']) && isset($value['currencyId'])) {
                $requestXmlBody .= '
    <itemizedRefund>
      <amount currencyId="' . $value['currencyId'] . '">' . $value['amount'] . '</amount>
      <refundFeeType>' . $value['refundFeeType'] . '</refundFeeType>
    </itemizedRefund>';
            }
        }
        
        $requestXmlBody .= '
    <totalAmount>' . $totalAmount . '</totalAmount>';
        
        $requestXmlBody .= '
  </refundDetail>
  <ReturnId>
    <id>' . $returnId . '</id>
  </ReturnId>
</issueRefundRequest>';
        
        // @see http://developer.ebay.com/Devzone/return-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        $session->headers[] = "X-EBAY-SOA-SERVICE-VERSION:{$this->compatabilityLevel}";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        // TODO change condition
        if (stripos($responseXml, '<ns1:ack>Failure</ns1:ack>')) {
            iMongo::getInstance()->setCollection($callName)->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'times' => 1
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<ns1:ack>Failure</ns1:ack>')) {
            iMongo::getInstance()->setCollection($callName)->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'times' => 2
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        return $responseXml;
    }

    /**
     * @desc 提供退货地址和商品识别码
     * @param string $returnId
     * @param string $RMAnumber
     * @param string $city
     * @param string $country
     * @param string $county
     * @param string $name
     * @param string $postalCode
     * @param string $stateOrProvince
     * @param string $street1
     * @param string $street2
     * @param string $token
     * @param number $siteid
     * @author YangLong
     * @date 2015-06-12
     * @return mixed
     */
    public function provideSellerInfo($returnId, $RMAnumber, $city, $country, $county, $name, $postalCode, $stateOrProvince, $street1, $street2, 
        $token, $siteid = 0)
    {
        $callName = 'provideSellerInfo';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/returns/v1/ReturnManagementService';
        } else {
            $this->serverUrl = 'https://svcs.sandbox.ebay.com/services/returns/v1/ReturnManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/return-management/CallRef/provideSellerInfo.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<provideSellerInfoRequest xmlns="http://www.ebay.com/marketplace/returns/v1/services">
  <returnAddress>
    <city>' . $city . '</city>
    <country>' . $country . '</country>
    <county>' . $county . '</county>
    <name>' . $name . '</name>
    <postalCode>' . $postalCode . '</postalCode>
    <stateOrProvince>' . $stateOrProvince . '</stateOrProvince>
    <street1>' . $street1 . '</street1>
    <street2>' . $street2 . '</street2>
  </returnAddress>
  <ReturnId>
    <id>' . $returnId . '</id>
  </ReturnId>
  <returnMerchandiseAuthorization>' . $RMAnumber . '</returnMerchandiseAuthorization>
</provideSellerInfoRequest>';
        
        // @see http://developer.ebay.com/Devzone/return-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        $session->headers[] = "X-EBAY-SOA-SERVICE-VERSION:{$this->compatabilityLevel}";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        // TODO change condition
        if (stripos($responseXml, '<ns1:ack>Failure</ns1:ack>')) {
            iMongo::getInstance()->setCollection($callName)->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'times' => 1
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<ns1:ack>Failure</ns1:ack>')) {
            iMongo::getInstance()->setCollection($callName)->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'times' => 2
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        return $responseXml;
    }

    /**
     * @desc 提供物流号
     * @param string $returnId
     * @param string $carrierUsed
     * @param string $trackingNumber
     * @param string $comments
     * @param string $token
     * @param number $siteid
     * @author YangLong
     * @date 2015-06-12
     * @return mixed
     */
    public function provideTrackingInfo($returnId, $carrierUsed, $trackingNumber, $comments, $token, $siteid = 0)
    {
        $callName = 'provideTrackingInfo';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/returns/v1/ReturnManagementService';
        } else {
            $this->serverUrl = 'https://svcs.sandbox.ebay.com/services/returns/v1/ReturnManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/return-management/CallRef/provideTrackingInfo.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<provideTrackingInfoRequest xmlns="http://www.ebay.com/marketplace/returns/v1/services">
  <carrierUsed>' . $carrierUsed . '</carrierUsed>
  <comments>' . $comments . '</comments>
  <ReturnId>' . $returnId . '</ReturnId>
  <trackingNumber>' . $trackingNumber . '</trackingNumber>
</provideTrackingInfoRequest>';
        
        // @see http://developer.ebay.com/Devzone/return-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        $session->headers[] = "X-EBAY-SOA-SERVICE-VERSION:{$this->compatabilityLevel}";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        // TODO change condition
        if (stripos($responseXml, '<ns1:ack>Failure</ns1:ack>')) {
            iMongo::getInstance()->setCollection($callName)->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'times' => 1
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<ns1:ack>Failure</ns1:ack>')) {
            iMongo::getInstance()->setCollection($callName)->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'times' => 2
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        return $responseXml;
    }

    /**
     * @desc 使用其他方式标记为已发货
     * @param string $returnId
     * @param string $token
     * @param number $siteid
     * @author YangLong
     * @date 2015-06-12
     * @return mixed
     */
    public function setItemAsReceived($returnId, $token, $siteid = 0)
    {
        $callName = 'setItemAsReceived';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/returns/v1/ReturnManagementService';
        } else {
            $this->serverUrl = 'https://svcs.sandbox.ebay.com/services/returns/v1/ReturnManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/return-management/CallRef/setItemAsReceived.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<setItemAsReceivedRequest xmlns="http://www.ebay.com/marketplace/returns/v1/services">
  <ReturnId>' . $returnId . '</ReturnId>
</setItemAsReceivedRequest>';
        
        // @see http://developer.ebay.com/Devzone/return-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        $session->headers[] = "X-EBAY-SOA-SERVICE-VERSION:{$this->compatabilityLevel}";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        // TODO change condition
        if (stripos($responseXml, '<ns1:ack>Failure</ns1:ack>')) {
            iMongo::getInstance()->setCollection($callName)->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'times' => 1
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<ns1:ack>Failure</ns1:ack>')) {
            iMongo::getInstance()->setCollection($callName)->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'times' => 2
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        return $responseXml;
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
        return gmdate('Y-m-d\TH:i:s\Z', $date);
    }
}
