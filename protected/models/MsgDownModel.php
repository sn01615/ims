<?php

/**
 * @desc msg下载处理类
 * @author YangLong
 * @date 2015-04-21
 */
class MsgDownModel extends BaseModel
{

    private $compatabilityLevel; // eBay API version

    private $devID;

    private $appID;

    private $certID;

    private $serverUrl; // eBay 服务器地址

    private $userToken; // token

    private $siteToUseID; // site id

    /**
     * @desc 覆盖父方法返回MsgDownModel对象
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-03-26
     * @return MsgDownModel
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
        $this->compatabilityLevel = 923; // eBay API version
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
     * @desc 获取消息 version:937
     * @param string $token 必选
     * @param string $DetailLevel 必选，ReturnHeaders/ReturnMessages/ReturnSummary
     * @param int $FolderID 文件夹ID
     * @param int $StartTime 开始时间
     * @param int $EndTime 结束时间
     * @param array $MessageIDs 消息ID数组
     * @param number $siteid 站点ID
     * @param number $PageNumber 页码
     * @param number $EntriesPerPage 页大小
     * @param array $ExternalMessageIDs 如果使用了这个则比$MessageIDs优先级高
     * @param string $HighPriority 是否只返回重要信息
     * @param string $OutputSelector 输出字段过滤
     * @author YangLong
     * @date 2015-04-21
     * @return string XML
     */
    public function getMyMessages($token, $DetailLevel, $FolderID, $StartTime, $EndTime, $MessageIDs, $siteid = 0,
         $PageNumber = 1, $EntriesPerPage = 200, $ExternalMessageIDs = array(), $HighPriority = false, $OutputSelector = '')
    {
        $callName = 'GetMyMessages';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }

        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<GetMyMessagesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>' . $token . '</eBayAuthToken>
  </RequesterCredentials>';
        $requestXmlBody .= '
  <DetailLevel>' . $DetailLevel . '</DetailLevel>';
        if (! empty($FolderID)) {
            $requestXmlBody .= '
  <FolderID>' . $FolderID . '</FolderID>';
        }
        if (! empty($StartTime)) {
            $requestXmlBody .= '
  <StartTime>' . $this->fmtDate($StartTime) . '</StartTime>';
        }
        if (! empty($EndTime)) {
            $requestXmlBody .= '
  <EndTime>' . $this->fmtDate($EndTime) . '</EndTime>';
        }
        if (is_array($MessageIDs) && ! empty($MessageIDs)) {
            $requestXmlBody .= '
  <MessageIDs>';
            foreach ($MessageIDs as $msg) {
                $requestXmlBody .= '
    <MessageID>' . $msg . '</MessageID>';
            }
            $requestXmlBody .= '
  </MessageIDs>';
        }
        $requestXmlBody .= '
  <Pagination>';
        if (! empty($PageNumber)) {
            $requestXmlBody .= '
    <PageNumber>' . $PageNumber . '</PageNumber>';
        }
        if (! empty($EntriesPerPage)) {
            $requestXmlBody .= '
    <EntriesPerPage>' . $EntriesPerPage . '</EntriesPerPage>';
        }
        $requestXmlBody .= '
  </Pagination>';
        if (is_array($ExternalMessageIDs) && ! empty($ExternalMessageIDs)) {
            $requestXmlBody .= '
  <ExternalMessageIDs>';
            foreach ($ExternalMessageIDs as $emid) {
                $requestXmlBody .= '
    <ExternalMessageID>' . $emid . '</ExternalMessageID>';
            }
            $requestXmlBody .= '
  </ExternalMessageIDs>';
        }
        if ($HighPriority) {
            $requestXmlBody .= '
  <IncludeHighPriorityMessageOnly>true</IncludeHighPriorityMessageOnly>';
        }
        if (is_array($OutputSelector) && ! empty($OutputSelector)) {
            foreach ($OutputSelector as $os) {
                $requestXmlBody .= '
  <OutputSelector>' . $os . '</OutputSelector>';
            }
        }
        $requestXmlBody .= '
</GetMyMessagesRequest>';

        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/GetMemberMessages.html
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/index.html#Limitations
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:937";
        $session->headers[] = "X-EBAY-API-DEV-NAME:{$this->devID}";
        $session->headers[] = "X-EBAY-API-APP-NAME:{$this->appID}";
        $session->headers[] = "X-EBAY-API-CERT-NAME:{$this->certID}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        $session->headers[] = "X-EBAY-API-SITEID:{$siteid}";
        $session->headers[] = "Content-Type:text/xml";
        $session->headers[] = "Content-Length:" . strlen($requestXmlBody);

        $tryCount = 0;

        label1:

