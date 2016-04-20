<?php

/**
 * @desc CaseDown class
 * @author YangLong
 * @date 2015-03-27
 */
class CaseDownModel extends BaseModel
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
     * @desc 覆盖父方法返回CaseDownModel对象
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-03-27
     * @return CaseDownModel
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
     * @desc 生成Case下载队列
     * @author YangLong
     * @date 2015-03-27
     * @return null
     */
    public function generateCaseDownQueue()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $shops = MsgDownDAO::getInstance()->getEbShop('case');
        
        foreach ($shops as $key => $shop) {
            $_time = time();
            if (empty($shop['case_down_time'])) {
                $shop['case_down_time'] = $_time - EnumOther::CASE_MAX_DOWNLOAD_DATE;
                for ($i = 0; $i < 19; $i ++) {
                    $fromDate = $_time - ($i + 1) * 30 * 24 * 3600;
                    $toDate = $_time - $i * 30 * 24 * 3600;
                    if ($toDate < strtotime('-18 month')) {
                        $toDate = strtotime('-18 month');
                    }
                    if ($toDate < $shop['case_down_time']) {
                        break;
                    }
                    $this->makeCaseDownQueue($fromDate, $toDate, $shop, 20 - $i);
                }
            } else {
                $fromDate = $shop['case_down_time'] - EnumOther::OVARLAP_TIME;
                $toDate = $_time;
                $this->makeCaseDownQueue($fromDate, $toDate, $shop, 19);
            }
            
            $columns = array(
                'case_down_time' => $_time
            );
            $conditions = 'shop_id=:shop_id';
            $params = array(
                ':shop_id' => $shop['shop_id']
            );
            ShopDAO::getInstance()->iupdate($columns, $conditions, $params);
        }
    }

    /**
     * @desc 生成Case下载队列
     * @param int $fromDate
     * @param int $toDate
     * @param array $shop
     * @param int $priority
     * @author YangLong
     * @date 2015-03-27
     * @return boolean|int
     */
    private function makeCaseDownQueue($fromDate, $toDate, $shop, $priority)
    {
        $columns = array(
            'seller_id' => $shop['seller_id'],
            'shop_id' => $shop['shop_id'],
            'site_id' => $shop['site_id'],
            'token' => $shop['token'],
            'start_time' => $fromDate,
            'end_time' => $toDate,
            'priority' => $priority
        );
        return CaseDownQueueDAO::getInstance()->iinsert($columns);
    }

    /**
     * @desc 运行下载队列
     * @author YangLong
     * @date 2015-03-27
     * @return boolean
     */
    public function executeCaseDownQueue()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $startTime = time();
        label1:
        
        if (time() - $startTime > 600) {
            return false;
        }
        
        $Queues = CaseDownQueueDAO::getInstance()->getDownQueueData();
        if ($Queues !== false) {
            $pagesize = 25;
            foreach ($Queues as $key => $Queue) {
                $page = 0;
                while (true) {
                    $page ++;
                    
                    $xmldata = array();
                    $xmldata['Cases'] = $this->getUserCases($Queue['start_time'], $Queue['end_time'], $Queue['token'], $Queue['site_id'], $page, 
                        $pagesize);
                    
                    $doc = phpQuery::newDocumentXML($xmldata['Cases']);
                    phpQuery::selectDocument($doc);
                    
                    if (pq('ack')->html() == 'Failure') {
                        if (stripos($xmldata['Cases'], '<errorId>1101</errorId>')) {
                            // 日期超过获取的限制直接删除无效队列
                            // 异常发邮件
                            $subject = "调试：日期超过获取的限制直接删除无效队列";
                            ob_start();
                            var_dump($Queue);
                            $text = ob_get_clean();
                            $to = Yii::app()->params['logmails'];
                            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
                            
                            CaseDownQueueDAO::getInstance()->deleteByPk($Queue['down_queue_id']);
                        }
                        continue 2;
                    }
                    
                    $cases = pq('cases>caseSummary');
                    $length = $cases->length;
                    
                    for ($i = 0; $i < $length; $i ++) {
                        $case = $cases->eq($i);
                        
                        $caseId_id = $case->find('caseId>id')->html();
                        $caseId_type = $case->find('caseId>type')->html();
                        if ($caseId_id !== false && $caseId_type !== false) {
                            if ($caseId_type == 'EBP_INR' || $caseId_type == 'EBP_SNAD') {
                                $xmldata['CaseDetail'][$caseId_id] = $this->getEBPCaseDetail($caseId_id, $caseId_type, $Queue['token'], 
                                    $Queue['site_id']);
                                $xmldata['ActivityOptions'][$caseId_id] = $this->getActivityOptions($caseId_id, $caseId_type, $Queue['token'], 
                                    $Queue['site_id']);
                            } else {
                                $xmldata['CaseDetail'][$caseId_id] = $this->getDispute($caseId_id, $Queue['token'], $Queue['site_id']);
                            }
                        }
                    }
                    $columns = array(
                        'seller_id' => $Queue['seller_id'],
                        'shop_id' => $Queue['shop_id'],
                        'text_json' => base64_encode(serialize($xmldata)),
                        'create_time' => time()
                    );
                    
                    $lid = CaseDownDAO::getInstance()->iinsert($columns);
                    if ($lid !== false) {
                        CaseDownQueueDAO::getInstance()->deleteByPk($Queue['down_queue_id']);
                    }
                    
                    // 写日志
                    if (stripos($xmldata['Cases'], '<ack>Success</ack>') === false) {
                        if (stripos($xmldata['Cases'], '<errorId>1302</errorId>') === false) {
                            iMongo::getInstance()->setCollection('getUserCasesNoSuccess')->insert(
                                array(
                                    'xmldata' => $xmldata,
                                    'time' => time()
                                ));
                        }
                    }
                    
                    unset($columns);
                    unset($xmldata);
                    
                    if ($length < $pagesize) {
                        break;
                    }
                }
            }
            
            goto label1;
        } else {
            sleep(8);
            goto label1;
        }
    }

    /**
     * @desc 获取用户CASE列表
     * @param number $fromDate 开始日期
     * @param number $toDate 结束日期
     * @param string $token token
     * @param number $siteid eBay site id
     * @param number $page 页码
     * @param number $number 分页大小
     * @param boolean $open 是否忽略时间并下载所有open case
     * @author YangLong
     * @date 2015-03-26
     * @return string XML
     */
    public function getUserCases($fromDate, $toDate, $token, $siteid = 0, $page = 1, $number = 200, $open = false)
    {
        $callName = 'getUserCases';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        } else {
            $this->serverUrl = 'http://svcs.sandbox.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/CallRef/getUserCases.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<getUserCasesRequest xmlns="http://www.ebay.com/marketplace/resolution/v1/services">';
        
        if (! empty($fromDate) && ! empty($toDate) && ! $open) {
            $requestXmlBody .= '
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
  </creationDateRangeFilter>';
        }
        
        if (false) {
            $requestXmlBody .= '
  <caseStatusFilter>
    <caseStatus>ELIGIBLE_FOR_CREDIT</caseStatus>
    <caseStatus>MY_PAYMENT_DUE</caseStatus>
    <caseStatus>MY_RESPONSE_DUE</caseStatus>
    <caseStatus>OPEN</caseStatus>
    <caseStatus>OTHER_PARTY_RESPONSE_DUE</caseStatus>
  </caseStatusFilter>';
        }
        
        $requestXmlBody .= '
  <paginationInput>
    <entriesPerPage>' . $number . '</entriesPerPage>
    <pageNumber>' . $page . '</pageNumber>
  </paginationInput>';
        $requestXmlBody .= '
  <sortOrder>CREATION_DATE_DESCENDING</sortOrder>';
        $requestXmlBody .= '
</getUserCasesRequest>';
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $tryCount = 0;
        
        label1:
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        // TODO change condition
        if (stripos($responseXml, '<ack>Failure</ack>')) {
            iMongo::getInstance()->setCollection('getUserCasesF')->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'tryCount' => $tryCount,
                    'times' => 1
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<ack>Failure</ack>')) {
            iMongo::getInstance()->setCollection('getUserCasesF')->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'tryCount' => $tryCount,
                    'times' => 2
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<errorId>1001</errorId>')) {
            if ($tryCount < 11) {
                $tryCount ++;
                goto label1;
            }
        }
        
        if (! XMLTool::IsXML($responseXml)) {
            if ($tryCount < 22) {
                $tryCount ++;
                goto label1;
            }
        }
        
        return $responseXml;
    }

    /**
     * @desc 获取用户纠纷列表
     * @param string $fromDate
     * @param string $toDate
     * @param string $token
     * @param number $siteid
     * @param number $page
     * @param number $number
     * @param string $DetailLevel
     * @author YangLong
     * @date 2015-06-03
     * @return string XML|boolean
     */
    public function getUserDisputes($fromDate, $toDate, $token, $siteid = 0, $page = 1, $number = 200, $DetailLevel = 'ReturnAll')
    {
        $callName = 'GetUserDisputes';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<GetUserDisputesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>' . $token . '</eBayAuthToken>
  </RequesterCredentials>
  <DisputeFilterType>AllInvolvedDisputes</DisputeFilterType>
  <DisputeSortType>DisputeCreatedTimeDescending</DisputeSortType>';
        if (! empty($fromDate)) {
            $requestXmlBody .= '
  <ModTimeFrom>' . $this->fmtDate($fromDate) . '</ModTimeFrom>';
        }
        if (! empty($toDate)) {
            $requestXmlBody .= '
  <ModTimeTo>' . $this->fmtDate($toDate) . '</ModTimeTo>';
        }
        $requestXmlBody .= '
  <Pagination>
    <PageNumber>' . $page . '</PageNumber>
    <EntriesPerPage>' . $number . '</EntriesPerPage>
  </Pagination>
  <DetailLevel>' . $DetailLevel . '</DetailLevel>
</GetUserDisputesRequest>';
        
        // @see http://developer.ebay.com/Devzone/XML/docs/Reference/eBay/GetUserDisputes.html
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/index.html#Limitations
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:933";
        $session->headers[] = "X-EBAY-API-DEV-NAME:{$this->devID}";
        $session->headers[] = "X-EBAY-API-APP-NAME:{$this->appID}";
        $session->headers[] = "X-EBAY-API-CERT-NAME:{$this->certID}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        $session->headers[] = "X-EBAY-API-SITEID:{$siteid}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        $tryCount = 0;
        
        label1:
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        // TODO change condition
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('getUserDisputesF')->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'tryCount' => $tryCount,
                    'times' => 1
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('getUserDisputesF')->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'tryCount' => $tryCount,
                    'times' => 2
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (! XMLTool::IsXML($responseXml)) {
            if ($tryCount < 15) {
                $tryCount ++;
                goto label1;
            }
        }
        
        if ($responseXml === false) {
            if ($tryCount < 22) {
                $tryCount ++;
                sleep(15);
                goto label1;
            }
        }
        
        return $responseXml;
    }

    /**
     * @desc CASE详情获取(EBP_INR or EBP_SNAD)
     * @param string $caseId_id
     * @param string $caseId_type
     * @param string $token
     * @param number $siteid
     * @author YangLong
     * @date 2015-03-26
     * @return string XML
     */
    public function getEBPCaseDetail($caseId_id, $caseId_type, $token, $siteid = 0)
    {
        $callName = 'getEBPCaseDetail';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        } else {
            $this->serverUrl = 'http://svcs.sandbox.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        }
        
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<getEBPCaseDetailRequest xmlns="http://www.ebay.com/marketplace/resolution/v1/services">
  <caseId>
    <id>' . $caseId_id . '</id>
    <type>' . $caseId_type . '</type>
  </caseId>
</getEBPCaseDetailRequest>';
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $tryCount = 0;
        
        label1:
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        // TODO change condition
        if (stripos($responseXml, '<ack>Failure</ack>')) {
            iMongo::getInstance()->setCollection('getEBPCaseDetailF')->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'times' => 1
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<ack>Failure</ack>')) {
            iMongo::getInstance()->setCollection('getEBPCaseDetailF')->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'times' => 2
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<errorId>1001</errorId>')) {
            if ($tryCount < 11) {
                $tryCount ++;
                goto label1;
            }
        }
        
        if (! XMLTool::IsXML($responseXml)) {
            iMongo::getInstance()->setCollection('getEBPCaseDetailBadXML')->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'times' => 3
                ));
            if ($tryCount < 22) {
                $tryCount ++;
                goto label1;
            }
        }
        
        return $responseXml;
    }

    /**
     * @desc Dispute详情获取(非 EBP_INR and EBP_SNAD)
     * @param strin $disputeID
     * @param strin $token
     * @param number $siteid
     * @author YangLong
     * @date 2015-04-03
     * @return string XML
     */
    public function getDispute($disputeID, $token, $siteid = 0)
    {
        $callName = 'GetDispute';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<GetDisputeRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>' . $token . '</eBayAuthToken>
  </RequesterCredentials>
  <DisputeID>' . $disputeID . '</DisputeID>
</GetDisputeRequest>';
        
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/GetDispute.html
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
            iMongo::getInstance()->setCollection('getDisputeF')->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'times' => 1
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('getDisputeF')->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'times' => 2
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        // if (stripos($responseXml, '<errorId>1001</errorId>')) {
        // if ($tryCount < 11) {
        // $tryCount ++;
        // goto label1;
        // }
        // }
        
        if (! XMLTool::IsXML($responseXml)) {
            iMongo::getInstance()->setCollection('getDisputeBadXML')->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'tryCount' => $tryCount,
                    'times' => 3
                ));
            if ($tryCount < 22) {
                sleep(1);
                $tryCount ++;
                goto label1;
            }
        }
        
        if (stripos($responseXml, '<Ack>Success</Ack>') === false) {
            iMongo::getInstance()->setCollection('getDisputeNoSuccess')->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'tryCount' => $tryCount,
                    'times' => 4
                ));
        }
        
        return $responseXml;
    }

    /**
     * @desc 获取CASE能进行的操作信息
     * @param string $caseId_id
     * @param string $caseId_type
     * @param string $token
     * @param number $siteid
     * @author YangLong
     * @date 2015-04-03
     * @return string XML
     */
    public function getActivityOptions($caseId_id, $caseId_type, $token, $siteid = 0)
    {
        $callName = 'getActivityOptions';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        } else {
            $this->serverUrl = 'http://svcs.sandbox.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        }
        
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<getActivityOptionsRequest xmlns="http://www.ebay.com/marketplace/resolution/v1/services">
  <caseId>
    <id>' . $caseId_id . '</id>
    <type>' . $caseId_type . '</type>
  </caseId>
</getActivityOptionsRequest>';
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $tryCount = 0;
        
        label1:
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('getActivityOptionsF')->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'times' => 1
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('getActivityOptionsF')->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'times' => 2
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (! XMLTool::IsXML($responseXml)) {
            iMongo::getInstance()->setCollection('getActivityOptionsBadXML')->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'tryCount' => $tryCount,
                    'times' => 3
                ));
            if ($tryCount < 22) {
                sleep(1);
                $tryCount ++;
                goto label1;
            }
        }
        
        return $responseXml;
    }

    /**
     * @desc 获取已经下载的Case数据
     * @param int $taskNumber
     * @author YangLong
     * @date 2015-03-30
     * @return Ambigous <string, multitype:, mixed>|boolean
     */
    public function getDownloadData($taskNumber = 1)
    {
        // 获取符合条件的数据
        $result = CaseDownDAO::getInstance()->getDownloadData($taskNumber);
        
        if (empty($result)) {
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
            'pick_time' => time()
        );
        $conditions = "down_id in ({$_ids})";
        CaseDownDAO::getInstance()->iupdate($columns, $conditions, array()); // 标记为正在处理
        
        CaseDownDAO::getInstance()->increase('pick_count', "down_id in ({$_ids})"); // 运行次数+1
        
        return $result;
    }

    /**
     * @desc 删除已经处理了的case原始数据 
     * @param string $ids            
     * @author YangLong
     * @date 2015-03-30
     * @return Ambigous <boolean, number>
     */
    public function deleteCaseDownData($ids)
    {
        return CaseDownDAO::getInstance()->deleteByIds($ids);
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

    /**
     * @desc 发起纠纷(BuyerHasNotPaid/TransactionMutuallyCanceled)，仅买家为付款或协商取消订单可以使用AddDispute发起
     * @param string $token
     * @param string $DisputeReason 发起纠纷原因
     * @param string $DisputeExplanation 纠纷代码
     * @param string $ItemID
     * @param string $OrderLineItemID
     * @param string $TransactionID 交易号
     * @param number $siteid 站点ID
     * @author liaojianwen,YangLong
     * @date 2015-04-21
     * @return string XML
     * @see http://developer.ebay.com/DevZone/XML/docs/Reference/ebay/AddDispute.html
     * @see http://developer.ebay.com/DevZone/XML/docs/Reference/ebay/types/DisputeExplanationCodeType.html
     */
    public function addDispute($token, $DisputeReason, $DisputeExplanation, $ItemID, $OrderLineItemID, $TransactionID, $siteid = 0)
    {
        $callName = 'AddDispute';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<AddDisputeRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>' . $token . '</eBayAuthToken>
  </RequesterCredentials>
  <DisputeReason>' . $DisputeReason . '</DisputeReason>
  <DisputeExplanation>' . $DisputeExplanation . '</DisputeExplanation>';
        if (! empty($ItemID)) {
            $requestXmlBody .= '
  <ItemID>' . $ItemID . '</ItemID>';
        }
        if (! empty($OrderLineItemID)) {
            $requestXmlBody .= '
  <OrderLineItemID>' . $OrderLineItemID . '</OrderLineItemID>';
        }
        if (! empty($TransactionID)) {
            $requestXmlBody .= '
  <TransactionID>' . $TransactionID . '</TransactionID>';
        }
        $requestXmlBody .= '
</AddDisputeRequest>';
        
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/ebay/AddDispute.html
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/index.html#Limitations
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:911";
        $session->headers[] = "X-EBAY-API-DEV-NAME:{$this->devID}";
        $session->headers[] = "X-EBAY-API-APP-NAME:{$this->appID}";
        $session->headers[] = "X-EBAY-API-CERT-NAME:{$this->certID}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        $session->headers[] = "X-EBAY-API-SITEID:{$siteid}";
        
        $tryCount = 0;
        
        label1:
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('addDisputeF')->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'times' => 1
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('addDisputeF')->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'times' => 2
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        return $responseXml;
    }

    /**
     * @desc 回复CASE
     * @param string $disputeActivity DisputeState value
     * @param string $disputeID 纠纷ID
     * @param string $token eBay token
     * @param string $messageText 信息内容
     * @param string $shipmentTrackNumber 物流单号
     * @param string $shippingCarrierUsed 物流承运商
     * @param string $shippingTime 发货时间
     * @param number $siteid 站点ID
     * @author YangLong
     * @date 2015-04-09
     * @return string XML
     */
    public function addDisputeResponse($disputeActivity, $disputeID, $token, $messageText = '', $siteid = 0, $shipmentTrackNumber = '', 
        $shippingCarrierUsed = '', $shippingTime = '')
    {
        $callName = 'AddDisputeResponse';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<AddDisputeResponseRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $requestXmlBody .= "
    <RequesterCredentials>
        <eBayAuthToken>{$token}</eBayAuthToken>
    </RequesterCredentials>
    <DisputeActivity>{$disputeActivity}</DisputeActivity>
    <DisputeID>{$disputeID}</DisputeID>";
        if (! empty($messageText)) {
            $messageText = htmlspecialchars($messageText, ENT_XML1);
            $requestXmlBody .= "
    <MessageText>{$messageText}</MessageText>";
        }
        if (! empty($shipmentTrackNumber)) {
            $requestXmlBody .= "
    <ShipmentTrackNumber>{$shipmentTrackNumber}</ShipmentTrackNumber>";
        }
        if (! empty($shippingCarrierUsed)) {
            $requestXmlBody .= "
    <ShippingCarrierUsed>{$shippingCarrierUsed}</ShippingCarrierUsed>";
        }
        if (! empty($shippingTime)) {
            $requestXmlBody .= "
    <ShippingTime>{$shippingTime}</ShippingTime>";
        }
        $requestXmlBody .= '
</AddDisputeResponseRequest>';
        
        // @see http://developer.ebay.com/Devzone/xml/docs/Reference/ebay/AddDisputeResponse.html
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/index.html#Limitations
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:911";
        $session->headers[] = "X-EBAY-API-DEV-NAME:{$this->devID}";
        $session->headers[] = "X-EBAY-API-APP-NAME:{$this->appID}";
        $session->headers[] = "X-EBAY-API-CERT-NAME:{$this->certID}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        $session->headers[] = "X-EBAY-API-SITEID:{$siteid}";
        
        $tryCount = 0;
        
        label1:
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('addDisputeResponseF')->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'times' => 1
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('addDisputeResponseF')->insert(
                array(
                    'requestXmlBody' => $requestXmlBody,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'times' => 2
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        return $responseXml;
    }

    /**
     * @desc 将CASE提交到eBay处理
     * @param string $appealReason DISAGREE_WITH_FINAL_DECISION/NEW_INFORMATION/OTHER
     * @param string $caseId
     * @param string $caseType EBP_INR/EBP_SNAD
     * @param string $comments Max length: 1000.
     * @author YangLong
     * @date 2015-04-14
     * @return string XML
     */
    public function appealToCustomerSupport($appealReason, $caseId, $caseType, $comments, $token)
    {
        $callName = 'appealToCustomerSupport';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        } else {
            $this->serverUrl = 'http://svcs.sandbox.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/CallRef/appealToCustomerSupport.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<appealToCustomerSupportRequest xmlns="http://www.ebay.com/marketplace/resolution/v1/services">
  <appealReason>' . $appealReason . '</appealReason>
  <caseId>
    <id>' . $caseId . '</id>
    <type>' . $caseType . '</type>
  </caseId>';
        if (! empty($comments)) {
            $requestXmlBody .= '
  <comments>' . $comments . '</comments>';
        }
        $requestXmlBody .= '
</appealToCustomerSupportRequest>';
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        // $session->headers[] = "X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        return $responseXml;
    }

    /**
     * @desc 将Case升级( Item Not Received => sellerINRReason ;Significantly Not As Described => sellerSNADReason )
     * @param string $caseId
     * @param string $caseType EBP_INR/EBP_SNAD
     * @param string $comments Max length: 1000.
     * @param string $token
     * @param string $sellerINRReason BUYER_STILL_UNHAPPY_AFTER_REFUND/ITEM_SHIPPED_WITH_TRACKING/OTHER/TROUBLE_COMMUNICATION_WITH_BUYER
     * @param string $sellerSNADReason BUYER_STILL_UNHAPPY_AFTER_REFUND/OTHER/TROUBLE_COMMUNICATION_WITH_BUYER
     * @param string $buyerINRReason ITEM_NOT_RECEIVED/OTHER/SELLER_NO_RESPONSE/TROUBLE_COMMUNICATION_WITH_SELLER
     * @param string $buyerSNADReason OTHER/SELLER_NO_RESPONSE/TROUBLE_COMMUNICATION_WITH_SELLER
     * @author YangLong
     * @date 2015-04-14
     * @return string XML
     */
    public function escalateToCustomerSupport($caseId, $caseType, $comments, $token, $sellerINRReason = '', $sellerSNADReason = '', 
        $buyerINRReason = '', $buyerSNADReason = '')
    {
        $callName = 'escalateToCustomerSupport';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        } else {
            $this->serverUrl = 'http://svcs.sandbox.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/CallRef/escalateToCustomerSupport.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<escalateToCustomerSupportRequest xmlns="http://www.ebay.com/marketplace/resolution/v1/services">
  <caseId>
    <id>' . $caseId . '</id>
    <type>' . $caseType . '</type>
  </caseId>';
        if (! empty($comments)) {
            $requestXmlBody .= '
  <comments>' . $comments . '</comments>';
        }
        $requestXmlBody .= '
  <escalationReason>';
        if (! empty($buyerINRReason)) {
            $requestXmlBody .= '
    <buyerINRReason>' . $buyerINRReason . '</buyerINRReason>';
        }
        if (! empty($buyerSNADReason)) {
            $requestXmlBody .= '
    <buyerSNADReason>' . $buyerSNADReason . '</buyerSNADReason>';
        }
        if (! empty($sellerINRReason)) {
            $requestXmlBody .= '
    <sellerINRReason>' . $sellerINRReason . '</sellerINRReason>';
        }
        if (! empty($sellerSNADReason)) {
            $requestXmlBody .= '
    <sellerSNADReason>' . $sellerSNADReason . '</sellerSNADReason>';
        }
        $requestXmlBody .= '
  </escalationReason>
</escalateToCustomerSupportRequest>';
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        // $session->headers[] = "X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        return $responseXml;
    }

    /**
     * @desc 全额退款
     * @param string $caseId
     * @param string $caseType EBP_INR/EBP_SNAD
     * @param string $comments Max length: 1000.
     * @param string $token
     * @author YangLong
     * @date 2015-04-14
     * @return string XML
     */
    public function issueFullRefund($caseId, $caseType, $comments, $token)
    {
        $callName = 'issueFullRefund';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        } else {
            $this->serverUrl = 'http://svcs.sandbox.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/CallRef/issueFullRefund.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<issueFullRefundRequest xmlns="http://www.ebay.com/marketplace/resolution/v1/services">
  <caseId>
    <id>' . $caseId . '</id>
    <type>' . $caseType . '</type>
  </caseId>';
        if (! empty($comments)) {
            $requestXmlBody .= '
  <comments>' . $comments . '</comments>';
        }
        $requestXmlBody .= '
</issueFullRefundRequest>';
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        // $session->headers[] = "X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        return $responseXml;
    }

    /**
     * @desc 部分退款
     * @param number $amount 退款金额
     * @param string $caseId 
     * @param string $caseType Applicable values: EBP_SNAD
     * @param string $comments Max length: 1000.
     * @param string $token
     * @author YangLong
     * @date 2015-04-14
     * @return string XML
     */
    public function issuePartialRefund($amount, $caseId, $caseType, $comments, $token)
    {
        $callName = 'issuePartialRefund';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        } else {
            $this->serverUrl = 'http://svcs.sandbox.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/CallRef/issuePartialRefund.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<issuePartialRefundRequest xmlns="http://www.ebay.com/marketplace/resolution/v1/services">
  <amount>' . $amount . '</amount>
  <caseId>
    <id>' . $caseId . '</id>
    <type>' . $caseType . '</type>
  </caseId>';
        if (! empty($comments)) {
            $requestXmlBody .= '
  <comments>' . $comments . '</comments>';
        }
        $requestXmlBody .= '
</issuePartialRefundRequest>';
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        // $session->headers[] = "X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        return $responseXml;
    }

    /**
     * @desc 提供其他解决方法
     * @param string $caseId
     * @param string $caseType EBP_INR/EBP_SNAD
     * @param string $messageToBuyer 必选，消息内容
     * @param string $token
     * @author YangLong
     * @date 2015-04-14
     * @return string XML
     */
    public function offerOtherSolution($caseId, $caseType, $messageToBuyer, $token)
    {
        $callName = 'offerOtherSolution';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        } else {
            $this->serverUrl = 'http://svcs.sandbox.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/CallRef/offerOtherSolution.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<offerOtherSolutionRequest xmlns="http://www.ebay.com/marketplace/resolution/v1/services">
  <caseId>
    <id>' . $caseId . '</id>
    <type>' . $caseType . '</type>
  </caseId>
  <messageToBuyer>' . htmlspecialchars($messageToBuyer, ENT_XML1) . '</messageToBuyer>
</offerOtherSolutionRequest>';
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        // $session->headers[] = "X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        return $responseXml;
    }

    /**
     * @desc 部分退款,payment method is PayPal.
     * @param number $amount 退款金额
     * @param string $caseId
     * @param string $caseType Applicable values: EBP_SNAD
     * @param string $comments 给买家的信息
     * @param string $token
     * @author YangLong
     * @date 2015-04-14
     * @return string XML
     */
    public function offerPartialRefund($amount, $caseId, $caseType, $comments, $token)
    {
        $callName = 'offerOtherSolution';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        } else {
            $this->serverUrl = 'http://svcs.sandbox.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/CallRef/offerPartialRefund.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<offerPartialRefundRequest xmlns="http://www.ebay.com/marketplace/resolution/v1/services">
  <amount>' . $amount . '</amount>
  <caseId>
    <id>' . $caseId . '</id>
    <type>' . $caseType . '</type>
  </caseId>';
        if (empty($comments)) {
            $requestXmlBody .= '
  <comments>' . $comments . '</comments>';
        }
        $requestXmlBody .= '
</offerPartialRefundRequest>';
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        // $session->headers[] = "X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        return $responseXml;
    }

    /**
     * @desc 提供退货地址, always EBP_SNAD for this call
     * @param string $caseId
     * @param string $token
     * @param string $returnMerchandiseAuthorization 商品授权码
     * @param string $additionalReturnInstructions 备注信息
     * @param string $city 城市
     * @param string $country 国家
     * @param string $name 姓名
     * @param string $postalCode 邮编
     * @param string $stateOrProvince 州/省
     * @param string $street1 街道1
     * @param string $street2 街道2
     * @author YangLong
     * @date 2015-04-14
     * @return string XML
     */
    public function offerRefundUponReturn($caseId, $token, $returnMerchandiseAuthorization = '', $additionalReturnInstructions = '', $city = '', 
        $country = '', $name = '', $postalCode = '', $stateOrProvince = '', $street1 = '', $street2 = '')
    {
        $callName = 'offerRefundUponReturn';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        } else {
            $this->serverUrl = 'http://svcs.sandbox.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/CallRef/offerRefundUponReturn.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<offerRefundUponReturnRequest xmlns="http://www.ebay.com/marketplace/resolution/v1/services">
  <caseId>
    <id>' . $caseId . '</id>
    <type>EBP_SNAD</type>
  </caseId>
  <returnAddress>';
        if (! empty($city)) {
            $requestXmlBody .= '
    <city></city>';
        }
        if (! empty($country)) {
            $requestXmlBody .= '
    <country></country>';
        }
        if (! empty($name)) {
            $requestXmlBody .= '
    <name></name>';
        }
        if (! empty($postalCode)) {
            $requestXmlBody .= '
    <postalCode></postalCode>';
        }
        if (! empty($stateOrProvince)) {
            $requestXmlBody .= '
    <stateOrProvince></stateOrProvince>';
        }
        if (! empty($street1)) {
            $requestXmlBody .= '
    <street1></street1>';
        }
        if (! empty($street2)) {
            $requestXmlBody .= '
    <street2></street2>';
        }
        $requestXmlBody .= '
  </returnAddress>';
        
        if (! empty($returnMerchandiseAuthorization)) {
            $requestXmlBody .= '
  <returnMerchandiseAuthorization></returnMerchandiseAuthorization>';
        }
        if (! empty($additionalReturnInstructions)) {
            $requestXmlBody .= '
  <additionalReturnInstructions></additionalReturnInstructions>';
        }
        $requestXmlBody .= '
</offerRefundUponReturnRequest>';
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        // $session->headers[] = "X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        return $responseXml;
    }

    /**
     * @desc 德国German (DE)卖家发送关于退款的自定义消息给买家
     * This call is used by German sellers to provide a customized message to the buyer regarding an item refund.
     * US and UK sellers will get an error if they attempt to use this call.
     * @param string $caseId Max length: 38.
     * @param string $caseType EBP_INR/EBP_SNAD
     * @param string $refundMessage Max length: 1000.
     * @param string $token
     * @author YangLong
     * @date 2015-04-14
     * @return string XML
     */
    public function provideRefundInfo($caseId, $caseType, $refundMessage, $token)
    {
        $callName = 'provideRefundInfo';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        } else {
            $this->serverUrl = 'http://svcs.sandbox.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/CallRef/provideRefundInfo.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<provideRefundInfoRequest xmlns="http://www.ebay.com/marketplace/resolution/v1/services">
  <caseId>
    <id>' . $caseId . '</id>
    <type>' . $caseType . '</type>
  </caseId>
  <refundMessage>' . $refundMessage . '</refundMessage>
</provideRefundInfoRequest>';
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        // $session->headers[] = "X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        return $responseXml;
    }

    /**
     * @desc 提供退货地址
     * provideReturnInfo cannot be used by UK or DE sellers for the 'RETURN' case type.
     * @param string $caseId
     * @param string $caseType EBP_INR/EBP_SNAD
     * @param string $token
     * @param string $city 城市
     * @param string $country 国家
     * @param string $name 姓名
     * @param string $postalCode 邮编
     * @param string $stateOrProvince 州/省
     * @param string $street1 街道1
     * @param string $street2 街道2
     * @param string $returnMerchandiseAuthorization 商品授权码
     * @author YangLong
     * @date 2015-04-14
     * @return string XML
     */
    public function provideReturnInfo($caseId, $caseType, $token, $city, $country, $name, $postalCode, $stateOrProvince, $street1, $street2, 
        $returnMerchandiseAuthorization = '')
    {
        $callName = 'provideReturnInfo';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        } else {
            $this->serverUrl = 'http://svcs.sandbox.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/CallRef/provideReturnInfo.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<provideReturnInfoRequest xmlns="http://www.ebay.com/marketplace/resolution/v1/services">
  <caseId>
    <id>' . $caseId . '</id>
    <type>' . $caseType . '</type>
  </caseId>
  <address>
    <city>' . $city . '</city>
    <country>' . $country . '</country>
    <name>' . $name . '</name>
    <postalCode>' . $postalCode . '</postalCode>
    <stateOrProvince>' . $stateOrProvince . '</stateOrProvince>
    <street1>' . $street1 . '</street1>
    <street2>' . $street2 . '</street2>
  </address>
  <returnMerchandiseAuthorization>' . $returnMerchandiseAuthorization . '</returnMerchandiseAuthorization>
</provideReturnInfoRequest>';
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        // $session->headers[] = "X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        return $responseXml;
    }

    /**
     * @desc 提供物流商名称和发货日期（无物流号）
     * @param string $caseId /Max length: 38
     * @param string $caseType EBP_INR/EBP_SNAD
     * @param string $carrierUsed 物流商名称/The name of the shipping carrier that is shipping the item to the buyer.
     * @param string $shippedDate 发货日期
     * @param string $token
     * @param string $comments 备注/Max length: 1000.
     * @author YangLong
     * @date 2015-04-14
     * @return string XML
     */
    public function provideShippingInfo($caseId, $caseType, $carrierUsed, $shippedDate, $token, $comments = '')
    {
        $callName = 'provideShippingInfo';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        } else {
            $this->serverUrl = 'http://svcs.sandbox.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/CallRef/provideShippingInfo.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<provideShippingInfoRequest xmlns="http://www.ebay.com/marketplace/resolution/v1/services">
  <caseId>
    <id>' . $caseId . '</id>
    <type>' . $caseType . '</type>
  </caseId>
  <carrierUsed>' . $carrierUsed . '</carrierUsed>';
        if (! empty($comments)) {
            $requestXmlBody .= '
  <comments>' . $comments . '</comments>';
        }
        $requestXmlBody .= '
  <shippedDate>' . $shippedDate . '</shippedDate>
</provideShippingInfoRequest>';
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        // $session->headers[] = "X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        return $responseXml;
    }

    /**
     * @desc 提供物流信息（提供物流号）
     * @param string $caseId
     * @param string $caseType EBP_INR/EBP_SNAD
     * @param string $carrierUsed 物流商名称
     * @param string $trackingNumber 物流号
     * @param string $token
     * @param string $comments 可选，备注
     * @author YangLong
     * @date 2015-04-14
     * @return string XML
     */
    public function provideTrackingInfo($caseId, $caseType, $carrierUsed, $trackingNumber, $token, $comments = '')
    {
        $callName = 'provideTrackingInfo';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        } else {
            $this->serverUrl = 'http://svcs.sandbox.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/CallRef/provideTrackingInfo.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<provideTrackingInfoRequest xmlns="http://www.ebay.com/marketplace/resolution/v1/services">
  <caseId>
    <id>' . $caseId . '</id>
    <type>' . $caseType . '</type>
  </caseId>
  <carrierUsed>' . $carrierUsed . '</carrierUsed>';
        if (! empty($comments)) {
            $requestXmlBody .= '
  <comments>' . $comments . '</comments>';
        }
        $requestXmlBody .= '
  <trackingNumber>' . $trackingNumber . '</trackingNumber>
</provideTrackingInfoRequest>';
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        // $session->headers[] = "X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        return $responseXml;
    }

    /**
     * @desc 德国(German)卖家上传证明文档
     * This call is used by German sellers to upload one or more documents (maximum of 5 per case) that act as proof that an item was shipped or proof that an order was fully or partially refunded.
     * US and UK sellers will get an error if they attempt to use this call.
     * @param string $caseId
     * @param string $caseType EBP_INR/EBP_SNAD
     * @param string $content 文档内容 [base64Binary] The binary representation of the proof document.
     * Supported file types for proof documents include JPEG, GIF, BMP, and PNG.
     * The upload operation will be unsuccessful for any other file type.
     * There is a file size limit of 1 MB per document.
     * @param string $name 文档名称
     * @param string $proofType OTHER/PROOF_OF_REFUND/PROOF_OF_SHIPPING
     * @param string $token
     * @author YangLong
     * @date 2015-04-14
     * @return string XML
     */
    public function uploadDocuments($caseId, $caseType, $content, $name, $proofType, $token)
    {
        $callName = 'uploadDocuments';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        } else {
            $this->serverUrl = 'http://svcs.sandbox.ebay.com/services/resolution/v1/ResolutionCaseManagementService';
        }
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/CallRef/uploadDocuments.html
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<uploadDocumentsRequest xmlns="http://www.ebay.com/marketplace/resolution/v1/services">
  <caseId>
    <id>' . $caseId . '</id>
    <type>' . $caseType . '</type>
  </caseId>
  <document>
    <content>' . $content . '</content>
    <name>' . $name . '</name>
  </document>
  <proofType>' . $proofType . '</proofType>
</uploadDocumentsRequest>';
        
        // @see http://developer.ebay.com/Devzone/resolution-case-management/Concepts/MakingACall.html
        $session = new eBaySession($this->serverUrl);
        // $session->headers[] = "X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-SOA-OPERATION-NAME:{$callName}";
        $session->headers[] = "X-EBAY-SOA-SECURITY-TOKEN:{$token}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        return $responseXml;
    }
}
