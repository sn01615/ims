<?php

/**
 * @desc IMS 相关相关下载和上传任务
 * @author YangLong
 * @date 2015-04-27
 */
class ImsjobsModel extends BaseModel
{
    
    // 默认优先级
    const P_DEFAULTPRIORITY = 100;
    
    // 新人优先级
    const P_NEWPRIORITY = 200;
    
    // 历史基准优先级
    const P_HISTORYPRIORITY = 52;
    
    // 新人下载尺寸
    const D_FIRSTDOWNLOADSIZE = 30;
    
    // 下载尺寸
    const D_HISDOWNLOADSIZE = 30;
    
    // 内存极限
    const D_MEMORYLIMIT = 32;
    
    // 运行下载队列
    const DOWNQUEUEDATALIMIT = 100;

    private $proxy;

    private $compatabilityLevel;
    // eBay API version
    private $devID;

    private $appID;

    private $certID;

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
     * @desc 覆盖父方法返回ImsjobsModel对象
     * @author YangLong
     * @date 2014-10-22
     * @return ImsjobsModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
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
        return gmdate('Y-m-d\TH:i:s\Z', $date);
    }

    /**
     * @desc 设置RequestToken和DevId等
     * @param string $requestToken
     * @param number $site_id
     * @author YangLong,hangguangquan
     * @date 2015-02-12
     */
    private function setRequestToken($requestToken, $site_id = 0)
    {
        // 参数里的默认值为测试数据
        $objSession = new EbatNs_Session();
        $objSession->setSiteId($site_id);
        $objSession->setUseHttpCompression(1);
        $objSession->setAppMode(0);
        $objSession->setDevId($this->devID);
        $objSession->setAppId($this->appID);
        $objSession->setCertId($this->certID);
        $objSession->setRequestToken($requestToken);
        $objSession->setTokenUsePickupFile(false);
        $objSession->setTokenMode(true);
        
        $this->proxy = new EbatNs_ServiceProxy($objSession, 'EbatNs_DataConverterUtf8');
    }

    /**
     * @desc 执行回复信息队列
     * @author heguangquan,lvjianfei,YangLong
     * @date 2015-03-05
     * @return bool 执行结果
     */
    public function executeReplyQueue()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $startTime = time();
        
        label1:
        
        if (time() - $startTime > 600) {
            return false;
        }
        
        $loopCount = 0;
        
        while (true) {
            
            $key = 'msg_reply_queue_mem';
            $msgReplyQueue = iMemQueue::getInstance()->pop($key);
            if ($msgReplyQueue === false) {
                if ($loopCount % 10 == 0) {
                    $Queues = EbayMsgReplyQueueDAO::getInstance()->getReplyQueueData(1);
                } else {
                    $Queues = false;
                }
            } else {
                $Queues = array();
                $Queues[] = $msgReplyQueue;
            }
            
            $loopCount ++;
            
            if ($Queues !== false) {
                $pkArr = array();
                foreach ($Queues as $task) {
                    
                    // lock check
                    if (iMemcache::getInstance()->get(md5("{$key}_{$task['down_queue_id']}_lock")) !== false) {
                        continue;
                    }
                    iMemcache::getInstance()->set(md5("{$key}_{$task['down_queue_id']}_lock"), true, 30);
                    
                    $pkArr[] = $task['down_queue_id'];
                    $picPathArr = array();
                    if (! empty($task['imgUrl']) && ! empty($task['token'])) {
                        $pathUrl = json_decode($task['imgUrl']);
                        if (is_array($pathUrl)) {
                            foreach ($pathUrl as $v) {
                                $_imgfile = BASE_PATH . '/' . $v;
                                $multiPartImageData = is_file($_imgfile) ? file_get_contents($_imgfile) : null;
                                if (! empty($multiPartImageData)) {
                                    $_imgcache = iMemcache::getInstance()->get(md5("{$_imgfile}_ebayimg"));
                                    if ($_imgcache !== false) {
                                        $picPathArr[] = $_imgcache['MediaURL'];
                                        continue;
                                    } else {
                                        $_imgcache = FileLog::getInstance()->read('imageuploadcache', md5($multiPartImageData));
                                        if ($_imgcache !== false) {
                                            try {
                                                $_imgcache = unserialize($_imgcache);
                                            } catch (Exception $e) {
                                                // 发送邮件通知
                                                ob_start();
                                                echo md5($multiPartImageData);
                                                $text = ob_get_clean();
                                                $subject = "imageuploadcache unserialize failure";
                                                $to = Yii::app()->params['logmails'];
                                                SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
                                            }
                                            if ($_imgcache !== false) {
                                                $picPathArr[] = $_imgcache['MediaURL'];
                                                continue;
                                            }
                                        }
                                    }
                                }
                                
                                $imsApiUrl = Yii::app()->params['imsApiUrl'] . $v;
                                $result = $this->UploadPicture($task['token'], $imsApiUrl, 0);
                                if ($result->Ack == 'Success' || $result->Ack == 'Warning') {
                                    $picPathArr[] = $result->SiteHostedPictureDetails->FullURL;
                                } else {
                                    iMongo::getInstance()->setCollection('UploadPictureErr')->insert(
                                        array(
                                            'imsApiUrl' => $imsApiUrl,
                                            'ErrorCode' => $result->Errors->ErrorCode,
                                            'LongMessage' => $result->Errors->LongMessage,
                                            'ShortMessage' => $result->Errors->ShortMessage,
                                            'time' => time()
                                        ));
                                }
                            }
                        }
                    }
                    
                    $task['content'] = imsTool::removeNonPrintable($task['content'], __METHOD__);
                    
                    $apiResult = $this->replyMessage($task['content'], $task['ExternalMessageID'], $task['Sender'], $task['token'], 
                        $task['copy'], $picPathArr);
                    
                    if ($apiResult['state'] === 0) {
                        iMemcache::getInstance()->set(md5("msg_reply_status_{$task['down_queue_id']}"), 'Failure', 3600);
                        iMemcache::getInstance()->set(md5("msg_reply_status_{$task['down_queue_id']}_error"), $apiResult, 3600);
                        
                        $msgid = $task['msg_id'];
                        $Replied = EnumMsgStatus::REPLIED_NOT;
                        $handled = EnumMsgStatus::HANDLED_NOT;
                        $send_status = EnumMsgStatus::SEND_STATUS_FAILURE;
                        
                        $columns = array(
                            'Replied' => $Replied,
                            'handled' => $handled,
                            'send_status' => $send_status
                        );
                        $conditions = 'msg_id=:msg_id';
                        $params = array(
                            ':msg_id' => $msgid
                        );
                        MsgDAO::getInstance()->iupdate($columns, $conditions, $params);
                        
                        // 发送邮件通知
                        ob_start();
                        echo "时间：\n";
                        echo date('Y-m-d H:i:s');
                        echo "\n";
                        echo time();
                        echo "\n";
                        echo "apiResult：\n";
                        var_export($apiResult);
                        echo "\n\n回复内容：\n";
                        var_export($task);
                        echo "\n\n图片信息：\n";
                        var_export($picPathArr);
                        $text = ob_get_clean();
                        $subject = "消息回复失败通知 [Failure]\n";
                        $to = Yii::app()->params['logmails'];
                        SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
                    } else {
                        iMemcache::getInstance()->set(md5("msg_reply_status_{$task['down_queue_id']}"), 'Success', 3600);
                    }
                }
                // 删除任务,任务只执行一次
                EbayMsgReplyQueueDAO::getInstance()->deleteByPk($pkArr);
            } else {
                break;
            }
            unset($Queues);
        }
        
        usleep(200000);
        goto label1;
    }

    /**
     * @desc 调用回复邮件
     * @param text $body 邮件正文
     * @param string $parentMessageID 邮件扩展ID(ExternalMessageID)
     * @param string $addRecipientID 收件人账号(Sender)
     * @param string $MessageID 邮件ID(MessageID)
     * @param string $token ebay token
     * @param int $siteId 站点ID
     * @author YangLong,liaojianwen
     * @date 2015-01-30
     * @return mixed 结果状态
     * @deprecated XXX
     */
    public function replyMessage($body, $parentMessageID, $addRecipientID, $token, $isSendEmail, $picPath, $siteId = 0)
    {
        $this->setRequestToken($token, $siteId);
        $addmembermessagertqrequest = new AddMemberMessageRTQRequestType();
        $membermessage = new MemberMessageType();
        $addmembermessagertqrequest->setMemberMessage($membermessage);
        $membermessage->setBody($body);
        $membermessage->setEmailCopyToSender($isSendEmail);
        if (! empty($picPath)) {
            foreach ($picPath as $photo) {
                $messagemedia = new MessageMediaType();
                $membermessage->addMessageMedia($messagemedia);
                $messagemedia->setMediaName(rand(11111, 99999));
                $messagemedia->setMediaURL($photo);
            }
        }
        $membermessage->setParentMessageID($parentMessageID);
        $membermessage->addRecipientID($addRecipientID);
        $addmembermessagertqrequest->setVersion($this->compatabilityLevel);
        $addmembermessagertqrequest->setErrorLanguage('zh_CN');
        
        $response = $this->proxy->AddMemberMessageRTQ($addmembermessagertqrequest);
        
        if ($response->Ack === 'Success') {
            $result = array(
                'state' => 1
            );
        } else {
            $error = $response->Errors;
            $result = array(
                'state' => 0,
                'body' => $error[0]->ShortMessage,
                'error' => $error
            );
        }
        return $result;
    }

    /**
     * @desc 获取信息解析的数据
     * @param int $taskNumber 任务数量
     * @author heguangquan,YangLong
     * @date 2015-01-29
     * @return array Ack：获取数据状态;body：内容
     */
    public function getMsgDownsData($taskNumber)
    {
        if (empty($taskNumber)) {
            return array(
                'Ack' => 'Failure',
                'body' => ''
            );
        }
        $objDowDAO = MsgDownDAO::getInstance();
        // $paramArr['status'] = EnumOther::MSG_DOWN_NOTSTATUS;
        $paramArr['status'] = 0; // TODO 枚举化
        $paramArr['version'] = 0;
        $criteria = array(
            'down_id',
            'seller_id',
            'shop_id',
            'text_json'
        );
        
        $msgParseResult = $objDowDAO->findAllByAttributes($paramArr, $criteria, '', $taskNumber['limit']);
        foreach ($msgParseResult as $msg) {
            $objDowDAO->update(array(
                'down_id' => $msg['down_id']
            ), 
                array(
                    'status' => 1, // TODO 枚举化 // 'status' => EnumOther::MSG_DOWN_DEALSTATUS,
                    'lastruntime' => time()
                ));
            $objDowDAO->increase('runcount', 'down_id=' . $msg['down_id']);
        }
        $resultArr = array(
            'Ack' => 'Success',
            'body' => $msgParseResult
        );
        return $resultArr;
    }

    /**
     * @desc 删除已解释的信息数据
     * @param string $strId 删除的信息ID
     * @author heguangquan,YangLong
     * @date 2015-02-02
     * @return array Ack:删除信息状态
     */
    public function delMsgDownsData($strId)
    {
        if (empty($strId)) {
            return array(
                'Ack' => 'Failure',
                'body' => ''
            );
        }
        
        $downId = explode(',', $strId);
        $rowNumber = MsgDownDAO::getInstance()->deleteByPk($downId);
        return empty($rowNumber) ? array(
            'Ack' => 'Failure',
            'body' => ''
        ) : array(
            'Ack' => 'Success',
            'body' => ''
        );
    }

    /**
     * @desc 生成队列
     * @param string $msgDownQueueJson 数据
     * @param string $authKey 密钥
     * @author zhanwei,YangLong
     * @date 2015-04-27
     * @return 返回信息
     */
    public function generateDownQueue($msgDownQueueJson, $authKey)
    {
        if ($authKey != md5(Yii::app()->params['imsTokenTool_mkey'] . $msgDownQueueJson) || empty($msgDownQueueJson)) {
            return array(
                'Ack' => 'Failure'
            );
        }
        
        $msgDownQueueArr = json_decode(base64_decode($msgDownQueueJson), true);
        $result = EbayMsgDownQueueDAO::getInstance()->iMultiInsert($msgDownQueueArr);
        if ($result) {
            return array(
                'Ack' => 'Success'
            );
        } else {
            return array(
                'Ack' => 'Failure'
            );
        }
    }

    /**
     * @desc 接收IMS推送的回复队列数据，并入库
     * @param array $replyInfo 队列数据
     * @author heguanguan,lvjianfei,YangLong
     * @date 2015-04-29
     * @return string
     */
    public function generateReplyMsgQueue($replyInfo)
    {
        if (empty($replyInfo)) {
            return array(
                'Ack' => 'Failure'
            );
        }
        
        $restlt = EbayMsgReplyQueueDAO::getInstance()->iinsert($replyInfo, true);
        
        if ($restlt !== false) {
            $resultArr['Ack'] = 'Success';
            $resultArr['Pk'] = $restlt;
        } else {
            $resultArr['Ack'] = 'Failure';
        }
        
        return $resultArr;
    }

    /**
     * @desc 上传图片API
     * @param string token
     * @param string $photoPath 本地图片路径
     * @param int $siteId 站点
     * @author liaojianwen,YangLong
     * @date 2015-04-30
     * @return string $respXmlObj 路径信息
     */
    public function UploadPicture($token = '', $photoPath = '', $siteId = -1)
    {
        $verb = 'UploadSiteHostedPictures';
        
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        
        $version = $this->compatabilityLevel;
        $picNameIn = 'img_' . time() . mt_rand(0, 9999);
        
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, getByCurl::get($photoPath));
        rewind($handle);
        $multiPartImageData = stream_get_contents($handle);
        fclose($handle);
        
        // Build the request XML request which is first part of multi-part POST
        $xmlReq = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $xmlReq .= '<' . $verb . 'Request xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $xmlReq .= "<Version>$version</Version>\n";
        $xmlReq .= "<PictureName>$picNameIn</PictureName>\n";
        $xmlReq .= "<RequesterCredentials><eBayAuthToken>$token</eBayAuthToken></RequesterCredentials>\n";
        $xmlReq .= '</' . $verb . 'Request>';
        
        $boundary = "MIME_boundary";
        $CRLF = "\r\n";
        
        // The complete POST consists of an XML request plus the binary image separated by boundaries
        $firstPart = '';
        $firstPart .= "--" . $boundary . $CRLF;
        $firstPart .= 'Content-Disposition: form-data; name="XML Payload"' . $CRLF;
        $firstPart .= 'Content-Type: text/xml;charset=utf-8' . $CRLF . $CRLF;
        $firstPart .= $xmlReq;
        $firstPart .= $CRLF;
        
        $secondPart = '';
        $secondPart .= "--" . $boundary . $CRLF;
        $secondPart .= 'Content-Disposition: form-data; name="dummy"; filename="dummy"' . $CRLF;
        $secondPart .= "Content-Transfer-Encoding: binary" . $CRLF;
        $secondPart .= "Content-Type: application/octet-stream" . $CRLF . $CRLF;
        $secondPart .= $multiPartImageData;
        $secondPart .= $CRLF;
        $secondPart .= "--" . $boundary . "--" . $CRLF;
        $fullPost = $firstPart . $secondPart;
        
        $session = new eBaySession($this->serverUrl);
        $session->headers[] = "Content-Type: multipart/form-data; boundary={$boundary}";
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:{$version}";
        $session->headers[] = "X-EBAY-API-DEV-NAME:" . $this->devID;
        $session->headers[] = "X-EBAY-API-APP-NAME:" . $this->appID;
        $session->headers[] = "X-EBAY-API-CERT-NAME:" . $this->certID;
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$verb}";
        $session->headers[] = "X-EBAY-API-SITEID:{$siteId}";
        
        $respXmlStr = $session->sendHttpRequest($fullPost); // send multi-part request and get string XML response
        if (stristr($respXmlStr, 'HTTP 404') || $respXmlStr == '') {
            die('<p>Error sending request');
        }
        $respXmlObj = simplexml_load_string($respXmlStr);
        return $respXmlObj;
    }

    /**
     * @desc 联系订单客户（只能联系订单过后90天内）
     * @param [stirng] $token
     * @param [string] $itemid
     * @param [string] $body          消息内容
     * @param [boolen] $isSendEmail   是否发送到自己的邮箱
     * @param [array] $picPath       图片路径
     * @param [string] $receiveUserID 客户ID
     * @param [int] $siteId        站点ID
     * @author  liaojianwen
     * @date  2015-08-27
     */
    public function addMessagesToPartner($token, $itemId, $body, $isSendEmail = '', $picPath, $receiveUserID, $siteId)
    {
        $callName = 'AddMemberMessageAAQToPartner';
        
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        $version = $this->compatabilityLevel;
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>
        <AddMemberMessageAAQToPartnerRequest xmlns="urn:ebay:apis:eBLBaseComponents">
        <RequesterCredentials>
        <eBayAuthToken>' . $token . '</eBayAuthToken>
        </RequesterCredentials>
        <ItemID ComplexType="ItemIDType">' . $itemId . '</ItemID>
        <MemberMessage ComplexType="MemberMessageType">
        <QuestionType>General</QuestionType>';
        if (! empty($isSendEmail)) {
            $requestXmlBody .= '<EmailCopyToSender>' . $isSendEmail . '</EmailCopyToSender>';
        }
        $requestXmlBody .= '<RecipientID>' . $receiveUserID . '</RecipientID>
        <Subject>Thank You for your purchase</Subject>
        <body>' . htmlspecialchars($body, ENT_XML1) . '</body>';
        if (! empty($picPath)) {
            foreach ($picPath as $photo) {
                $requestXmlBody .= '<MessageMedia>
                    <MediaURL>' . $photo . '</MediaURL>
                    <MediaName>' . rand(11111, 99999) . '</MediaName>
                </MessageMedia>';
            }
        }
        $requestXmlBody .= '</MemberMessage>
        </AddMemberMessageAAQToPartnerRequest>​';
        $session = new eBaySession($this->serverUrl);
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:{$version}";
        $session->headers[] = "X-EBAY-API-DEV-NAME:" . $this->devID;
        $session->headers[] = "X-EBAY-API-APP-NAME:" . $this->appID;
        $session->headers[] = "X-EBAY-API-CERT-NAME:" . $this->certID;
        $session->headers[] = "X-EBAY-API-SITEID:{$siteId}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        return $responseXml;
    }
}