        $responseXml = $session->sendHttpRequest($requestXmlBody);

        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('getMyMessagesF')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'time' => time(),
                'times' => 1
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }

        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('getMyMessagesF')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'time' => time(),
                'times' => 2
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }

        if (! XMLTool::IsXML($responseXml)) {
            iMongo::getInstance()->setCollection('getMyMessagesBadXML')->insert(array(
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

        if (stripos($responseXml, '<ErrorClassification>SystemError</ErrorClassification>') && ! stripos($responseXml, '<ErrorCode>20118</ErrorCode>')) {
            if ($tryCount < 15) {
                $tryCount ++;
                sleep(5);
                goto label1;
            }
        }

        return $responseXml;
    }

    /**
     * @desc 回复消息
     * @param string $token eBay token
     * @param string $Body messages body
     * @param string $ParentMessageID 需要回复的消息的ID
     * @param array $RecipientIDArray 收件人eBay ID
     * @param string $ItemID ItemID, 如果没有收件人则这个必须输入
     * @param array $MessageMediaArray MediaName: Max length: 100. MediaURL: Max length: 200.
     * @param number $siteid
     * @param string $EmailCopyToSender
     * @param string $DisplayToPublic
     * @author YangLong
     * @date 2015-08-17
     * @return boolean|mixed
     */
    public function addMemberMessageRTQ($token, $Body, $ParentMessageID, $RecipientIDArray, $ItemID = '', $MessageMediaArray = array(), $siteid = 0, $MessageID = '', $EmailCopyToSender = '', $DisplayToPublic = '')
    {
        $callName = 'AddMemberMessageRTQ';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }

        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<AddMemberMessageRTQRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>' . $token . '</eBayAuthToken>
  </RequesterCredentials>';
        if (! empty($ItemID)) {
            $requestXmlBody .= '
  <ItemID>' . $ItemID . '</ItemID>';
        }
        $requestXmlBody .= '
  <MemberMessage>';
        if (! empty($Body)) {
            $requestXmlBody .= '
    <Body>' . $Body . '</Body>';
        }
        if (! empty($DisplayToPublic)) {
            $requestXmlBody .= '
    <DisplayToPublic>' . $DisplayToPublic . '</DisplayToPublic>';
        }
        if (! empty($EmailCopyToSender)) {
            $requestXmlBody .= '
    <EmailCopyToSender>' . $EmailCopyToSender . '</EmailCopyToSender>';
        }
        if (! empty($MessageMediaArray)) {
            $requestXmlBody .= '
    <MessageMedia>';
            foreach ($MessageMediaArray as $MessageMedia) {
                $requestXmlBody .= '
      <MediaName>' . $MessageMedia['MediaName'] . '</MediaName>';
                $requestXmlBody .= '
      <MediaURL>' . $MessageMedia['MediaURL'] . '</MediaURL>';
            }
            $requestXmlBody .= '
    </MessageMedia>';
        }
        if (! empty($ParentMessageID)) {
            $requestXmlBody .= '
    <ParentMessageID>' . $ParentMessageID . '</ParentMessageID>';
        }
        if (! empty($RecipientIDArray)) {
            foreach ($RecipientIDArray as $RecipientID) {
                $requestXmlBody .= '
    <RecipientID>' . $RecipientID . '</RecipientID>';
            }
        }
        $requestXmlBody .= '
  </MemberMessage>';
        if (! empty($MessageID)) {
            $requestXmlBody .= '
  <MessageID>' . $MessageID . '</MessageID>';
        }
        $requestXmlBody .= '
</AddMemberMessageRTQRequest>';

        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/AddMemberMessageRTQ.html
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/index.html#Limitations
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:933";
        $session->headers[] = "X-EBAY-API-DEV-NAME:{$this->devID}";
        $session->headers[] = "X-EBAY-API-APP-NAME:{$this->appID}";
        $session->headers[] = "X-EBAY-API-CERT-NAME:{$this->certID}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        $session->headers[] = "X-EBAY-API-SITEID:{$siteid}";
        $session->headers[] = "Content-Type:text/xml";
        $session->headers[] = "Content-Length:" . strlen($requestXmlBody);

        $tryCount = 0;

        label1:

        $responseXml = $session->sendHttpRequest($requestXmlBody);

        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('addMemberMessageRTQ')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'time' => time(),
                'times' => 1
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }

        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('addMemberMessageRTQ')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'time' => time(),
                'times' => 2
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }

        if (! XMLTool::IsXML($responseXml)) {
            iMongo::getInstance()->setCollection('addMemberMessageRTQBadXML')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'tryCount' => $tryCount,
                'time' => time()
            ));
            if ($tryCount < 0) {
                $tryCount ++;
                goto label1;
            }
            return false;
        }

        if (stripos($responseXml, '<ErrorClassification>SystemError</ErrorClassification>')) {
            if ($tryCount < 2) {
                $tryCount ++;
                sleep(5);
                goto label1;
            }
        }

        return $responseXml;
    }
    
    /**
     * @desc 给会员发送信息
     * @param string $token
     * @param string $Body 消息内容
     * @param string $ItemID itemid
     * @param array $RecipientIDArray 收件人数组
     * @param string $CorrelationID 关联ID
     * @param string $EmailCopyToSender
     * @param number $siteid
     * @author YangLong
     * @date 2015-10-16
     * @return string XML
     */
    public function addMemberMessagesAAQToBidder($token, $Body, $ItemID, $RecipientIDArray, $CorrelationID, $EmailCopyToSender = false, $siteid = 0)
    {
        $callName = 'AddMemberMessagesAAQToBidder';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<AddMemberMessagesAAQToBidderRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>' . $token . '</eBayAuthToken>
  </RequesterCredentials>';
        $requestXmlBody .= '
  <AddMemberMessagesAAQToBidderRequestContainer>';
        $requestXmlBody .= '
    <CorrelationID>' . $CorrelationID . '</CorrelationID>';
        $requestXmlBody .= '
    <ItemID>' . $ItemID . '</ItemID>';
        $requestXmlBody .= '
    <MemberMessage>
      <Body>' . $Body . '</Body>';
        if (! empty($EmailCopyToSender)) {
            $requestXmlBody .= '
      <EmailCopyToSender>' . $EmailCopyToSender . '</EmailCopyToSender>';
        }
        if (is_array($RecipientIDArray)) {
            foreach ($RecipientIDArray as $value) {
                $requestXmlBody .= '
      <RecipientID>' . $value . '</RecipientID>';
            }
        }
        $requestXmlBody .= '
    </MemberMessage>
  </AddMemberMessagesAAQToBidderRequestContainer>';
        $requestXmlBody .= '
</AddMemberMessagesAAQToBidderRequest>';
        
        // @see http://developer.ebay.com/Devzone/XML/docs/Reference/ebay/AddMemberMessagesAAQToBidder.html
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/index.html#Limitations
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:943";
        $session->headers[] = "X-EBAY-API-DEV-NAME:{$this->devID}";
        $session->headers[] = "X-EBAY-API-APP-NAME:{$this->appID}";
        $session->headers[] = "X-EBAY-API-CERT-NAME:{$this->certID}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        $session->headers[] = "X-EBAY-API-SITEID:{$siteid}";
        $session->headers[] = "Content-Type:text/xml";
        $session->headers[] = "Content-Length:" . strlen($requestXmlBody);
        
        $tryCount = 0;
        
        label1:
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('addMemberMessagesAAQToBidder')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'time' => time(),
                'times' => 1
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('addMemberMessagesAAQToBidder')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'time' => time(),
                'times' => 2
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (! XMLTool::IsXML($responseXml)) {
            iMongo::getInstance()->setCollection('addMemberMessagesAAQToBidderBadXML')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'tryCount' => $tryCount,
                'time' => time()
            ));
            if ($tryCount < 0) {
                $tryCount ++;
                goto label1;
            }
            return false;
        }
        
        if (stripos($responseXml, '<ErrorClassification>SystemError</ErrorClassification>')) {
            if ($tryCount < 2) {
                $tryCount ++;
                sleep(5);
                goto label1;
            }
        }
        
        return $responseXml;
    }
    
    /**
     * @desc 给buyer发送消息
     * @param string $token
     * @param string $Subject
     * @param string $Body
     * @param string $ItemID
     * @param array $RecipientIDArray
     * @param array $MessageMediaArray
     * @param string $QuestionType CustomCode/CustomizedSubject/General/MultipleItemShipping/None/Payment/Shipping
     * @param string $EmailCopyToSender
     * @param number $siteid
     * @author YangLong
     * @date 2015-10-16
     * @return string XML
     */
    public function addMemberMessageAAQToPartner($token, $Subject, $Body, $ItemID, $RecipientIDArray, $MessageMediaArray, $QuestionType, $EmailCopyToSender = false, $siteid = 0)
    {
        $callName = 'AddMemberMessageAAQToPartner';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<AddMemberMessageAAQToPartnerRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>' . $token . '</eBayAuthToken>
  </RequesterCredentials>';
        $requestXmlBody .= '
  <ItemID>' . $ItemID . '</ItemID>
  <MemberMessage>
    <Body>' . $Body . '</Body>';
        if (! empty($EmailCopyToSender)) {
            $requestXmlBody .= '
    <EmailCopyToSender>' . $EmailCopyToSender . '</EmailCopyToSender>';
        }
        $requestXmlBody .= '
    <MessageMedia>';
        if (is_array($MessageMediaArray)) {
            foreach ($MessageMediaArray as $value) {
                if (isset($value['MediaName']) && isset($value['MediaURL'])) {
                    $requestXmlBody .= '
      <MediaName>' . $value['MediaName'] . '</MediaName>
      <MediaURL>' . $value['MediaURL'] . '</MediaURL>';
                }
            }
        }
        $requestXmlBody .= '
    </MessageMedia>';
        $requestXmlBody .= '
    <QuestionType>' . $QuestionType . '</QuestionType>';
        if (is_array($RecipientIDArray)) {
            foreach ($RecipientIDArray as $value) {
                $requestXmlBody .= '
    <RecipientID>' . $value . '</RecipientID>';
            }
        }
        $requestXmlBody .= '
    <Subject>' . $Subject . '</Subject>
  </MemberMessage>';
        $requestXmlBody .= '
</AddMemberMessageAAQToPartnerRequest>';
        
        // @see http://developer.ebay.com/Devzone/XML/docs/Reference/ebay/AddMemberMessageAAQToPartner.html
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/index.html#Limitations
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:943";
        $session->headers[] = "X-EBAY-API-DEV-NAME:{$this->devID}";
        $session->headers[] = "X-EBAY-API-APP-NAME:{$this->appID}";
        $session->headers[] = "X-EBAY-API-CERT-NAME:{$this->certID}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        $session->headers[] = "X-EBAY-API-SITEID:{$siteid}";
        $session->headers[] = "Content-Type:text/xml";
        $session->headers[] = "Content-Length:" . strlen($requestXmlBody);
        
        $tryCount = 0;
        
        label1:
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('addMemberMessageAAQToPartner')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'time' => time(),
                'times' => 1
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('addMemberMessageAAQToPartner')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'time' => time(),
                'times' => 2
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }
        
        if (! XMLTool::IsXML($responseXml)) {
            iMongo::getInstance()->setCollection('addMemberMessageAAQToPartnerBadXML')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'tryCount' => $tryCount,
                'time' => time()
            ));
            if ($tryCount < 0) {
                $tryCount ++;
                goto label1;
            }
            return false;
        }
        
        if (stripos($responseXml, '<ErrorClassification>SystemError</ErrorClassification>')) {
            if ($tryCount < 2) {
                $tryCount ++;
                sleep(5);
                goto label1;
            }
        }
        
        return $responseXml;
    }
    
    /**
     * @desc 上传图片
     * @param string $token token
     * @param string $multiPartImageData 图片文件内容
     * @param string $PictureName 图片名称
     * @param number $siteid 站点ID
     * @param string $PictureWatermark 水印
     * @param string $MessageID 关联ID
     * @param string $ExternalPictureURL URL上传
     * @param string $PictureData
     * @param number $ExtensionInDays
     * @param string $PictureSet
     * @param string $PictureSystemVersion
     * @param string $PictureUploadPolicy
     * @author YangLong
     * @date 2015-08-18
     * @return boolean|mixed
     */
    public function uploadSiteHostedPictures($token, $multiPartImageData = '', $PictureName = '', $siteid = 0, $PictureWatermark = '',
         $MessageID = '', $ExternalPictureURL = '', $PictureData = '', $ExtensionInDays = 0, $PictureSet = 'Supersize', $PictureSystemVersion = '2', $PictureUploadPolicy = '')
    {
        $callName = 'UploadSiteHostedPictures';
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }

        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
        $requestXmlBody .= '
<UploadSiteHostedPicturesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>' . $token . '</eBayAuthToken>
  </RequesterCredentials>';
        if (! empty($ExtensionInDays)) {
            $requestXmlBody .= '
  <ExtensionInDays>' . $ExtensionInDays . '</ExtensionInDays>';
        }
        if (! empty($ExternalPictureURL)) {
            $requestXmlBody .= '
  <ExternalPictureURL>' . $ExternalPictureURL . '</ExternalPictureURL>';
        }
        if (! empty($PictureData)) {
            $requestXmlBody .= '
  <PictureData contentType="string">' . $PictureData . '</PictureData>';
        }
        if (! empty($PictureName)) {
            $requestXmlBody .= '
  <PictureName>' . $PictureName . '</PictureName>';
        }
        if (! empty($PictureSet)) {
            $requestXmlBody .= '
  <PictureSet>' . $PictureSet . '</PictureSet>';
        }
        if (! empty($PictureSystemVersion)) {
            $requestXmlBody .= '
  <PictureSystemVersion>' . $PictureSystemVersion . '</PictureSystemVersion>';
        }
        if (! empty($PictureUploadPolicy)) {
            $requestXmlBody .= '
  <PictureUploadPolicy></PictureUploadPolicy>';
        }
        if (! empty($PictureWatermark)) {
            $requestXmlBody .= '
  <PictureWatermark>' . $PictureWatermark . '</PictureWatermark>';
        }
        if (! empty($MessageID)) {
            $requestXmlBody .= '
  <MessageID>' . $MessageID . '</MessageID>';
        }
        $requestXmlBody .= '
