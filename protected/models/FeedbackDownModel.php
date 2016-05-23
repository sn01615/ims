<?php

/**
 * @desc 附加信息获取
 * @author liaojianwen
 * @date 2015-05-19
 */
class FeedbackDownModel extends BaseModel
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
     * @desc 覆盖父方法,返回当前类的(单)实例
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-05-19
     * @return FeedbackDownModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-05-19
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
     * @desc 获取已经下载的Feedback数据
     * @param int $taskNumber
     * @author liaojianwen
     * @date 2015-05-19
     * @return Ambigous <string, multitype:, mixed>|boolean
     */
    public function getFeedbackDownData($taskNumber)
    {
        FeedbackDownDAO::getInstance()->begintransaction();
        try {
            // 获取符合条件的数据
            $result = FeedbackDownDAO::getInstance()->getFeedbackDownData($taskNumber);
            
            if (empty($result)) {
                FeedbackDownDAO::getInstance()->rollback();
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
            FeedbackDownDAO::getInstance()->iupdate($columns, $conditions, array()); // 标记为正在处理
            
            FeedbackDownDAO::getInstance()->increase('pick_count', "down_id in ({$_ids})"); // 运行次数+1
            
            FeedbackDownDAO::getInstance()->commit();
            return $result;
        } catch (Exception $e) {
            FeedbackDownDAO::getInstance()->rollback();
            return false;
        }
    }

    /**
     * @desc 删除已经处理了的Feedback原始数据 
     * @param string $ids            
     * @author liaojianwen
     * @date 2015-05-19
     * @return Ambigous <boolean, number>
     */
    public function deleteFeedbackDownData($ids)
    {
        return FeedbackDownDAO::getInstance()->deleteByIds($ids);
    }

    /**
     * @desc 获取feedback 数据存入数据库
     * @author liaojianwen,YangLong
     * @date 2015-05-15
     */
    public function parseFeedback()
    {
        label:
        
        $result = FeedbackDownModel::model()->getFeedbackDownData(EnumOther::FEEDBACK_PARSE_SIZE);
        
        $ids = array();
        if ($result !== false) {
            foreach ($result as $key => &$value) {
                if (isset($value['text_json'])) {
                    $doc = phpQuery::newDocumentXML($value['text_json']);
                    phpQuery::selectDocument($doc);
                    if (pq('Ack')->html() == 'Success') {
                        $FeedbackDetailArray = pq('FeedbackDetailArray>FeedbackDetail');
                        $length = $FeedbackDetailArray->length;
                        for ($i = 0; $i < $length; $i ++) {
                            $FeedbackDetail = $FeedbackDetailArray->eq($i);
                            $columns = array(
                                'shop_id' => $value['shop_id'],
                                'CommentingUser' => $FeedbackDetail->find('CommentingUser')->html(),
                                'CommentingUserScore' => $FeedbackDetail->find('CommentingUserScore')->html(),
                                'CommentText' => $FeedbackDetail->find('CommentText')->html(),
                                'CommentTime' => strtotime($FeedbackDetail->find('CommentTime')->html()),
                                'CommentType' => $FeedbackDetail->find('CommentType')->html(),
                                'FeedbackResponse' => $FeedbackDetail->find('FeedbackResponse')->html(),
                                'ItemID' => $FeedbackDetail->find('ItemID')->html(),
                                'Role' => $FeedbackDetail->find('Role')->html(),
                                'TransactionID' => $FeedbackDetail->find('TransactionID')->html(),
                                'OrderLineItemID' => $FeedbackDetail->find('OrderLineItemID')->html(),
                                'FeedbackID' => $FeedbackDetail->find('FeedbackID')->html(),
                                'ItemTitle' => $FeedbackDetail->find('ItemTitle')->html(),
                                'ItemPrice' => $FeedbackDetail->find('ItemPrice')->html(),
                                'currencyID' => $FeedbackDetail->find('ItemPrice')->attr('currencyID'),
                                'create_time' => time()
                            );
                            if ($columns['FeedbackResponse']) {
                                $columns['isResponse'] = 1;
                            }
                            $conditions = 'FeedbackID=:FeedbackID';
                            $params = array(
                                ':FeedbackID' => $FeedbackDetail->find('FeedbackID')->html()
                            );
                            foreach ($columns as $k => $val) {
                                if (empty($val) || $val == 'Invalid Request') {
                                    unset($columns[$k]);
                                }
                            }
                            EbayFeedbackTransactionDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                        }
                    }
                }
                $ids[] = $value['down_id'];
            }
            unset($value);
            
            FeedbackDownModel::model()->deleteFeedbackDownData($ids);
            
            goto label;
        }
    }

    /**
     * @desc 获取feedback
     * @author liaojianwen
     * @date 2015-05-15
     * @param $page  integer 页数
     * @param $perPageCount  页码
     * @return string XML
     */
    public function getFeedback($token, $site_id, $page, $perPageCount)
    {
        $callName = 'GetFeedback'; // the call being made:
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        
        $requestXml = '<?xml version="1.0" encoding="utf-8"?>';
        
        $requestXml .= '<GetFeedbackRequest  xmlns="urn:ebay:apis:eBLBaseComponents">
      		<RequesterCredentials>
      		<eBayAuthToken>' . $token . '</eBayAuthToken>
      		</RequesterCredentials>
     		<Pagination> 
    		<EntriesPerPage>' . $perPageCount . '</EntriesPerPage>
   			 <PageNumber>' . $page . '</PageNumber>
  			</Pagination>
      		<DetailLevel>ReturnAll</DetailLevel></GetFeedbackRequest>';
        
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:919";
        $session->headers[] = "X-EBAY-API-DEV-NAME:{$this->devID}";
        $session->headers[] = "X-EBAY-API-APP-NAME:{$this->appID}";
        $session->headers[] = "X-EBAY-API-CERT-NAME:{$this->certID}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        $session->headers[] = "X-EBAY-API-SITEID:{$site_id}";
        
        $tryCount = 0;
        
        label1:
        
        $responseXml = $session->sendHttpRequest($requestXml);
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('getFeedbackFailure')->insert(
                array(
                    'requestXmlBody' => $requestXml,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'times' => 1
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXml);
        }
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('getFeedbackFailure')->insert(
                array(
                    'requestXmlBody' => $requestXml,
                    'responseXml' => $responseXml,
                    'time' => time(),
                    'times' => 2
                ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXml);
        }
        
        if (! XMLTool::IsXML($responseXml)) {
            iMongo::getInstance()->setCollection('getFeedbackBadXML')->insert(
                array(
                    'requestXmlBody' => $requestXml,
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
     * @desc 回复feedback
     * @param string $token
     * @param string $feedbackID
     * @param string $receiveID
     * @param string $responseText
     * @param string $siteId
     * @author liaojianwen
     * @date 2015-08-27
     * @return mixed
     */
    public function responseFeedback($token, $feedbackID, $receiveID, $responseText, $siteId)
    {
        $callName = 'RespondToFeedback';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        $version = $this->compatabilityLevel;
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?\>
                <RespondToFeedbackRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                <RequesterCredentials>
                <eBayAuthToken>' . $token . '</eBayAuthToken>
                </RequesterCredentials>
                <FeedbackID>' . $feedbackID . '</FeedbackID>
                <TargetUserID ComplexType="UserIDType">' . $receiveID . '</TargetUserID>
                <ResponseType EnumType="FeedbackResponseCodeType">Reply</ResponseType>
                <ResponseText>' . $responseText . '</ResponseText>
                </RespondToFeedbackRequest>';
        
        $session = new eBaySession($this->serverUrl);
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:{$version}";
        $session->headers[] = "X-EBAY-API-DEV-NAME:" . $this->devID;
        $session->headers[] = "X-EBAY-API-APP-NAME:" . $this->appID;
        $session->headers[] = "X-EBAY-API-CERT-NAME:" . $this->certID;
        $session->headers[] = "X-EBAY-API-SITEID:{$siteId}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        if ($responseXml === false) {
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        if ($responseXml === false) {
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        if ($responseXml === false) {
            sleep(4);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        return $responseXml;
    }
}