</UploadSiteHostedPicturesRequest>';

        $boundary = "MIME_boundary";
        $CRLF = "\r\n";

        // The complete POST consists of an XML request plus the binary image separated by boundaries
        $firstPart = '';
        $firstPart .= "--" . $boundary . $CRLF;
        $firstPart .= 'Content-Disposition: form-data; name="XML Payload"' . $CRLF;
        $firstPart .= 'Content-Type: text/xml;charset=utf-8' . $CRLF . $CRLF;
        $firstPart .= $requestXmlBody;
        $firstPart .= $CRLF;

        $secondPart = '';
        $secondPart .= "--" . $boundary . $CRLF;
        $secondPart .= 'Content-Disposition: form-data; name="dummy"; filename="dummy"' . $CRLF;
        $secondPart .= "Content-Transfer-Encoding: binary" . $CRLF;
        $secondPart .= "Content-Type: application/octet-stream" . $CRLF . $CRLF;
        $secondPart .= $multiPartImageData;
        $secondPart .= $CRLF;
        $secondPart .= "--" . $boundary . "--" . $CRLF;

        $requestXmlBody = $firstPart . $secondPart;

        // @see http://developer.ebay.com/devzone/xml/docs/reference/ebay/uploadsitehostedpictures.html
        // @see http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/index.html#Limitations
        $session = new eBaySession($this->serverUrl);
        // $session->headers[]="X-EBAY-SOA-GLOBAL-ID:EBAY-US";
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:933";
        $session->headers[] = "X-EBAY-API-DEV-NAME:{$this->devID}";
        $session->headers[] = "X-EBAY-API-APP-NAME:{$this->appID}";
        $session->headers[] = "X-EBAY-API-CERT-NAME:{$this->certID}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        $session->headers[] = "X-EBAY-API-SITEID:{$siteid}";
        $session->headers[] = "Content-Type: multipart/form-data; boundary={$boundary}";
        $session->headers[] = "Content-Length:" . strlen($requestXmlBody);

        $tryCount = 0;

        label1:

        $responseXml = $session->sendHttpRequest($requestXmlBody);

        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('uploadSiteHostedPictures')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'time' => time(),
                'times' => 1
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }

        if (stripos($responseXml, '<Ack>Failure</Ack>')) {
            iMongo::getInstance()->setCollection('uploadSiteHostedPictures')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'time' => time(),
                'times' => 2
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXmlBody);
        }

        if (! XMLTool::IsXML($responseXml)) {
            iMongo::getInstance()->setCollection('uploadSiteHostedPicturesBadXML')->insert(array(
                'requestXmlBody' => $requestXmlBody,
                'responseXml' => $responseXml,
                'tryCount' => $tryCount,
                'time' => time()
            ));
            if ($tryCount < 5) {
                $tryCount ++;
                goto label1;
            }
            return false;
        }

        if (stripos($responseXml, '<ErrorClassification>SystemError</ErrorClassification>')) {
            if ($tryCount < 10) {
                $tryCount ++;
                sleep(5);
                goto label1;
            }
        }

        return $responseXml;
    }

    /**
     * @desc 获取格式化的GMT时间
     * @param int $date
     * @return string
     * @author YangLong
     * @date 2015-02-12
     */
    private function fmtDate($date)
    {
        return gmdate('Y-m-d\TH:i:s\Z',$date);
    }

    /**
     * @desc 新的队列运行机制
     * @param number $pickSize 一次取多少行队列数据
     * @author YangLong
     * @date 2015-06-11
     * @return null|boolean
     */
    public function executeMsgDownQueueXml($pickSize = 5)
    {
        DaemonLockTool::lock(__METHOD__ . gmdate('i'));
        
        $startTime = time();
        
        label1:
        
        if (time() - $startTime > 600) {
            return false;
        }
        
        $pagesize = 20;
        
        $Queues = MsgDownDAO::getInstance()->getDownQueueData($pickSize);
        if ($Queues !== false) {
            foreach ($Queues as $key => $Queue) {
                $page = 0;
                while (true) {
                    $page ++;
                    $xmlArr = array();
                    $xmlArr['AccountID'] = $Queue['AccountID'];
                    $xmlArr['list'] = $this->getMyMessages($Queue['token'], 'ReturnHeaders', $Queue['folder_id'], $Queue['start_time'], $Queue['end_time'], '', 0, $page, $pagesize);
                    
                    $doc = phpQuery::newDocumentXML($xmlArr['list']);
                    phpQuery::selectDocument($doc);
                    if (pq('Ack')->html() === 'Success') {
                        $Messages = pq('Messages>Message');
                        $length = $Messages->length;
                        $msgids = array();
                        for ($i = 0; $i < $length; $i ++) {
                            $_msg = $Messages->eq($i);
                            $msgids[] = $_msg->find('>MessageID')->html();
                            if (count($msgids) === 10 || $length == $i + 1) {
                                $xmlArr['details'][] = $this->getMyMessages($Queue['token'], 'ReturnMessages', '', '', '', $msgids, 0);
                                $msgids = array();
                            }
                        }
                        
                        $columns = array(
                            'seller_id' => $Queue['seller_id'],
                            'shop_id' => $Queue['shop_id'],
                            'text_json' => base64_encode(serialize($xmlArr)),
                            'create_time' => time(),
                            'version' => 1
                        );
                        MsgDownDAO::getInstance()->iinsert($columns);
                        
                        MsgDownDAO::getInstance()->deleteDownQueue($Queue['down_queue_id']);
                    } else {
                        iMongo::getInstance()->setCollection('v1MsgDownXmlErr')->insert(array(
                            'Queue' => $Queue,
                            'list' => $xmlArr['list'],
                            'page' => $page,
                            'time' => time()
                        ));
                    }
                    
                    phpQuery::selectDocument($doc);
                    if (pq('Messages>Message')->length < $pagesize) {
                        break;
                    }
                }
            }
            
            goto label1;
        } else {
            sleep(5);
            goto label1;
        }
    }
    
    /**
     * @desc 获取用户信息
     * @author YangLong
     * @date 2015-06-12
     * @return null
     */
    public function getUserInfo()
    {
        DaemonLockTool::lock(__METHOD__);

        $startTime = time();

        label1:

        if (time() - $startTime > 600) {
            return false;
        }

        $columns = array(
            'ebay_user_info_id',
            'shop_id',
            'EIASToken',
            'UserID',
            'RItemId',
            'getinterval'
        );
        $conditions = 'last_get_time<:last_get_time+getinterval*getinterval*3600*6';
        $params = array(
            ':last_get_time' => time()
        );
        $Queues = EbayUserInfoDAO::getInstance()->iselect($columns, $conditions, $params, true, array(), '', 'last_get_time', 100);

        $ids = array();
        foreach ($Queues as $_key => $_value) {
            $ids[] = $_value['ebay_user_info_id'];
        }
        $ids = implode(',', $ids);
        $field = 'getinterval';
        $conditions = "ebay_user_info_id in ({$ids})";
        if (! empty($ids)) {
            EbayUserInfoDAO::getInstance()->increase($field, $conditions);
        }

        $QueueIds = array();
        foreach ($Queues as $Queue) {
            $QueueIds[] = $Queue['ebay_user_info_id'];
        }
        if (! empty($QueueIds)) {
            $columns = array(
                'last_get_time' => time()
            );
            $conditions = 'ebay_user_info_id in (' . implode(',', $QueueIds) . ')';
            $params = array();
            EbayUserInfoDAO::getInstance()->iupdate($columns, $conditions, $params);
        }

        $_cacheKey = md5(__METHOD__ . '$shops2');
        $shops2 = iMemcache::getInstance()->get($_cacheKey);
        if ($shops2 === false) {
            $columns = array(
                'shop_id',
                'token'
            );
            $conditions = 'is_delete=' . boolConvert::toInt01(false);
            $shops = ShopDAO::getInstance()->iselect($columns, $conditions, array());

            $shops2 = array();
            foreach ($shops as $value) {
                $shops2[$value['shop_id']] = $value['token'];
            }
            unset($shops);

            iMemcache::getInstance()->set($_cacheKey, $shops2, 60);
        }

        foreach ($Queues as $Queue) {

            if ($Queue['UserID'] == 'csfeedback@ebay.com' || $Queue['UserID'] == 'eBay') {
                continue;
            }

            $key = md5(__METHOD__ . 'lock' . $Queue['UserID']);
            $lock = iMemcache::getInstance()->get($key);
            if ($lock === false) {
                $lock = iMemcache::getInstance()->set($key, true, 600);
            } else {
                continue;
            }

            if (isset($shops2[$Queue['shop_id']])) {
                labelx:
                if (empty($Queue['RItemId'])) {
                    $xml = EbayOtherInfoModel::model()->eBayGetUser($shops2[$Queue['shop_id']], $Queue['UserID']);
                } else {
                    $xml = EbayOtherInfoModel::model()->eBayGetUser($shops2[$Queue['shop_id']], $Queue['UserID'], $Queue['RItemId'], 'ReturnAll');
                }

                if (stripos($xml, '<ErrorCode>17420</ErrorCode>')) {
                    $Queue['RItemId'] = false;
                    goto labelx;
                }

                // preg_match('/<ErrorCode>(\d+)<\/ErrorCode>/', $xml, $matches);
            } else {
                continue;
            }

            $doc = phpQuery::newDocumentXML($xml);
            phpQuery::selectDocument($doc);

            if (pq('Ack')->html() !== 'Success') {
                if (pq('Ack')->html() !== 'Warning') {
                    iMongo::getInstance()->setCollection('getUserInfoNoSW')->insert(array(
                        'UserID' => $Queue['UserID'],
                        'shop_id' => $Queue['shop_id'],
                        'RItemId' => $Queue['RItemId'],
                        'xml' => $xml,
                        'time' => time()
                    ));
                    continue;
                }
            }

            $columns = array(
                'shop_id' => $Queue['shop_id'],
                'EIASToken' => pq('User>EIASToken')->html(),
                'UserID' => pq('User>UserID')->html(),
                'Email' => pq('User>Email')->html(),
                'UniqueNegativeFeedbackCount' => pq('User>UniqueNegativeFeedbackCount')->html(),
                'UniqueNeutralFeedbackCount' => pq('User>UniqueNeutralFeedbackCount')->html(),
                'UniquePositiveFeedbackCount' => pq('User>UniquePositiveFeedbackCount')->html(),
                'RegistrationDate' => strtotime(pq('User>RegistrationDate')->html()),
                'UserIDChanged' => boolConvert::toInt01(pq('User>UserIDChanged')->html()),
                'UserIDLastChanged' => strtotime(pq('User>UserIDLastChanged')->html()),
                'AboutMePage' => boolConvert::toInt01(pq('User>AboutMePage')->html()),
                'BillingEmail' => pq('User>BillingEmail')->html(),
                'BusinessRole' => pq('User>BusinessRole')->html(),
                'eBayGoodStanding' => boolConvert::toInt01(pq('User>eBayGoodStanding')->html()),
                'eBayWikiReadOnly' => boolConvert::toInt01(pq('User>eBayWikiReadOnly')->html()),
                'EnterpriseSeller' => boolConvert::toInt01(pq('User>EnterpriseSeller')->html()),
                'FeedbackPrivate' => boolConvert::toInt01(pq('User>FeedbackPrivate')->html()),
                'FeedbackRatingStar' => boolConvert::toInt01(pq('User>FeedbackRatingStar')->html()),
                'FeedbackScore' => boolConvert::toInt01(pq('User>FeedbackScore')->html()),
                'IDVerified' => boolConvert::toInt01(pq('User>IDVerified')->html()),
                'MotorsDealer' => boolConvert::toInt01(pq('User>MotorsDealer')->html()),
                'NewUser' => boolConvert::toInt01(pq('User>NewUser')->html()),
                'PayPalAccountLevel' => pq('User>PayPalAccountLevel')->html(),
                'PayPalAccountStatus' => pq('User>PayPalAccountStatus')->html(),
                'PayPalAccountType' => pq('User>PayPalAccountType')->html(),
                'PositiveFeedbackPercent' => pq('User>PositiveFeedbackPercent')->html(),
                'QualifiesForSelling' => boolConvert::toInt01(pq('User>QualifiesForSelling')->html()),
                'regaddr_CityName' => pq('User>RegistrationAddress>CityName')->html(),
                'regaddr_CompanyName' => pq('User>RegistrationAddress>CompanyName')->html(),
                'regaddr_Country' => pq('User>RegistrationAddress>Country')->html(),
                'regaddr_CountryName' => pq('User>RegistrationAddress>CountryName')->html(),
                'regaddr_Name' => pq('User>RegistrationAddress>Name')->html(),
                'regaddr_Phone' => pq('User>RegistrationAddress>Phone')->html(),
                'regaddr_PostalCode' => pq('User>RegistrationAddress>PostalCode')->html(),
                'regaddr_StateOrProvince' => pq('User>RegistrationAddress>StateOrProvince')->html(),
                'regaddr_Street' => pq('User>RegistrationAddress>Street')->html(),
                'regaddr_Street1' => pq('User>RegistrationAddress>Street1')->html(),
                'regaddr_Street2' => pq('User>RegistrationAddress>Street2')->html(),
                'si_AllowPaymentEdit' => boolConvert::toInt01(pq('User>SellerInfo>AllowPaymentEdit')->html()),
                'si_CharityAffiliationDetailsXML' => pq('User>SellerInfo>CharityAffiliationDetails')->html(),
                'si_CharityRegistered' => boolConvert::toInt01(pq('User>SellerInfo>CharityRegistered')->html()),
                'si_CheckoutEnabled' => boolConvert::toInt01(pq('User>SellerInfo>CheckoutEnabled')->html()),
                'si_CIPBankAccountStored' => boolConvert::toInt01(pq('User>SellerInfo>CIPBankAccountStored')->html()),
                'si_DomesticRateTable' => boolConvert::toInt01(pq('User>SellerInfo>DomesticRateTable')->html()),
                'si_fe_QualifiedForAuctionOneDayDuration' => boolConvert::toInt01(pq('User>SellerInfo>FeatureEligibility>QualifiedForAuctionOneDayDuration')->html()),
                'si_fe_QualifiedForFixedPriceOneDayDuration' => boolConvert::toInt01(pq('User>SellerInfo>FeatureEligibility>QualifiedForFixedPriceOneDayDuration')->html()),
                'si_fe_QualifiesForBuyItNow' => boolConvert::toInt01(pq('User>SellerInfo>FeatureEligibility>QualifiesForBuyItNow')->html()),
                'si_fe_QualifiesForBuyItNowMultiple' => boolConvert::toInt01(pq('User>SellerInfo>FeatureEligibility>QualifiesForBuyItNowMultiple')->html()),
                'si_fe_QualifiesForVariations' => boolConvert::toInt01(pq('User>SellerInfo>FeatureEligibility>QualifiesForVariations')->html()),
                'si_GoodStanding' => boolConvert::toInt01(pq('User>SellerInfo>GoodStanding')->html()),
                'si_IntegratedMerchantCreditCardInfoXML' => pq('User>SellerInfo>IntegratedMerchantCreditCardInfo')->html(),
                'si_InternationalRateTable' => boolConvert::toInt01(pq('User>SellerInfo>InternationalRateTable')->html()),
                'si_MerchandizingPref' => pq('User>SellerInfo>MerchandizingPref')->html(),
                'si_PaisaPayEscrowEMIStatus' => pq('User>SellerInfo>PaisaPayEscrowEMIStatus')->html(),
                'si_PaisaPayStatus' => pq('User>SellerInfo>PaisaPayStatus')->html(),
                'si_PaymentMethod' => pq('User>SellerInfo>PaymentMethod')->html(),
                'si_QualifiesForB2BVAT' => boolConvert::toInt01(pq('User>SellerInfo>QualifiesForB2BVAT')->html()),
                'si_RecoupmentPolicyConsentXML' => pq('User>SellerInfo>RecoupmentPolicyConsent')->html(),
                'si_RegisteredBusinessSeller' => boolConvert::toInt01(pq('User>SellerInfo>RegisteredBusinessSeller')->html()),
                'si_SafePaymentExempt' => boolConvert::toInt01(pq('User>SellerInfo>SafePaymentExempt')->html()),
                'si_si_MaxScheduledItems' => pq('User>SellerInfo>SchedulingInfo>MaxScheduledItems')->html(),
                'si_si_MaxScheduledMinutes' => pq('User>SellerInfo>SchedulingInfo>MaxScheduledMinutes')->html(),
                'si_si_MinScheduledMinutes' => pq('User>SellerInfo>SchedulingInfo>MinScheduledMinutes')->html(),
                'si_SellerBusinessType' => pq('User>SellerInfo>SellerBusinessType')->html(),
                'si_SellerLevel' => pq('User>SellerInfo>SellerLevel')->html(),
                'si_SellerPaymentAddressXML' => pq('User>SellerInfo>SellerPaymentAddress')->html(),
                'si_StoreOwner' => boolConvert::toInt01(pq('User>SellerInfo>StoreOwner')->html()),
                'si_StoreSite' => pq('User>SellerInfo>StoreSite')->html(),
                'si_StoreURL' => pq('User>SellerInfo>StoreURL')->html(),
                'si_TopRatedSeller' => boolConvert::toInt01(pq('User>SellerInfo>TopRatedSeller')->html()),
                'si_TopRatedSellerDetailsXML' => pq('User>SellerInfo>TopRatedSellerDetails')->html(),
                'si_TransactionPercent' => pq('User>SellerInfo>TransactionPercent')->html(),
                'Site' => pq('User>Site')->html(),
                'Status' => pq('User>Status')->html(),
                'TUVLevel' => pq('User>TUVLevel')->html(),
                'UserSubscriptionValues' => pq('User>UserSubscriptionValues')->html(),
                'VATID' => pq('User>VATID')->html(),
                'VATStatus' => pq('User>VATStatus')->html(),
                'update_time' => time()
            );
            $conditions = 'UserID=:UserID';
            $params = array(
                ':UserID' => $Queue['UserID']
            );

            $SkypeIDs = pq('User>SkypeID');
            $length = $SkypeIDs->length;
            $SkypeIDsArray = array();
            for ($i = 0; $i < $length; $i ++) {
                $SkypeIDsArray[] = $SkypeIDs->eq($i)->html();
            }
            $columns['SkypeIDValues'] = implode(',', $SkypeIDsArray);
            
            foreach ($columns as $key => $value) {
                if ($value === null || $value === false || $value === 'Invalid Request') {
                    unset($columns[$key]);
                }
            }
            
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
                echo "\n" . 'UserID:' . "\n";
                echo $columns['UserID'] . "\n";
                echo 'desc: 严重错误:[getUserInfo]ebay_user_info数据重复。' . "\n";
                echo '$_upk:' . "\n";
                var_dump($_upk);
                $text = ob_get_clean();
                $subject = "[Error.data.duplication][buyer]Fatal error: data duplication\n";
                $to = Yii::app()->params['logmails'];
                SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            }
            
            $columns = array(
                'ebay_user_info_id' => $_upk,
                'shop_id' => $Queue['shop_id']
            );
            $conditions = 'ebay_user_info_id=:ebay_user_info_id and shop_id=:shop_id';
            $params = array(
                ':ebay_user_info_id' => $_upk,
                ':shop_id' => $Queue['shop_id']
            );
            EbayUserShopsDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
        }

        if (count($Queues) < 100) {
            sleep(5);
        }
        goto label1;
    }

    /**
     * @desc 解析消息V1
     * @author YangLong
     * @date 2015-06-19
     * @return null
     */
    public function parseMessagesV1()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $startTime = time();
        
        label1:
        
        if (time() - $startTime > 600) {
            return false;
        }
        
        $columns = array(
            'down_id',
            'seller_id',
            'shop_id',
            'text_json'
        );
        $conditions = '(v1status=:v1status and version=1) or (v1lrt<:v1lrt and v1count<=:v1count and version=1)';
        $params = array(
            ':v1status' => boolConvert::toInt01(false),
            ':v1lrt' => time() - 300,
            ':v1count' => 4
        );
        
        $dataArr = MsgDownDAO::getInstance()->iselect($columns, $conditions, $params, false);
        
        if (empty($dataArr)) {
            goto label2;
        }
        
        $columns = array(
            'v1status' => boolConvert::toInt01(true)
        );
        $conditions = 'down_id=:down_id';
        $params = array(
            ':down_id' => $dataArr['down_id']
        );
        // lock
        MsgDownDAO::getInstance()->iupdate($columns, $conditions, $params);
        
        $dataArr['text_json'] = unserialize(base64_decode($dataArr['text_json']));
        
        // list
        $doc = phpQuery::newDocumentXML($dataArr['text_json']['list']);
        phpQuery::selectDocument($doc);
        if (pq('Ack')->html() == 'Success') {
            $Messages = pq('Messages>Message');
            $length = $Messages->length;
            for ($i = 0; $i < $length; $i ++) {
                $Message = $Messages->eq($i);
                
                $columns = array(
                    'shop_id' => $dataArr['shop_id'],
                    'Sender' => $Message->find('Sender')->html(),
                    'SendingUserID' => $Message->find('SendingUserID')->html(),
                    'RecipientUserID' => $Message->find('RecipientUserID')->html(),
                    'SendToName' => $Message->find('SendToName')->html(),
                    'Subject' => html_entity_decode($Message->find('Subject')->html()),
                    'MessageID' => $Message->find('MessageID')->html(),
                    'ExternalMessageID' => $Message->find('ExternalMessageID')->html(),
                    'Flagged' => boolConvert::toStr01($Message->find('Flagged')->html()),
                    'Read' => boolConvert::toStr01($Message->find('Read')->html()),
                    'ReceiveDate' => strtotime($Message->find('ReceiveDate')->html()),
                    'ExpirationDate' => strtotime($Message->find('ExpirationDate')->html()),
                    'ResponseEnabled' => boolConvert::toStr01($Message->find('ResponseDetails>ResponseEnabled')->html()),
                    'UserResponseDate' => strtotime($Message->find('ResponseDetails>UserResponseDate')->html()),
                    'FolderID' => $Message->find('Folder>FolderID')->html(),
                    'MessageType' => $Message->find('MessageType')->html(),
                    'Replied' => boolConvert::toStr01($Message->find('Replied')->html()),
                    'HighPriority' => boolConvert::toStr01($Message->find('HighPriority')->html()),
                    'ItemID' => $Message->find('ItemID')->html(),
                    'ItemEndTime' => strtotime($Message->find('ItemEndTime')->html()),
                    'ItemTitle' => $Message->find('ItemTitle')->html(),
                    'ListingStatus' => $Message->find('ListingStatus')->html(),
                    'QuestionType' => $Message->find('QuestionType')->html(),
                    'update_time' => time()
                );
                $conditions = 'shop_id=:shop_id and MessageID=:MessageID';
                $params = array(
                    ':shop_id' => $dataArr['shop_id'],
                    ':MessageID' => $Message->find('MessageID')->html()
                );
                
                foreach ($columns as $_tempkey => $_tempvalue) {
                    if ($_tempvalue === false || $_tempvalue === null) {
                        unset($columns[$_tempkey]);
                    }
                }
                
                $msgpk = MsgDAO::getInstance()->ireplaceinto($columns, $conditions, $params, true);
                
                if (is_array($msgpk)) {
                    // 消息数据重复
                    // 发送邮件通知
                    ob_start();
                    echo '$msgpk:';
                    var_dump($msgpk);
                    $text = ob_get_clean();
                    $subject = "Fatal error: msg 数据重复";
                    $to = Yii::app()->params['logmails'];
                    SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
                    
                    $msgpk = array_pop($msgpk);
                    if (is_array($msgpk)) {
                        $msgpk = array_pop($msgpk);
                    }
                }
                
                // URL字段分表
                $columns = array(
                    'msg_id' => $msgpk,
                    'response_rul' => $Message->find('ResponseDetails>ResponseURL')->html()
                );
                $conditions = 'msg_id=:msg_id';
                $params = array(
                    ':msg_id' => $msgpk
                );
                MsgResponseUrlDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                
                // 图片字段分出来
                $_length = $Message->find('MessageMedia')->length;
                for ($j = 0; $j < $_length; $j ++) {
                    $_media = $Message->find('MessageMedia')->eq($j);
                    $columns = array(
                        'msg_id' => $msgpk,
                        'number' => $j + 1,
                        'media_name' => $_media->find('MediaName')->html(),
                        'media_url' => $_media->find('MediaURL')->html()
                    );
                    $conditions = 'msg_id=:msg_id and number=:number';
                    $params = array(
                        ':msg_id' => $msgpk,
                        ':number' => $j + 1
                    );
                    MsgMediasDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                }
                
                // 写用户表
                $columns = array(
                    'shop_id' => $dataArr['shop_id'],
                    'UserID' => $Message->find('Sender')->html(),
                    'RItemId' => $Message->find('ItemID')->html(),
                    'getinterval' => 0
                );
                $conditions = 'UserID=:UserID';
                $params = array(
                    ':UserID' => $Message->find('Sender')->html()
                );
                
                foreach ($columns as $_tempkey => $_tempvalue) {
                    if ($_tempvalue === false || $_tempvalue === null) {
                        unset($columns[$_tempkey]);
                    }
                }
                
                if ($columns['UserID'] != 'eBay' && stripos($columns['UserID'], '@ebay.com') === false) {
                    $_upk = EbayUserInfoDAO::getInstance()->ireplaceinto($columns, $conditions, $params, true);
                    
                    // 数据有重复
                    if (is_array($_upk)) {
                        imsTool::clearDuplication('EbayUserInfoDAO', $_upk);
                        $_upk = array_shift($_upk);
                        $_upk = array_shift($_upk);
                        
                        // 发送邮件通知
                        ob_start();
                        echo "date: ";
                        echo date('Y-m-d H:i:s');
                        echo "\ngmdate: ";
                        echo gmdate('Y-m-d H:i:s');
                        echo "\ntable: ";
                        echo EbayUserInfoDAO::getInstance()->getTableName();
                        echo 'pk: ';
                        echo EbayUserInfoDAO::getInstance()->getPk();
                        echo 'UserID: ';
                        echo $columns['UserID'] . "\n";
                        echo '$_upk: ';
                        var_dump($_upk);
                        $text = ob_get_clean();
                        $subject = "[Error.data.duplication][buyer]Fatal error: ebay_user_info数据重复";
                        $to = Yii::app()->params['logmails'];
                        SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
                    }
                    
                    $columns = array(
                        'ebay_user_info_id' => $_upk,
                        'shop_id' => $dataArr['shop_id']
                    );
                    $conditions = 'ebay_user_info_id=:ebay_user_info_id and shop_id=:shop_id';
                    $params = array(
                        ':ebay_user_info_id' => $_upk,
                        ':shop_id' => $dataArr['shop_id']
                    );
                    EbayUserShopsDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                }
                
                // more
            }
        } else {
            // 写下日志
            iMongo::getInstance()->setCollection('msgListNoSuccess')->insert(array(
                'list' => $dataArr['text_json']['list'],
                'time' => time()
            ));
        }
        
        // details
        if (! isset($dataArr['text_json']['details'])) {
            $dataArr['text_json']['details'] = array();
        }
        foreach ($dataArr['text_json']['details'] as $detail) {
            try {
                $ddoc = phpQuery::newDocumentXML($detail);
                phpQuery::selectDocument($ddoc);
            } catch (Exception $e) {
                iMongo::getInstance()->setCollection('pqXmlErrMsgD')->insert(array(
                    'down_id' => $dataArr['down_id'],
                    'xml' => $detail,
                    'errInfo' => $e->getMessage(),
                    'time' => time()
                ));
                continue;
            }
            
            $Messages = pq('Messages>Message');
            $length = $Messages->length;
            for ($i = 0; $i < $length; $i ++) {
                $Message = $Messages->eq($i);
                
                $columns = array(
                    'Read' => boolConvert::toStr01($Message->find('Read')->html()),
                    'Replied' => boolConvert::toStr01($Message->find('Replied')->html()),
                    'update_time' => time()
                );
                $conditions = 'shop_id=:shop_id and MessageID=:MessageID';
                $params = array(
                    ':shop_id' => $dataArr['shop_id'],
                    ':MessageID' => $Message->find('MessageID')->html()
                );
                
                foreach ($columns as $_tempkey => $_tempvalue) {
                    if ($_tempvalue === false || $_tempvalue === null) {
                        unset($columns[$_tempkey]);
                    }
                }
                
                // 业务状态值处理
                if (isset($columns['Read']) && $columns['Read'] === boolConvert::toStr01(false)) {
                    $columns['handled'] = boolConvert::toStr01(false);
                }
                if (isset($columns['Replied']) && $columns['Replied'] === boolConvert::toStr01(true)) {
                    $columns['handled'] = boolConvert::toStr01(true);
                } else {
                    $columns['handled'] = boolConvert::toStr01(false);
                }
                
                $msgpk = MsgDAO::getInstance()->ireplaceinto($columns, $conditions, $params, true);
                
                if (is_array($msgpk)) {
                    // 异常发邮件
                    $subject = "严重错误：消息数据重复";
                    ob_start();
                    var_dump($msgpk);
                    var_dump($params);
                    var_dump($conditions);
                    var_dump($columns);
                    $text = ob_get_clean();
                    $to = Yii::app()->params['logmails'];
                    SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
                    
                    $msgpk = array_shift($msgpk);
                    $msgpk = array_shift($msgpk);
                }
                
                $msgkey = $Message->find('MessageID')->html();
                $_dir = gmdate('/Ym/', strtotime($Message->find('ReceiveDate')->html())) . $dataArr['text_json']['AccountID'];
                
                // 文本消息
                $columns = array(
                    'msg_id' => $msgpk,
                    'Content' => $Message->find('Content')->html()
                );
                $conditions = 'msg_id=:msg_id';
                $params = array(
                    ':msg_id' => $msgpk
                );
                // EbayMsgContentDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                FileLog::getInstance()->write(EnumOther::LOG_DIR_MSG_CONTENT . $_dir, $msgkey, $columns['Content']);
                
                // HTML消息
                $columns = array(
                    'msg_id' => $msgpk,
                    'Text' => html_entity_decode($Message->find('Text')->html())
                );
                $conditions = 'msg_id=:msg_id';
                $params = array(
                    ':msg_id' => $msgpk
                );
                // EbayMsgTextDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                FileLog::getInstance()->write(EnumOther::LOG_DIR_MSG_TEXT . $_dir, $msgkey, $columns['Text']);
                
                // if ($Message->find('Sender')->html() != 'eBay' && stripos($Message->find('Sender')->html(), '@ebay.com') === false) {
                // HTML解析出来的信息
                $columns['Text'] = tidyTool::cleanRepair($columns['Text']);
                
                $hdoc = phpQuery::newDocumentHTML($columns['Text']);
                phpQuery::selectDocument($hdoc);
                
                // clear
                pq('#TextCTA')->find('*')->removeAttr('style');
                
                // ItemId和OrderId提取
                $ItemId = $Message->find('ItemID')->html();
                if (empty($ItemId)) {
                    // "物品編號" "Item Id"
                    $ItemId = pq('#ItemDetails')->find('table table')
                        ->find('tr')
                        ->eq(1)
                        ->find('td')
                        ->eq(1)
                        ->html();
                }
                $OrderId = pq('#ItemDetails')->find('table table')
                    ->find('tr')
                    ->eq(2)
                    ->find('td')
                    ->eq(1)
                    ->html();
                preg_match('/^\d{11,13}-\d{11,13}|\d{11,13}-0|\d{11,13}$/', $OrderId, $matches);
                if (empty($matches)) {
                    $OrderId = null;
                }
                
                if (pq('#UserInputtedText')->length > 0) {
                    $effect_content = pq('#UserInputtedText')->html();
                    
                    iMongo::getInstance()->setCollection('parseMessagesNewF1')->insert(array(
                        'effect_content' => $effect_content,
                        'html' => html_entity_decode($Message->find('Text')
                            ->html()),
                        'time' => time()
                    ));
                } elseif (stripos(html_entity_decode($Message->find('Text')->html()), '                       ') === 0 && stripos(html_entity_decode($Message->find('Text')->html()), '-----------------------------------------------------------------') > 0 && stripos(html_entity_decode($Message->find('Text')->html()), '=================================================================')) {
                    // 如果特殊格式A 则取原文
                    $effect_content = $columns['Text'];
                    
                    $pattern = '/(.*)=================================================================(.*)=================================================================(.*)wrote:(.*)/';
                    preg_match($pattern, $effect_content, $matches);
                    
                    if (isset($matches[3])) {
                        $effect_content = $matches[3];
                    }
                    
                    $effect_content = tidyTool::cleanRepair($effect_content);
                    $effect_content = tidyTool::getBody($effect_content);
                    
                    if (empty($effect_content)) {
                        $effect_content = $columns['Text'];
                    }
                    
                    iMongo::getInstance()->setCollection('parseMessagesV1F1')->insert(array(
                        'effect_content' => $effect_content,
                        'matches' => $matches,
                        'html' => html_entity_decode($Message->find('Text')
                            ->html()),
                        'time' => time()
                    ));
                } elseif (pq('body>#TextCTA')->length == 0 && pq('body>#RawHtmlText')->length == 0 && $Message->find('Sender')->html() != 'eBay' && stripos($Message->find('Sender')->html(), '@ebay.com') === false) {
                    // 68069017059/fast 特殊格式处理
                    if (pq('#RawHtmlText>div')->eq(0)->find('hr')->length > 0) {
                        // (.*)(<hr[^<>]+[/]? >)
                        pq('#RawHtmlText>div')->eq(0)
                            ->find('#TextCTA')
                            ->hide();
                        pq('#RawHtmlText>div')->eq(0)
                            ->find('#RawHtmlText')
                            ->hide();
                        pq('#RawHtmlText>div')->eq(0)
                            ->find('#EmailDetails')
                            ->hide();
                        $effect_content = pq('#RawHtmlText>div')->eq(0)->html();
                        $effect_content = substr($effect_content, 0, stripos($effect_content, '<hr'));
                    } else {
                        pq('#RawHtmlText>div')->eq(0)
                            ->find('#TextCTA')
                            ->hide();
                        $effect_content = pq('#RawHtmlText>div')->eq(0)->html();
                    }
                    $effect_content = tidyTool::cleanRepair($effect_content);
                    $effect_content = tidyTool::getBody($effect_content);
                    
                    if (empty($effect_content)) {
                        $effect_content = $columns['Text'];
                    }
                    
                    iMongo::getInstance()->setCollection('parseMessagesV1F2')->insert(array(
                        'effect_content' => $effect_content,
                        'html' => html_entity_decode($Message->find('Text')
                            ->html()),
                        'time' => time()
                    ));
                } else {
                    $effect_content = pq('#TextCTA')->eq(0)
                        ->find('td')
                        ->eq(0)
                        ->html();
                }
                
                $effect_content = tidyTool::cleanRepair($effect_content);
                $effect_content = tidyTool::getBody($effect_content);
                
                $ImagePreview = pq('#ImagePreview')->eq(0)->find('#imagePreviewHtml');
                $ImagePreview->find('a')->removeAttr('href');
                $ImagePreview->find('a')->removeAttr('style');
                $ImagePreview->find('a')->removeAttr('id');
                $ImagePreview->find('table')->removeAttr('style');
                $ImagePreview->find('td')->removeAttr('style');
                $ImagePreview->find('td')->removeAttr('id');
                $ImagePreview->find('td')->removeAttr('align');
                $ImagePreview = $ImagePreview->html();
                
                $columns = array(
                    'msg_id' => $msgpk,
                    'effect_content' => $effect_content,
                    'ImagePreview' => $ImagePreview,
                    'TitleInfo' => pq('#Title')->find('table')
                        ->eq(0)
                        ->find('td>span')
                        ->eq(0)
                        ->html(),
                    'ItemId' => $ItemId,
                    'OrderId' => $OrderId,
                    'ItemUrl' => pq('#ItemDetails')->find('table td>a')
                        ->eq(0)
                        ->attr('href')
                );
                $conditions = 'msg_id=:msg_id';
                $params = array(
                    ':msg_id' => $msgpk
                );
                
                foreach ($columns as $_tempkey => $_tempvalue) {
                    if ($_tempvalue === false || $_tempvalue === null) {
                        unset($columns[$_tempkey]);
                    }
                }
                
                MsgTextResolveDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                
                // 获取消息对应的userid
                $user_id = $this->getMatchUserIdByOrderId($OrderId);
                if (empty($user_id)) {
                    $user_id = $this->getMatchUserIdByItemId($ItemId);
                }
                
                // 订单号 及其他提取信息写入msg表
                $subject = html_entity_decode($Message->find('Subject')->html());
                $subject_hash = md5(imsTool::subjectClear($subject));
                $columns = array(
                    'OrderID' => $OrderId,
                    'subject_hash' => $subject_hash,
                    'user_id' => $user_id
                );
                $conditions = 'msg_id=:msg_id';
                $params = array(
                    ':msg_id' => $msgpk
                );
                
                foreach ($columns as $_tempkey => $_tempvalue) {
                    if ($_tempvalue === false || $_tempvalue === null) {
                        unset($columns[$_tempkey]);
                    }
                }
                
                MsgDAO::getInstance()->iupdate($columns, $conditions, $params);
                
                if (! empty($ImagePreview)) {
                    $columns = array(
                        'shop_id',
                        'FolderID',
                        'Sender',
                        'SendToName',
                        'ItemID',
                        'subject_hash'
                    );
                    $msgInfo = MsgDAO::getInstance()->getValuesByPk($msgpk, $columns);
                    
                    if ($msgInfo['FolderID'] == 1) {
                        $UserID = $msgInfo['SendToName'];
                    } else {
                        $UserID = $msgInfo['Sender'];
                    }
                    
                    $columns = array(
                        'm.msg_id'
                    );
                    if ($msgInfo['Sender'] === $msgInfo['SendToName']) {
                        $conditions = '(Sender=:UserID or SendToName=:UserID) and Sender=SendToName';
                    } else {
                        $conditions = '(Sender=:UserID or SendToName=:UserID)';
                    }
                    $params = array(
                        ':UserID' => $UserID
                    );
                    if (! empty($resultAll['ItemID'])) {
                        $conditions .= ' and tr.ItemId=:ItemId';
                        $params[':ItemId'] = $msgInfo['ItemID'];
                    } else {
                        // 没有ItemId时,按subject_hash聚合
                        $conditions .= ' and m.subject_hash=:subject_hash';
                        $params[':subject_hash'] = $msgInfo['subject_hash'];
                    }
                    $conditions .= ' and m.shop_id=:shop_id';
                    $params[':shop_id'] = $msgInfo['shop_id'];
                    $order = '';
                    $joinArray = array(
                        array(
                            MsgTextResolveDAO::getInstance()->getTableName() . ' tr',
                            'm.msg_id=tr.msg_id'
                        ),
                        array(
                            ShopDAO::getInstance()->getTableName() . ' s',
                            's.shop_id=m.shop_id and s.is_delete=0'
                        )
                    );
                    $result = MsgDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 'm', $order);
                    
                    foreach ($result as $key => $value) {
                        $columns = array(
                            'is_img' => boolConvert::toStr01(true)
                        );
                        $conditions = 'msg_id=:msg_id';
                        $params = array(
                            ':msg_id' => $value['msg_id']
                        );
                        MsgDAO::getInstance()->iupdate($columns, $conditions, $params);
                    }
                }
                unset($ImagePreview);
                
                // more
                // }
            }
        }
        
        $conditions = 'down_id=:down_id';
        $params = array(
            ':down_id' => $dataArr['down_id']
        );
        MsgDownDAO::getInstance()->idelete($conditions, $params);
        
        // 此处的休眠时为了减小CPU消耗峰值
        usleep(100000);
        
        goto label1;
        
        label2:
        
        sleep(5);
        goto label1;
    }
    
    /**
     * @desc 根据Item获取对应的userid
     * @param string $ItemId
     * @author YangLong
     * @date 2015-11-19
     * @return int user_id
     */
    public function getMatchUserIdByItemId($ItemId)
    {
        if (empty($ItemId)) {
            return false;
        }
        
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        
        $columns = array(
            'l.listing_id',
            'l.sku'
        );
        $conditions = 'l.item_id=:item_id and s.seller_id=:seller_id';
        $params = array(
            ':item_id' => $ItemId,
            ':seller_id' => $sellerId
        );
        $joinArray = array(
            array(
                ShopDAO::getInstance()->getTableName() . ' s',
                's.shop_id=l.shop_id'
            )
        );
        $listing = EbayListingDAO::getInstance()->iselect($columns, $conditions, $params, false, $joinArray, 'l');
        
        if (empty($listing['sku'])) {
            return false;
        }
        
        $_sku = $listing['sku'];
        
        if (empty($_sku)) {
            $columns = array(
                'sku'
            );
            $conditions = 'listing_id=:listing_id';
            $params = array(
                ':listing_id' => $listing['listing_id']
            );
            $skus = EbayListingSkuDAO::getInstance()->iselect($columns, $conditions, $params);
            
            // TODO
            if (! empty($skus)) {
                $_sku = $skus[0]['sku'];
            }
        }
        
        $columns = array(
            'user_id'
        );
        $conditions = 'SKU=:SKU';
        $params = array(
            ':SKU' => $_sku
        );
        $userid = UserSkuDAO::getInstance()->iselect($columns, $conditions, $params, 'queryScalar');
        
        if (empty($userid)) {
            return false;
        } else {
            return $userid;
        }
    }
    
    /**
     * @desc 根据订单号获取对应的userid
     * @param string $OrderId
     * @author YangLong
     * @date 2015-11-19
     * @return int user_id
     */
    public function getMatchUserIdByOrderId($OrderId)
    {
        if (empty($OrderId)) {
            return false;
        }
        
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        
        $columns = array(
            't.CustomLabel',
            't.Item_SKU',
            't.Variation_SKU'
        );
        $conditions = 'o.OrderID=:OrderID and s.seller_id=:seller_id';
        $params = array(
            ':OrderID' => $OrderId,
            ':seller_id' => $sellerId
        );
        $joinArray = array(
            array(
                EbayOrderTransactionDAO::getInstance()->getTableName() . ' t',
                't.ebay_orders_id=o.ebay_orders_id'
            ),
            array(
                ShopDAO::getInstance()->getTableName() . ' s',
                's.shop_id=o.shop_id'
            )
        );
        $sku = EbayOrdersDAO::getInstance()->iselect($columns, $conditions, $params, false, $joinArray, 'o');
        
        if (empty($sku)) {
            return false;
        }
        
        $_sku = $sku['Variation_SKU'];
        if (empty($_sku)) {
            $_sku = $sku['Item_SKU'];
        }
        if (empty($_sku)) {
            $_sku = $sku['CustomLabel'];
        }
        
        $columns = array(
            'user_id'
        );
        $conditions = 'SKU=:SKU';
        $params = array(
            ':SKU' => $_sku
        );
        $userid = UserSkuDAO::getInstance()->iselect($columns, $conditions, $params, 'queryScalar');
        
        if (empty($userid)) {
            return false;
        } else {
            return $userid;
        }
    }
    
    /**
     * @desc 运行图片上传队列
     * @author YangLong
     * @date 2015-09-20
     * @return null
     */
    public function executeUploadImageQueue()
    {
        DaemonLockTool::lock(__METHOD__);

        $startTime = time();

        label1:

        $key = 'uploadImage';
        $result = iMemQueue::getInstance()->pop($key);

        if ($result === false || ! isset($result['fileUrl']) || ! isset($result['refid']) || ! isset($result['src']) || ! isset($result['fileName'])) {
            if (time() - $startTime > 295) {
                die(0);
            }
            usleep(200000);
            goto label1;
        }

        if ($result['src'] == 'msgid') {
            $columns = array(
                's.token'
            );
            $conditions = 'm.msg_id=:msg_id';
            $params = array(
                ':msg_id' => $result['refid']
            );
            $joinArray = array(
                array(
                    MsgDAO::getInstance()->getTableName() . ' m',
                    'm.shop_id=s.shop_id and s.is_delete=0'
                )
            );
            $result['token'] = ShopDAO::getInstance()->iselect($columns, $conditions, $params, 'queryScalar', $joinArray, 's');
        } elseif ($result['src'] == 'fbid') {
            // TODO
        } else {
            goto label1;
        }

        $result['fileUrl'] = BASE_PATH . '/' . $result['fileUrl'];
        $multiPartImageData = is_file($result['fileUrl']) ? file_get_contents($result['fileUrl']) : null;
        if (empty($multiPartImageData)) {
            goto label1;
        }
        $PictureName = $result['fileName'];

        $xml = MsgDownModel::model()->uploadSiteHostedPictures($result['token'], $multiPartImageData, $PictureName);

        $doc = phpQuery::newDocumentXML($xml);
        phpQuery::selectDocument($doc);

        if (pq('Ack')->html() != 'Failure') {
            $picNameOut = pq('SiteHostedPictureDetails>PictureName')->html();
            empty($picNameOut) ? $picNameOut = '' : null;
            $MessageMedia = array(
                'MediaName' => $picNameOut,
                'MediaURL' => pq('SiteHostedPictureDetails>FullURL')->html()
            );
            iMemcache::getInstance()->set(md5("{$result['fileUrl']}_ebayimg"), $MessageMedia, 7200);

            $_imd5 = md5($multiPartImageData);
            FileLog::getInstance()->write('imageuploadcache', $_imd5, serialize($MessageMedia));
        }
        unset($doc);

        goto label1;
    }
}
