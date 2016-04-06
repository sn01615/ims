<?php

/**
 * @desc 信息定时任务处理类
 * @author heguangquan
 * @date 2015-01-28
 */
class MsgDealModel extends BaseModel
{

    const VESRION = '905';

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
     * @desc 覆盖父方法返回MsgDealModel对象
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date  2015-01-28
     * @return MsgDealModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @desc 处理msg表删除、还原功能、标记已读、未读、标星、取消标星、待办、取消待办
     * @param array $array 修改的MSG数据
     * @return Ambigous <number, string>
     * @author liaojianwen
     * @date  2015-01-28
     * @deprecated
     */
    public function dealMsg($array)
    {
        $type = $array['type'];
        switch ($type) {
            case EnumOther::MODIFY_DEL:
                $value = EnumMsgStatus::MSG_DELETE_YES;
                $aram = 'is_delete';
                break;
            case EnumOther::MODIFY_HIDDEN:
                $value = EnumMsgStatus::MSG_DELETE_SUCCE;
                $aram = 'is_delete';
                break;
            case EnumOther::MODIFY_REVERT:
                $value = EnumMsgStatus::MSG_DELETE_NOT;
                $aram = 'is_delete';
                break;
            case EnumOther::MODIFY_READ_NOT:
                $value = EnumMsgStatus::READ_NOT;
                $aram = 'Read';
                break;
            case EnumOther::MODIFY_READ_SUCCE:
                $value = EnumMsgStatus::READ_SUCCE;
                $aram = 'Read';
                if (isset($array['verfiy']) && $array['verfiy'] !== 'list') {
                    $apiResult = $this->setMessagesStatus($array['ids'], 'true', 1);
                    if ($apiResult['Ack'] !== 'Success') {
                        return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'fail to change eBay data');
                    }
                }
                break;
            case EnumOther::MODIFY_STAR_NOT:
                $value = EnumMsgStatus::STAR_NOT;
                $aram = 'is_star';
                break;
            case EnumOther::MODIFY_STAR_SUCCE:
                $value = EnumMsgStatus::STAR_SUCCE;
                $aram = 'is_star';
                break;
            case EnumOther::MODIFY_DISPOSE_SUCCE:
                $value = EnumMsgStatus::HANDLED_NOT;
                $aram = 'handled';
                break;
            case EnumOther::MODIFY_DISPOSE_NOT:
                $value = EnumMsgStatus::HANDLED_YES;
                $aram = 'handled';
                break;
            case EnumOther::MODIFY_REPLIED:
                $value = EnumMsgStatus::REPLIED;
                $aram = 'Replied';
                break;
        }
        $express = "`{$aram}`= '{$value}'";
        if ($aram == "Read") {
            $express = "`{$aram}`= '{$value}', send_status = '" . EnumMsgStatus::SEND_STATUS_NORMAL . "'";
        }
        $res = MsgDealDAO::getInstance()->dealMsg($array['ids'], $express);
        if ($res == true) {
            $result = $this->handleApiFormat(EnumOther::ACK_SUCCESS, '', '');
        } else {
            $result = $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'fail to change data');
        }
        return $result;
    }

    /**
     * @desc 批量回复邮件
     * @param text $body 邮件正文
     * @param string $mids 需要回复的邮件的ID列表
     * @author YangLong,liaojianwen
     * @return 结果状态
     * @date 2015-01-30
     */
    public function replyMessages($body, $mids)
    {
        $result = array();
        $data = MsgDealDAO::getInstance()->getReplyRInfo($mids);
        foreach ($data as $key => $value) {
            if ($value['Sender'] == 'eBay') {
                $result = array(
                    'body' => '系统邮件不能回复',
                    'state' => 0
                );
            } else {
                $parentMessageID = $value['ExternalMessageID'];
                $addRecipientID = $value['Sender'];
                $token = $value['token'];
                $siteId = $value['siteId'];
                $result = $this->replyMessage($body, $parentMessageID, $addRecipientID, $token, $siteId);
                $_arr['ids'] = $mids;
                $_arr['type'] = 'replied';
                $this->dealMsg($_arr);
            }
        }
        return $result;
    }

    /**
     * @desc 调用回复邮件
     * @param text $body 邮件正文
     * @param string $parentMessageID 邮件扩展ID(ExternalMessageID)
     * @param string $addRecipientID 收件人账号(Sender)
     * @param string $MessageID 邮件ID(MessageID)
     * @param string $token ebay token
     * @param int $siteId 站点ID
     * @return 结果状态
     * @author YangLong
     * @date 2015-01-30
     */
    public function replyMessage($body, $parentMessageID, $addRecipientID, $token, $siteId = -1)
    {
        $this->setRequestToken($token, $siteId);
        $addmembermessagertqrequest = new AddMemberMessageRTQRequestType();
        $membermessage = new MemberMessageType();
        $addmembermessagertqrequest->setMemberMessage($membermessage);
        $membermessage->setBody($body);
        $membermessage->setParentMessageID($parentMessageID);
        $membermessage->addRecipientID($addRecipientID);
        $addmembermessagertqrequest->setVersion(self::VESRION);
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
                'body' => $error[0]->ShortMessage
            );
        }
        return $result;
    }

    /**
     * @desc 上传图片API
     * @param  string token 
     * @param string $photoPath 本地图片路径
     * @param int $siteId 站点 
     * @author liaojianwen 
     * @date 2015-04-30
     * @return string $respXmlObj 路径信息
     */
    public function UploadPicture($token = '', $photoPath = '', $siteId = -1)
    {
        // $token = 'AgAAAA**AQAAAA**aAAAAA**f1WrVA**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wFk4GhDZGGowudj6x9nY+seQ**pjEDAA**AAMAAA**DHfOSsA9iF5yhruTjCK1CuYORC1FGEzxs1SPcy0fZuvwDof+Ckm7Xgqdu4jcDNcS0rXKCvwpVaOUlf4XPAHHUGj+aMV3LZlpYYzneCNs6yOPLDbapjdIel16N4YzE5iOc88OkdKU+j42ckQfn1Um6MDiTnFkOYFIlthLcCLsydsrfwvIE89ts/BCMTTt7DLVTHErkBwWQsH8dlxHf1Q7zYm88Yneh/c88cXpoXWGtir/boHEKzJqUrh6CuNy7T6tG+gbeCfIdX5Kw/TcXcMzOG6LtRqgwYGXU5Wm2jF0CaMAkSIRXOy7yvl08FeSYs8stthzXzU/bMujqrFpmFUyAm3+qJIccRip0O4V8QyLjtIc9PxFmygfU7upra9gDl3QcX7kGio+k5yHY6t14vh+Iv0vnzcCJ4NFJG5us/eeqSI+27R89xiigx7t2H5KYc1Yy1u1Hj+O7oZbbHMXUs/7MXmk+UMs3LmDxrtT0AYx2wfScs9JbIH5SQuRbdcx68zUXxg/SKATHB/BQvAoggvdDZQVVUIkv6Ya1PuAaV5axAf9+FcG67AwxTgJwOEiH7X9zGRC6aJnxVGZyKxMrqhUuoxDVY6/7GZEy9oGh290d5+pe7p6S+3Z0Wmv+vwKw/GiDA1d48l9tlfqA4thjjq5gL2T2i2cFplhLi1VxvOI2LCUADkPgbpejURROV6Sn0jnKkJZwCI3KdKTRFKgaeappteesRldHBF1JVhCSaZQVLVEMP4yNLkt9Nzo1EEnGTIW';
        // $photoPath = 'C:\Users\Administrator\Desktop\hhh.jpg';
        $verb = 'UploadSiteHostedPictures'; // the call being made:
        if (Yii::app()->params['ebay_api_production']) {
            $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        } else {
            $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        }
        $version = self::VESRION; // eBay API version
        $file = $photoPath; // 'hhh.jpg'; // image file to read and upload
        $picNameIn = 'img_' . time() . mt_rand(0, 9999);
        $handle = fopen($file, 'r'); // do a binary read of image
        $multiPartImageData = fread($handle, filesize($file));
        fclose($handle);
        
        // /Build the request XML request which is first part of multi-part POST
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
     * @desc 设置RequestToken和DevId等
     * @param string $RequestToken            
     * @param string $DevId            
     * @param string $AppId            
     * @param string $CertId            
     * @author heguangquan,YangLong
     */
    private function setRequestToken($requestToken = '', $siteId = -1)
    {
        // 参数里的默认值为测试数据
        $objSession = new EbatNs_Session();
        $objSession->setSiteId($siteId);
        $objSession->setUseHttpCompression(1);
        $objSession->setAppMode(0);
        $objSession->setDevId($this->devID);
        $objSession->setAppId($this->appID);
        $objSession->setCertId($this->certID);
        if (! empty($requestToken)) {
            $objSession->setRequestToken($requestToken);
        }
        $objSession->setTokenUsePickupFile(false);
        $objSession->setTokenMode(true);
        
        $this->proxy = new EbatNs_ServiceProxy($objSession, 'EbatNs_DataConverterUtf8');
    }

    /**
     * @desc 获取对应标签的消息
     * @param string $listType
     * @param unknown $classID
     * @param int $page
     * @param int $pageSize
     * @param string $searchCon
     * @param int $labelid
     * @param int $tagid
     * @param int $aggregate
     * @param int $questionType
     * @param int $messageType
     * @author liaojianwen
     * @modify 2015-07-01 YangLong
     * @date 2015-1-30          
     * @return mixed
     * @deprecated 需要重构 维护性和灵活性太差
     */
    public function getMessageList($listType, $classID, $page, $pageSize, $searchCon, $labelid, $tagid, $aggregate, $questionType, $messageType)
    {
        switch ($listType) {
            case EnumOther::IMS_PENDING:
                $express = "m.handled=:param and m.is_delete='" . EnumMsgStatus::MSG_DELETE_NOT;
                $express .= "' and m.Sender!='eBay' and m.Replied='" . EnumMsgStatus::REPLIED_NOT . "' and m.FolderID!=1";
                $param = EnumMsgStatus::HANDLED_NOT;
                break;
            case EnumOther::IMS_STAR:
                $express = "m.is_star = :param and m.is_delete = '" . EnumMsgStatus::MSG_DELETE_NOT . "'";
                $param = EnumMsgStatus::STAR_SUCCE;
                break;
            case EnumOther::IMS_SYS:
                $express = "m.sender = :param and m.is_delete = '" . EnumMsgStatus::MSG_DELETE_NOT . "'";
                $param = 'eBay';
                break;
            case EnumOther::IMS_SENT:
                $express = "m.FolderID = :param and m.is_delete = '" . EnumMsgStatus::MSG_DELETE_NOT . "'";
                $param = 1;
                break;
            case EnumOther::IMS_SALEBEFORE:
                $express = "m.sender <> :param and m.FolderID != 1 and m.is_delete = '" . EnumMsgStatus::MSG_DELETE_NOT . "' and m.OrderID = ''";
                $param = 'eBay';
                break;
            case EnumOther::IMS_SALEAFTER:
                $express = "m.sender<> :param and m.FolderID != 1 and m.is_delete = '" . EnumMsgStatus::MSG_DELETE_NOT . "' and m.OrderID <> ''";
                $param = "eBay";
                break;
            case EnumOther::IMS_DELETE:
                $express = "m.is_delete = :param";
                $param = EnumMsgStatus::MSG_DELETE_YES;
                break;
            case EnumOther::IMS_MEMBER:
                $express = "m.Sender <> :param and m.FolderID != 1 and m.is_delete = '" . EnumMsgStatus::MSG_DELETE_NOT . "'";
                $param = 'eBay';
                break;
            case EnumOther::IMS_LABEL:
                $express = "mlr.msg_label_id=:param and m.is_delete = '" . EnumMsgStatus::MSG_DELETE_NOT . "'";
                $param = $labelid;
                break;
            case EnumOther::IMS_AUTOTAG:
                $express = "malr.msg_auto_label_id=:param and m.is_delete = '" . EnumMsgStatus::MSG_DELETE_NOT . "'";
                $param = $tagid;
                break;
        }
        
        if (! empty($questionType)) {
            $express .= " and QuestionType='{$questionType}'";
        }
        if (! empty($messageType)) {
            $express .= " and MessageType='{$messageType}'";
        }
        
        $userConfig = UserModel::model()->getUserConfigs();
        $shopIds = implode(',', $userConfig['shops']);
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            if (empty($shopIds)) {
                $shopIds = 0;
            }
            $express .= " and s.shop_id in ({$shopIds})";
            
            // SKU分配
            if (isset($userConfig['seller_config']) && isset($userConfig['seller_config']['sku_default_user']) &&
                 $userConfig['seller_config']['sku_default_user'] > 0) {
                $express .= " and user_id={$userConfig['seller_config']['sku_default_user']}";
            }
        }
        
        $columns = array(
            'shop_id'
        );
        $conditions = 'seller_id=:seller_id and is_delete=' . boolConvert::toInt01(false) . ' and status=1';
        $params = array(
            ':seller_id' => Yii::app()->session['userInfo']['seller_id']
        );
        
        // 账号和站点切换逻辑
        $siteId = isset(Yii::app()->session['switchInfo']['siteId']) ? Yii::app()->session['switchInfo']['siteId'] : - 1;
        $accountId = isset(Yii::app()->session['switchInfo']['accountId']) ? Yii::app()->session['switchInfo']['accountId'] : 0;
        if (is_numeric($siteId) && $siteId > - 1) {
            $conditions .= ' and site_id=:site_id';
            $params[':site_id'] = $siteId;
        }
        
        $shopArr = ShopDAO::getInstance()->iselect($columns, $conditions, $params);
        
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
        
        // 聚合条件
        if ($aggregate == 1 && empty($searchCon)) {
            $express = "{$express} and m.aggregate_hide='" . boolConvert::toStr01(false) . "'";
        }
        
        $key = md5(
            __METHOD__ . ',,' . $express . ',,' . implode(',', $paramArr) . ',,' . $page . ',,' . $pageSize . ',,' . $searchCon . ',,' . $listType);
        $result = iMemcache::getInstance()->get($key);
        if ($result !== false) {
            return $result;
        }
        
        labelx:
        $list = MsgDealDAO::getInstance()->getMessageList($express, $paramArr, $page, $pageSize, $searchCon, $listType);
        
        if (empty($list['list']) && $page > 1) {
            $page --;
            goto labelx;
        }
        
        if (! empty($list)) {
            $result = $this->handleApiFormat(EnumOther::ACK_SUCCESS, $list);
            // iMemcache::getInstance()->set($key, $result, 60);
            return $result;
        } else {
            $result = $this->handleApiFormat(EnumOther::ACK_WARNING);
            // iMemcache::getInstance()->set($key, $result, 60);
            return $result;
        }
    }

    /**
     * @desc 查询待处理数
     * @author liaojianwen,YangLong
     * @date 2015-06-11
     * @return Ambigous mixed
     * @deprecated 需要重构
     */
    public function getDisposeCount()
    {
        $userConfig = UserModel::model()->getUserConfigs();
        $shopIds = implode(',', $userConfig['shops']);
        
        $express = "handled = :param and is_delete = '" . EnumMsgStatus::MSG_DELETE_NOT . "' and Sender!='eBay'";
        $express .= " and Replied='" . boolConvert::toStr01(false) . "' and aggregate_hide='" . boolConvert::toStr01(false) . "' and FolderID!=1";
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            if (empty($shopIds)) {
                $shopIds = 0;
            }
            $express .= " and shop_id in ({$shopIds})";
        }
        $param = boolConvert::toInt01(false);
        
        $parr = array();
        $parr['seller_id'] = Yii::app()->session['userInfo']['seller_id'];
        $parr['is_delete'] = boolConvert::toInt01(false);
        $parr['status'] = 1;
        // 账号和站点切换逻辑
        $siteId = isset(Yii::app()->session['switchInfo']['siteId']) ? Yii::app()->session['switchInfo']['siteId'] : - 1;
        $accountId = isset(Yii::app()->session['switchInfo']['accountId']) ? Yii::app()->session['switchInfo']['accountId'] : 0;
        if (is_numeric($siteId) && $siteId > - 1) {
            $parr['site_id'] = $siteId;
        }
        
        $return = array(
            'shop_id'
        );
        
        $shopArr = ShopDAO::getInstance()->findAllByAttributes($parr, $return, '');
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
        $result = MsgDealDAO::getInstance()->getDisposeCount($express, $paramArr);
        if (! empty($result)) {
            $result = $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
        }
        return $result;
    }

    /**
     * @desc 获取信息明细
     * @param int $mids  信息ID
     * @param string $msgType 邮件类型
     * @param int $sellerId 用户ID
     * @author liaojianwen
     * @date 2015-02-02
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     * @midify YangLong 2015-05-28 优化SQL
     * @deprecated 干掉它 XXX
     */
    public function showMessages($mids, $msgType, $classID)
    {
        if (empty($mids) || empty($msgType)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '信息不存在1');
        }
        $paramArr = array(
            'and'
        );
        $paramVal = array();
        
        // 信息分类
        switch ($msgType) {
            case EnumOther::IMS_PENDING:
                $paramArr[] = "is_delete = " . EnumMsgStatus::MSG_DELETE_NOT . " and handled = :param";
                $paramVal[':param'] = EnumMsgStatus::HANDLED_NOT;
                break;
            case EnumOther::IMS_STAR:
                $paramArr[] = "is_delete = " . EnumMsgStatus::MSG_DELETE_NOT . " and is_star = :param";
                $paramVal[':param'] = EnumMsgStatus::STAR_SUCCE;
                break;
            case EnumOther::IMS_SYS:
                $paramArr[] = "is_delete = " . EnumMsgStatus::MSG_DELETE_NOT . " and Sender = :param";
                $paramVal[':param'] = 'eBay';
                break;
            case EnumOther::IMS_SENT:
                $paramArr[] = "is_delete = " . EnumMsgStatus::MSG_DELETE_NOT . " and FolderID = :param";
                // TODO
                $paramVal[':param'] = 1;
                break;
            case EnumOther::IMS_SALEBEFORE:
                $paramArr[] = "is_delete = " . EnumMsgStatus::MSG_DELETE_NOT . " and FolderID = 0 and OrderID = '' and Sender <> :param";
                $paramVal[':param'] = 'eBay';
                break;
            case EnumOther::IMS_SALEAFTER:
                $paramArr[] = "is_delete = " . EnumMsgStatus::MSG_DELETE_NOT . " and FolderID = 0 and OrderID <> '' and Sender<> :param";
                $paramVal[':param'] = "eBay";
                break;
            case EnumOther::IMS_DELETE:
                $paramArr[] = "is_delete = :param";
                $paramVal[':param'] = EnumMsgStatus::MSG_DELETE_SUCCE;
                break;
        }
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $msgDao = MsgDealDAO::getInstance();
        $returnArr = array(
            'msg_id',
            'shop_id',
            'MessageID',
            'Sender',
            'SendToName',
            'Subject',
            'ReceiveDate',
            'ItemID',
            'OrderID',
            'ItemTitle',
            'is_star',
            'handled',
            'ItemEndTime',
            'send_status',
            '`Read`',
            'FolderID'
        );
        
        // 获取信息明细
        $result = $msgDao->findAllByAttributes(array(
            'msg_id' => $mids
        ), $returnArr, '');
        $result = array_pop($result);
        
        // 账号和站点切换逻辑
        $siteId = isset(Yii::app()->session['switchInfo']['siteId']) ? Yii::app()->session['switchInfo']['siteId'] : - 1;
        $accountId = isset(Yii::app()->session['switchInfo']['accountId']) ? Yii::app()->session['switchInfo']['accountId'] : 0;
        if (is_numeric($siteId) && $siteId > - 1) {
            $parr['site_id'] = $siteId;
        }
        
        $columns = array(
            'shop_id'
        );
        $conditions = 'is_delete=' . boolConvert::toInt01(false) . ' and status=1 and seller_id=:seller_id';
        $params = array(
            ':seller_id' => $sellerId
        );
        $shopArr = ShopDAO::getInstance()->iselect($columns, $conditions, $params);
        foreach ($shopArr as $value) {
            $idArr[] = $value['shop_id'];
        }
        
        $idArr = implode(',', $idArr);
        if (is_numeric($accountId) && $accountId > 0) {
            $idArr = $accountId;
        }
        
        // 处理信息内容
        $columns = array(
            'AccountID'
        );
        $conditions = 'shop_id=:shop_id';
        $params = array(
            ':shop_id' => $result['shop_id']
        );
        $result['AccountID'] = ShopDAO::getInstance()->iselect($columns, $conditions, $params, 'queryScalar');
        
        $msgkey = $result['MessageID'];
        $_dir = gmdate('/Ym/', $result['ReceiveDate']) . $result['AccountID'];
        $result['Text'] = FileLog::getInstance()->read(EnumOther::LOG_DIR_MSG_TEXT . $_dir, $msgkey);
        
        $result['Text'] = tidyTool::cleanRepair($result['Text']);
        
        $doc = phpQuery::newDocumentHTML($result['Text']);
        $ItemDetail = pq('#ItemDetails')->html();
        
        if (! empty($ItemDetail)) {
            $doc = phpQuery::newDocumentHTML($ItemDetail);
            $result['url'] = $doc['a:first']->attr('href');
            $result['img'] = $doc['img']->attr('src');
            // guest ItemID hide
            if (Yii::app()->session['userInfo']['user_id'] == 99999) {
                $result['url'] = preg_replace('/(\d{8})\d{4}/', '$1****', $result['url']);
                $result['img'] = preg_replace('/(\d{8})\d{4}/', '$1****', $result['img']);
                $result['ItemID'] = preg_replace('/(\d{8})\d{4}/', '$1****', $result['ItemID']);
            }
            
            $result['cust'] = $doc['font:first']->html();
            $loca = $doc['table:eq(3) tr:eq(3) td']->text();
            $result['loca'] = substr($loca, stripos($loca, ':') + 1);
        }
        unset($doc);
        unset($ItemDetail);
        unset($result['Text']);
        
        // $result = array_merge($result, $preNex);
        $result['msgType'] = $msgType;
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, array(
            'content' => $result
        ));
    }

    /**
     * @desc 添加模板分类
     * @param string $classname 模板名称         
     * @param integer $pid 模板父ID            
     * @return number
     * @author YangLong
     * @date 2015-02-12
     */
    public function addTpClass($classname, $pid)
    {
        if (empty($classname)) {
            return false;
        }
        return MsgDealDAO::getInstance()->addTpClass($classname, $pid);
    }

    /**
     * @desc 获取模板分类列表
     * @param string $classname            
     * @param integer $pid  父ID
     * @param boolean $alternative jstree格式
     * @return number
     * @author YangLong
     * @date 2015-02-12
     */
    public function getTpClassList($pid = -1, $alternative = false)
    {
        return MsgDealDAO::getInstance()->getTpClassList($pid, $alternative);
    }

    /**
     * @desc 模板分类的编辑和添加
     * @param int $cid 分类ID
     * @param int $pid 分类的父ID
     * @param string $classname 分类名称
     * @author YangLong
     * @date 2015-02-25
     */
    public function tpClassEdit($cid, $pid, $classname)
    {
        if (empty($classname)) {
            return array(
                'Ack' => 'Failure',
                'error' => array(
                    'error_code' => 'none',
                    'error_message' => "classname cannot empty."
                )
            );
        }
        $result = MsgDealDAO::getInstance()->tpClassEdit($cid, $pid, $classname);
        return $result;
    }

    /**
     * @desc 模板分类的移动
     * @param int $cid 分类ID
     * @param int $pid 分类的父ID
     * @author YangLong
     * @date 2015-03-02
     */
    public function tpClassMove($cid, $pid)
    {
        if (empty($cid)) {
            return array(
                'Ack' => 'Failure',
                'error' => array(
                    'error_code' => 'none',
                    'error_message' => "cid or pid cannot empty."
                )
            );
        }
        $result = MsgDealDAO::getInstance()->tpClassMove($cid, $pid);
        return $result;
    }

    /**
     * @desc 获取模板类别
     * @param string $pid 父ID
     * @param int $sellerId 客户ID
     * @param array $pageInfo 分页
     * @param int $type 是否分页
     * @author heguangquan
     * @date  2015-02-03
     * @return array 模板列表数据
     */
    public function getTpList($pid, $sellerId, $pageInfo, $type)
    {
        if (empty($sellerId)) {
            return array(
                'list' => '',
                'count' => ''
            );
        }
        
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, MsgDealDAO::getInstance()->getTpList($pid, $sellerId, $pageInfo, $type));
    }

    /**
     * @desc 删除模板列表
     * @param string $tpId 删除的模板ID
     * @author heguangquan
     * @date 2015-02-03
     */
    public function deteleTpList($tid)
    {
        if (empty($tid)) {
            return array(
                'state' => 0
            );
        }
        $result = MsgDealDAO::getInstance()->deteleTpList($tid);
        $result = empty($result) ? array(
            'state' => 0
        ) : array(
            'state' => 1
        );
        return $result;
    }

    /**
     * @desc 模板的添加和编辑
     * @param int $tpId 添加的模板标题的父ID    
     * @param int $classId 添加的模板类父ID
     * @param string $className 添加的模板类名称        
     * @param string $title   模版标题         
     * @param string $content 模版内容           
     * @param int $sellerId 客户ID
     * @author YangLong
     * @date 2015-02-11            
     * @return number
     */
    public function tpEdit($tpId, $classId, $className, $title, $content, $sellerId)
    {
        $result = MsgDealDAO::getInstance()->tpEdit($tpId, $classId, $className, $title, $content, $sellerId);
        return $result;
    }

    /**
     * @desc 根据 模板的自增ID获取模板详情，包括分类名称
     * @param int $tpId 模板ID
     * @param int $sellerId 客户ID
     * @author YangLong
     * @date 2015-02-11 
     * @return mixed
     */
    public function getTp($tpId, $sellerId)
    {
        if (empty($tpId) || empty($sellerId)) {
            return false;
        }
        $result = MsgDealDAO::getInstance()->getTp($tpId, $sellerId);
        return $result;
    }

    /**
     * @desc 获取模板明细
     * @param int $tid 模板父ID
     * @author heguangquan
     * @date  2015-02-03
     * @return array 模板明细
     */
    public function getTpDetail($tid)
    {
        if (empty($tid)) {
            return array(
                'state' => 0
            );
        }
        $resultArr = MsgDealDAO::getInstance()->getTpDetail($tid);
        return $resultArr;
    }

    /**
     * @desc 将消息标记为已读/未读
     * @param string $mids 信息ID          
     * @param string $value 设置是否已读/文件夹ID        
     * @param int $type 修改类型 1:设置是否阅读 2:设置文件夹ID
     * @author YangLong,heguangquan
     * @date 2015-02-12
     * @return boolean|multitype:NULL dateTime
     */
    public function setMessagesStatus($mids, $value, $type = 1)
    {
        if (empty($mids)) {
            return false;
        }
        ignore_user_abort(true);
        
        $mTokens = MsgDealDAO::getInstance()->getTokensByMids($mids);
        $gmTokens = array();
        foreach ($mTokens as $key => $mToken) {
            $gmTokens[$mToken['shop_id']][] = array(
                'token' => $mToken['token'],
                'm' => $mToken['MessageID'],
                's' => $mToken['site_id']
            );
        }
        if (empty($gmTokens)) {
            return array(
                'Ack' => 'Failure'
            );
        }
        $result = array();
        foreach ($gmTokens as $midsarray) {
            $msgcount = count($midsarray);
            $tmp = array();
            foreach ($midsarray as $key => $msgInfo) {
                $tmp[] = $msgInfo['m'];
                if (($key + 1) % 10 == 0 || $key == $msgcount - 1) {
                    $this->setRequestToken($msgInfo['token'], $msgInfo['s']);
                    $revisemymessagesrequest = new ReviseMyMessagesRequestType();
                    $mymessagesmessageidarray = new MyMessagesMessageIDArrayType();
                    $revisemymessagesrequest->setMessageIDs($mymessagesmessageidarray);
                    foreach ($tmp as $tmpmid) {
                        $mymessagesmessageidarray->addMessageID($tmpmid);
                    }
                    if ($type == 1) {
                        // 设置是否阅读
                        $revisemymessagesrequest->setRead($value);
                    }
                    if ($type == 2) {
                        // 设置文件夹ID
                        $revisemymessagesrequest->setFolderID($value);
                    }
                    $revisemymessagesrequest->setVersion(self::VESRION);
                    $response = $this->proxy->ReviseMyMessages($revisemymessagesrequest);
                    $result['Timestamp'] = $response->Timestamp;
                    $result['Ack'] = $response->Ack;
                    $result['CorrelationID'] = $response->CorrelationID;
                    if (! is_null($response->Errors)) {
                        foreach ($response->Errors as $ekey => $evalue) {
                            $result['Errors'][$ekey]['ShortMessage'] = $evalue->ShortMessage;
                            $result['Errors'][$ekey]['LongMessage'] = $evalue->LongMessage;
                            $result['Errors'][$ekey]['ErrorCode'] = $evalue->ErrorCode;
                            $result['Errors'][$ekey]['UserDisplayHint'] = $evalue->UserDisplayHint;
                            $result['Errors'][$ekey]['SeverityCode'] = $evalue->SeverityCode;
                            if (! is_null($evalue->ErrorParameters)) {
                                foreach ($evalue->ErrorParameters as $epkey => $epvalue) {
                                    $result['Errors'][$ekey]['ErrorParameters'][$epkey]['Value'] = $epvalue->Value;
                                    $result['Errors'][$ekey]['ErrorParameters'][$epkey]['_ns'] = $epvalue->_ns;
                                    if (! is_null($epvalue->attributeValues)) {
                                        foreach ($epvalue->attributeValues as $eppkey => $eppvalue) {
                                            $result['Errors'][$ekey]['ErrorParameters'][$epkey]['attributeValues'][$eppkey]['ParamID'] = $eppvalue;
                                        }
                                    }
                                    $result['Errors'][$ekey]['ErrorParameters'][$epkey]['value'] = $epvalue->value;
                                }
                            }
                            $result['Errors'][$ekey]['ErrorClassification'] = $evalue->ErrorClassification;
                            $result['Errors'][$ekey]['_ns'] = $evalue->_ns;
                            $result['Errors'][$ekey]['attributeValues'] = $evalue->attributeValues;
                            $result['Errors'][$ekey]['value'] = $evalue->value;
                        }
                    }
                    $result['Message'] = $response->Message;
                    $result['Version'] = $response->Version;
                    $result['Build'] = $response->Build;
                    $result['NotificationEventName'] = $response->NotificationEventName;
                    $result['DuplicateInvocationDetails'] = $response->DuplicateInvocationDetails;
                    $result['RecipientUserID'] = $response->RecipientUserID;
                    $result['EIASToken'] = $response->EIASToken;
                    $result['NotificationSignature'] = $response->NotificationSignature;
                    $result['HardExpirationWarning'] = $response->HardExpirationWarning;
                    $result['BotBlock'] = $response->BotBlock;
                    $result['ExternalUserData'] = $response->ExternalUserData;
                    $result['_ns'] = $response->_ns;
                    $result['attributeValues'] = $response->attributeValues;
                    $result['value'] = $response->value;
                    $tmp = array();
                }
            }
        }
        return $result;
    }

    /**
     * @desc 将模板分类标记为已删除
     * @param string $cids 模板ID           
     * @return multitype:string multitype:string  |number
     * @author YangLong
     * @date 2015-02-12
     */
    public function deleteTpClass($cids)
    {
        if (empty($cids)) {
            return array(
                'Ack' => 'Failure',
                'error' => array(
                    'error_code' => 'none',
                    'error_message' => "cids cannot empty."
                )
            );
        }
        $result = MsgDealDAO::getInstance()->deleteTpClass($cids);
        return $result;
    }

    /**
     * @desc 调用API获取一个 ebay session
     * @author YangLong
     * @date 2015-02-12
     * TODO 重构
     * @return array 包含sessionid的数组
     */
    private function getEbaySessionIDbyApi()
    {
        $this->setRequestToken();
        
        $getsessionidrequest = new GetSessionIDRequestType();
        $getsessionidrequest->setRuName(Yii::app()->params['eBayRuName']);
        $getsessionidrequest->setVersion(self::VESRION);
        
        $response = $this->proxy->GetSessionID($getsessionidrequest);
        
        if (! is_null($response->Ack) && $response->Ack == 'Success') {
            return array(
                'Ack' => 'Success',
                'data' => array(
                    'SessionID' => $response->SessionID,
                    'RuName' => $getsessionidrequest->getRuName()
                )
            );
        } else {
            return array(
                'Ack' => 'Failure',
                'error' => array(
                    'error_code' => 'none',
                    'error_message' => "get session ID error.",
                    'Errors1' => @$response->Errors[0]->LongMessage,
                    'Errors2' => @$response->Errors[1]->LongMessage
                )
            );
        }
    }

    /**
     * @desc 获取SessionID返回给浏览器构造出跳转URL
     * @author YangLong
     * @date 2015-02-12
     * @return string 一个ebay sessionid
     */
    public function getEbaySessionID()
    {
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING, '');
        }
        
        $sessionid = $this->getEbaySessionIDbyApi();
        return $sessionid;
    }

    /**
     * @desc 浏览器发送回SessionID根据此SessionID获取token并保存到数据库
     * @param string $sessionid
     * @param string $account
     * @param string $nickname
     * @param number $siteid
     * @author liaojianwen YangLong
     * @date 2015-02-05
     * @return Ambigous boolean
     */
    public function saveTokenBySessionID($sessionid = '', $account = '', $nickname = '', $siteid = -1)
    {
        $result = $this->fetchToken($sessionid);
        if ($result === false) {
            return array(
                'Ack' => 'Failure',
                'error' => array(
                    'error_code' => 'none',
                    'error_message' => "fetchToken error."
                )
            );
        }
        $columns = array(
            'seller_id' => Yii::app()->session['userInfo']['seller_id'],
            'EbaySessionID' => $sessionid,
            'token' => $result['eBayAuthToken'],
            'HardExpirationTime' => strtotime($result['HardExpirationTime']),
            'account' => $account,
            'nick_name' => $nickname,
            'site_id' => $siteid
        );
        
        iMongo::getInstance()->setCollection('fetchToken')->insert(
            array(
                'result' => $result,
                'time' => time()
            ));
        
        $accountInfo = $this->geteBayAccount($result['eBayAuthToken'], $errorinfo);
        if ($accountInfo !== false) {
            $columns['AccountID'] = $accountInfo['AccountID'];
            $columns['AccountState'] = $accountInfo['AccountState'];
        } else {
            return array(
                'Ack' => 'Failure',
                'error' => array(
                    'error_code' => 'F0001',
                    'error_message' => $errorinfo['LongMessage'],
                    'error_desc' => 'GeteBayAccount Failure'
                )
            );
        }
        $userInfo = $this->geteBayUserInfoByToken($result['eBayAuthToken']);
        if ($userInfo !== false) {
            $columns['account'] = $userInfo['UserID'];
            foreach (EnumMsgStatus::$SiteCodeType as $key => $value) {
                if ($value == $userInfo['Site']) {
                    $columns['site_id'] = $key;
                }
            }
        } else {
            return array(
                'Ack' => 'Failure',
                'error' => array(
                    'error_code' => 'F0002',
                    'error_message' => "get AccountID error.",
                    'error_desc' => 'GeteBayUserInfoByToken Failure'
                )
            );
        }
        $result = ShopDAO::getInstance()->saveToken($columns);
        return $result;
    }

    /**
     * @desc 根据sessionID获取token，并返回
     * @param string $sessionid
     * @author YangLong
     * @date 2015-02-12
     * @return multitype:NULL |boolean
     */
    private function fetchToken($sessionid)
    {
        $this->setRequestToken();
        
        $fetchtokenrequest = new FetchTokenRequestType();
        $fetchtokenrequest->setSessionID($sessionid);
        $fetchtokenrequest->setVersion(self::VESRION);
        
        $response = $this->proxy->FetchToken($fetchtokenrequest);
        
        if (! is_null($response->Ack) && $response->Ack == 'Success') {
            return array(
                'eBayAuthToken' => $response->eBayAuthToken,
                'HardExpirationTime' => $response->HardExpirationTime
            );
        } else {
            return false;
        }
    }

    /**
     * @desc 删除消息
     * @param string $mids 例如:1,2,3,4,5 要求是msg表对应的自增ID
     * @author YangLong
     * @date 2015-02-12
     * @return boolean
     */
    public function deleteMyMessages($mids = '')
    {
        if (empty($mids)) {
            return false;
        }
        $data = MsgDealDAO::getInstance()->getMIByMID($mids);
        $gdata = array();
        foreach ($data as $key => $value) {
            $gdata[$value['token']][] = array(
                'm' => $value['MessageID'],
                's' => $value['site_id']
            );
        }
        $result = array();
        foreach ($gdata as $token => $MessageIDs) {
            $tmp = array();
            $count = count($MessageIDs);
            foreach ($MessageIDs as $key => $MessageID) {
                $tmp[] = $MessageID['m'];
                if (($key + 1) % 10 == 0 || $key + 1 == $count) {
                    $this->setRequestToken($token, $MessageID['s']);
                    $deletemymessagesrequest = new DeleteMyMessagesRequestType();
                    $mymessagesmessageidarray = new MyMessagesMessageIDArrayType();
                    $deletemymessagesrequest->setMessageIDs($mymessagesmessageidarray);
                    foreach ($tmp as $mid) {
                        $mymessagesmessageidarray->addMessageID($mid);
                    }
                    $deletemymessagesrequest->setVersion(self::VESRION);
                    $response = $this->proxy->DeleteMyMessages($deletemymessagesrequest);
                    if (! is_null($response->Ack) && $response->Ack == 'Success') {
                        $result[$MessageID['m']] = true;
                    } else {
                        $result[$MessageID['m']] = false;
                    }
                    $tmp = array();
                }
            }
        }
        return $result;
    }

    /**
     * @desc 获取ebay账户唯一标识ID
     * @param string $token
     * @param mixed $errorinfo
     * @author YangLong
     * @date 2015-03-06
     */
    private function geteBayAccount($token, &$errorinfo)
    {
        $this->setRequestToken($token);
        
        $getaccountrequest = new GetAccountRequestType();
        $getaccountrequest->setVersion(self::VESRION);
        
        $response = $this->proxy->GetAccount($getaccountrequest);
        if (! is_null($response->Ack) && $response->Ack == 'Success') {
            $result['AccountID'] = $response->AccountID;
            $result['AccountState'] = $response->AccountSummary->AccountState;
            return $result;
        } else {
            $errorinfo['ShortMessage'] = $response->Errors[0]->ShortMessage;
            $errorinfo['LongMessage'] = $response->Errors[0]->LongMessage;
            return false;
        }
    }

    /**
     * @desc 获取Ebay账号信息
     * @author YangLong
     * @param string $token
     * @date 2015-03-18
     */
    private function geteBayUserInfoByToken($token)
    {
        $this->setRequestToken($token);
        
        $getuserrequest = new GetUserRequestType();
        $getuserrequest->setVersion(self::VESRION);
        
        $response = $this->proxy->GetUser($getuserrequest);
        if (! is_null($response->Ack) && $response->Ack == 'Success') {
            $result['Email'] = $response->User->Email;
            $result['Site'] = $response->User->Site;
            if (boolConvert::toInt01($response->User->SellerInfo->StoreSite)) {
                $result['Site'] = $response->User->SellerInfo->StoreSite;
            }
            $result['Status'] = $response->User->Status;
            $result['UserID'] = $response->User->UserID;
            return $result;
        } else {
            return false;
        }
    }

    /**
     * @desc 调用cdc回复队列执行结果返回
     * @param unknown_type $msgid 消息id
     * @param unknown_type $Replied 是否回复
     * @param unknown_type $handled 是否代办
     * @param unknown_type $send_status 是否发送成功
     * @date 2015-04-27
     * @author lvjianfei
     */
    public function UpdateMsgStatus($msgid, $Replied, $handled, $send_status)
    {
        $msgDao = MsgDealDAO::getInstance();
        $result = $msgDao->updateByPk($msgid, 
            array(
                'Replied' => (string) $Replied,
                'handled' => (string) $handled,
                'send_status' => (string) $send_status
            ));
        if ($result === false) {
            return false;
        }
        setcookie("DisposeMsgId", "", time() + 3600 * 24);
        return $result;
    }

    /**
     * @desc 添加msg备注
     * @param integer $msgid msg的id
     * @param string $text备注text
     * @param int $sellerId serller_id
     * @author liaojianwen
     * @date 2015-05-25
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function addItemNote($text, $msgid, $sellerId)
    {
        if ($msgid <= 0) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '`msgid` can not empty.');
        }
        
        $columns = array(
            'm.ItemID',
            'm.Sender',
            'm.SendToName',
            's.account'
        );
        $conditions = 'm.msg_id=:msg_id and s.seller_id=:seller_id';
        $params = array(
            ':msg_id' => $msgid,
            ':seller_id' => $sellerId
        );
        $joinArray = array(
            array(
                ShopDAO::getInstance()->igetproperty('tableName') . ' s',
                's.shop_id=m.shop_id and s.is_delete=0'
            )
        );
        $tableAlias = 'm';
        $tokeninfo = MsgDAO::getInstance()->iselect($columns, $conditions, $params, false, $joinArray, $tableAlias);
        $cust = '';
        if ($tokeninfo !== false) {
            if ($tokeninfo['account'] != $tokeninfo['Sender']) {
                $cust = $tokeninfo['Sender'];
            } elseif ($tokeninfo['account'] != $tokeninfo['SendToName']) {
                $cust = $tokeninfo['SendToName'];
            } else {
                $cust = $tokeninfo['account'];
            }
            
            $itemnote = ItemNoteDAO::getInstance();
            $username = isset(Yii::app()->session['userInfo']['username']) ? Yii::app()->session['userInfo']['username'] : 0;
            if ($username !== 0) {
                $param['author_name'] = $username;
            } else {
                return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户未登陆');
            }
            $param['text'] = $text;
            $param['create_time'] = time();
            $param['item_id'] = $tokeninfo['ItemID'];
            $param['cust'] = $cust;
            $result = $itemnote->insert($param);
            if ($result === false) {
                return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
            } else {
                return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
            }
        }
    }

    /**
     * @desc 升级最新最多200条消息的一些状态信息（无队列）(收件箱)
     * @author YangLong
     * @date 2015-06-02
     * @return null
     */
    public function updateMsgByList()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $startTime = time();
        
        $columns = array(
            'site_id',
            'token',
            'shop_id'
        );
        $conditions = 'status=1 and is_delete=' . boolConvert::toInt01(false);
        $shops = ShopDAO::getInstance()->iselect($columns, $conditions, array());
        
        foreach ($shops as $shop) {
            
            $key = md5(__METHOD__ . $shop['shop_id']);
            if (iMemcache::getInstance()->get($key) === false) {
                iMemcache::getInstance()->set($key, true, 50);
                
                $page = 0;
                $pagesize = 20;
                while (true) {
                    $page ++;
                    
                    if (time() - $startTime > 600) {
                        return false;
                    }
                    
                    if (iMemcache::getInstance()->get(md5("{$key}_{$page}")) !== false) {
                        continue;
                    }
                    
                    $OutputSelector = array(
                        'MessageID',
                        'SendingUserID',
                        'ExternalMessageID',
                        'Flagged',
                        'Read',
                        'ReceiveDate',
                        'ExpirationDate',
                        'ItemID',
                        'ResponseEnabled',
                        'FolderID',
                        'MessageType',
                        'Replied',
                        'ItemEndTime',
                        'ItemTitle'
                    );
                    
                    $xml = MsgDownModel::model()->getMyMessages($shop['token'], 'ReturnHeaders', 0, 0, 0, '', $shop['site_id'], $page, $pagesize, 
                        array(), false, $OutputSelector);
                    $doc = phpQuery::newDocumentXML($xml);
                    phpQuery::selectDocument($doc);
                    if (pq('Ack')->html() === 'Success') {
                        
                        iMemcache::getInstance()->set(md5("{$key}_{$page}"), true, $page * $page * 60);
                        
                        $Messages = pq('Messages>Message');
                        $length = $Messages->length;
                        for ($i = 0; $i < $length; $i ++) {
                            $Message = $Messages->eq($i);
                            $columns = array(
                                'SendingUserID' => $Message->find('>SendingUserID')->html(),
                                'ExternalMessageID' => $Message->find('>ExternalMessageID')->html(),
                                'Flagged' => boolConvert::toStr01($Message->find('>Flagged')->html()),
                                'Read' => boolConvert::toStr01($Message->find('>Read')->html()),
                                'ReceiveDate' => strtotime($Message->find('>ReceiveDate')->html()),
                                'ExpirationDate' => strtotime($Message->find('>ExpirationDate')->html()),
                                'ItemID' => $Message->find('>ItemID')->html(),
                                'ResponseEnabled' => boolConvert::toStr01($Message->find('>ResponseDetails>ResponseEnabled')->html()),
                                'FolderID' => $Message->find('>Folder>FolderID')->html(),
                                'MessageType' => $Message->find('>MessageType')->html(),
                                'Replied' => boolConvert::toStr01($Message->find('>Replied')->html()),
                                'ItemEndTime' => strtotime($Message->find('>ItemEndTime')->html()),
                                'ItemTitle' => $Message->find('>ItemTitle')->html()
                            );
                            
                            // 回复移出待处理
                            if ($columns['Replied'] === boolConvert::toStr01(true)) {
                                $columns['handled'] = boolConvert::toStr01(false);
                            }
                            
                            $conditions = 'shop_id=:shop_id and MessageID=:MessageID';
                            $params = array(
                                ':shop_id' => $shop['shop_id'],
                                ':MessageID' => $Message->find('>MessageID')->html()
                            );
                            foreach ($columns as $k => $val) {
                                if ($val === false || $val === null) {
                                    unset($columns[$k]);
                                }
                            }
                            MsgDAO::getInstance()->iupdate($columns, $conditions, $params);
                        }
                        iMongo::getInstance()->setCollection('updateMsgByListXML')->insert(
                            array(
                                'shop_id' => $shop['shop_id'],
                                'page' => $page,
                                'time' => time(),
                                'strtime' => gmdate('Y-m-d H:i:s Z')
                            ));
                    } else {
                        iMongo::getInstance()->setCollection('updateMsgByListXMLF')->insert(
                            array(
                                'shop_id' => $shop['shop_id'],
                                'xml' => $xml,
                                'page' => $page,
                                'time' => time(),
                                'strtime' => gmdate('Y-m-d H:i:s Z')
                            ));
                    }
                    
                    phpQuery::selectDocument($doc);
                    if (pq('Messages>Message')->length < $pagesize) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * @desc 通过orderLIneItemID 获取评价
     * @param string $OrderLineItemID
     * @author liaojianwen
     * @date 2015-06-17
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function getFeedbackInfo($OrderLineItemID)
    {
        if (empty($OrderLineItemID)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '#orderLineItemNo# can not empty.');
        }
        $columns = array(
            'CommentType',
            'CommentText'
        );
        $conditions = 'OrderLineItemID=:OrderLineItemID and Role =:Role';
        $params = array(
            ':OrderLineItemID' => $OrderLineItemID,
            ':Role' => 'Seller'
        );
        $result = EbayFeedbackTransactionDAO::getInstance()->iselect($columns, $conditions, $params, false);
        if (! empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'no feedback.');
        }
    }

    /**
     * @desc 根据UserId获取Msgs
     * @param string $UserID 用户ID
     * @param number $page 页码
     * @param number $limit limit
     * @author YangLong
     * @date 2015-06-18
     * @return boolean|Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function getMsgsByUserID($UserID, $page, $limit = 10)
    {
        if (empty($UserID)) {
            return false;
        }
        
        $offset = ($page - 1) * 10;
        $columns = array(
            'm.msg_id',
            'm.Subject',
            'm.FolderID',
            'm.ReceiveDate',
            's.account',
            's.nick_name'
        );
        $conditions = 'm.Sender=:UserID or m.SendToName=:UserID';
        $params = array(
            ':UserID' => $UserID
        );
        $order = 'm.ReceiveDate desc';
        $joinArray = array(
            array(
                ShopDAO::getInstance()->getTableName() . ' s',
                's.shop_id=m.shop_id and s.is_delete=0'
            )
        );
        $result = MsgDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 'm', $order, $limit, $offset);
        
        foreach ($result as &$value) {
            $value['Subject'] = str_ireplace($value['account'], $value['nick_name'], $value['Subject']);
            
            // guest ItemID hide
            if (Yii::app()->session['userInfo']['user_id'] == 99999) {
                $value['Subject'] = preg_replace('/(\d{8})\d{4}/', '$1****', $value['Subject']);
            }
        }
        unset($value);
        unset($result['account']);
        
        if ($result !== false) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '');
        }
    }

    /**
     * @desc 获取历史消息正文
     * @param int $sellerId
     * @param int $msgId
     * @author YangLong
     * @date 2015-06-26
     * @return mixed
     */
    public function getMsgTexts($sellerId, $msgId)
    {
        if (empty($msgId) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '');
        }
        
        $resultAll = array();
        
        $columns = array(
            'shop_id',
            'FolderID',
            'Sender',
            'SendToName',
            'RecipientUserID',
            'Subject',
            'ReceiveDate',
            'ItemID',
            'subject_hash',
            'ItemTitle',
            'ResponseEnabled',
            'MessageID'
        );
        $msgInfo = MsgDAO::getInstance()->getValuesByPk($msgId, $columns);
        
        // 如果msg表的itemid为空则去textResolve表拿itemid
        if (empty($msgInfo['ItemID'])) {
            $columns = array(
                'ItemId'
            );
            $conditions = 'msg_id=:msg_id';
            $params = array(
                ':msg_id' => $msgId
            );
            $_temp = MsgTextResolveDAO::getInstance()->iselect($columns, $conditions, $params, false);
            if (! empty($_temp)) {
                $msgInfo['ItemID'] = $_temp['ItemId'];
            }
        }
        // 如果msg表里的标题为空则通过从Text里提取获取标题
        if (empty($msgInfo['ItemTitle'])) {
            
            $columns = array(
                'AccountID'
            );
            $conditions = 'shop_id=:shop_id';
            $params = array(
                ':shop_id' => $msgInfo['shop_id']
            );
            $msgInfo['AccountID'] = ShopDAO::getInstance()->iselect($columns, $conditions, $params, 'queryScalar');
            
            $msgkey = $msgInfo['MessageID'];
            $_dir = gmdate('/Ym/', $msgInfo['ReceiveDate']) . $msgInfo['AccountID'];
            $msgInfo['Text'] = FileLog::getInstance()->read(EnumOther::LOG_DIR_MSG_TEXT . $_dir, $msgkey);
            
            $msgInfo['Text'] = tidyTool::cleanRepair($msgInfo['Text']);
            
            $doc = phpQuery::newDocumentHTML($msgInfo['Text']);
            phpQuery::selectDocument($doc);
            $_ItemTitle = pq('#ItemDetails table table td a')->eq(0)->html();
            $msgInfo['ItemTitle'] = $_ItemTitle;
            unset($doc);
            
            unset($msgInfo['Text']);
            unset($msgInfo['AccountID']);
        }
        
        $resultAll['Subject'] = $msgInfo['Subject'];
        $resultAll['Sender'] = $msgInfo['Sender'];
        $resultAll['SendToName'] = $msgInfo['SendToName'];
        $resultAll['ReceiveDate'] = $msgInfo['ReceiveDate'];
        $resultAll['ItemID'] = $msgInfo['ItemID'];
        $resultAll['ItemTitle'] = $msgInfo['ItemTitle'];
        $resultAll['ResponseEnabled'] = $msgInfo['ResponseEnabled'];
        $resultAll['FolderID'] = $msgInfo['FolderID'];
        if ($msgInfo['FolderID'] == 1) {
            $UserID = $msgInfo['SendToName'];
        } else {
            $UserID = $msgInfo['Sender'];
        }
        $resultAll['UserID'] = $UserID;
        
        $columns = array(
            'EIASToken'
        );
        $conditions = 'UserID=:UserID';
        $params = array(
            ':UserID' => $UserID
        );
        $resultAll['EIASToken'] = EbayUserInfoDAO::getInstance()->iselect($columns, $conditions, $params, 'queryScalar');
        
        $columns = array(
            'regaddr_Country'
        );
        $conditions = 'UserID=:UserID';
        $params = array(
            ':UserID' => $UserID
        );
        $resultAll['regaddr_Country'] = EbayUserInfoDAO::getInstance()->iselect($columns, $conditions, $params, 'queryScalar');
        
        if (empty($resultAll['SendToName'])) {
            $resultAll['SendToName'] = $msgInfo['RecipientUserID'];
        }
        
        $columns = array(
            'account',
            'nick_name',
            'AccountID'
        );
        $shopInfo = ShopDAO::getInstance()->getValuesByPk($msgInfo['shop_id'], $columns);
        $resultAll['Subject'] = str_ireplace($shopInfo['account'], $shopInfo['nick_name'], $resultAll['Subject']);
        $resultAll['SendToName'] = str_ireplace($shopInfo['account'], $shopInfo['nick_name'], $resultAll['SendToName']);
        $resultAll['Sender'] = str_ireplace($shopInfo['account'], $shopInfo['nick_name'], $resultAll['Sender']);
        
        if ($UserID == 'eBay' || stripos($UserID, '@ebay.com') !== false || $msgInfo['SendToName'] === $msgInfo['Sender'] ||
             $msgInfo['Sender'] === 'eBay CS Support') {
            // $columns = array(
            // 't.Text'
            // );
            // $conditions = 'm.msg_id=:msg_id';
            // $params = array(
            // ':msg_id' => $msgId
            // );
            // $joinArray = array(
            // array(
            // 'msg_text t',
            // 't.msg_id=m.msg_id'
            // )
            // );
            // $result = MsgDAO::getInstance()->iselect($columns, $conditions, $params, false, $joinArray, 'm');
            $msgkey = $msgInfo['MessageID'];
            $_dir = gmdate('/Ym/', $resultAll['ReceiveDate']) . $shopInfo['AccountID'];
            $result['Text'] = FileLog::getInstance()->read(EnumOther::LOG_DIR_MSG_TEXT . $_dir, $msgkey);
            if (! empty($result['Text'])) {
                
                $result['Text'] = tidyTool::cleanRepair($result['Text']);
                
                $doc = phpQuery::newDocumentHTML($result['Text']);
                phpQuery::selectDocument($doc);
                $result['Text'] = $doc->find('body')->html();
                unset($doc);
                
                // 替换为别名
                $result['Text'] = str_ireplace($shopInfo['account'], $shopInfo['nick_name'], $result['Text']);
                
                // URL提取
                $resultAll['ItemUrl'] = pq('#itemDetailsComponent')->find('table table table a')
                    ->eq(1)
                    ->attr('href');
                
                // guest ItemID hide
                if (Yii::app()->session['userInfo']['user_id'] == 99999) {
                    $resultAll['ItemUrl'] = preg_replace('/(\d{8})\d{4}/', '$1****', $resultAll['ItemUrl']);
                }
            } else {
                // 异常发邮件
                $subject = "shop_id:{$msgInfo['shop_id']} msg_id:{$msgId} Text字段缺少";
                $text = $subject;
                $to = Yii::app()->params['logmails'];
                SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            }
            $resultAll['Content'] = $result;
            
            // 标签获取
            $columns = array(
                'ml.msg_label_id',
                'ml.label_title',
                'ml.label_color'
            );
            $conditions = 'msg_id=:msg_id';
            $params = array(
                ':msg_id' => $msgId
            );
            $joinArray = array(
                array(
                    MsgLabelDAO::getInstance()->getTableName() . ' ml',
                    'mlr.msg_label_id=ml.msg_label_id'
                )
            );
            $resultAll['msgLabels'] = MsgLabelRefDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 'mlr');
        } else {
            $columns = array(
                'm.msg_id',
                'm.FolderID',
                'm.Sender',
                'm.SendToName',
                'm.ReceiveDate',
                'm.MessageType',
                'm.QuestionType',
                'm.aggregate_hide',
                'm.handled',
                'tr.effect_content',
                'tr.ImagePreview',
                'tr.OrderId',
                's.account',
                's.nick_name'
            );
            if ($resultAll['Sender'] === $resultAll['SendToName']) {
                $conditions = '(Sender=:UserID or SendToName=:UserID) and Sender=SendToName';
            } else {
                $conditions = '(Sender=:UserID or SendToName=:UserID)';
            }
            $params = array(
                ':UserID' => $UserID
            );
            if (! empty($resultAll['ItemID'])) {
                $conditions .= ' and tr.ItemId=:ItemId';
                $params[':ItemId'] = $resultAll['ItemID'];
            } else {
                // 没有ItemId时,按subject_hash聚合
                $conditions .= ' and m.subject_hash=:subject_hash';
                $params[':subject_hash'] = $msgInfo['subject_hash'];
            }
            $conditions .= ' and m.shop_id=:shop_id';
            $params[':shop_id'] = $msgInfo['shop_id'];
            $order = 'ReceiveDate desc';
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
            // 数据异常写日志
            if (empty($result)) {
                // iMongo::getInstance()->setCollection('MsgTextsEmpty')->insert(array(
                // 'sql' => MsgDAO::getInstance()->getLastSql(),
                // 'columns' => serialize($columns),
                // 'conditions' => serialize($conditions),
                // 'params' => serialize($params),
                // 'joinArray' => serialize($joinArray),
                // 'order' => serialize($order),
                // 'time' => time()
                // ));
                
                // 异常发邮件
                $subject = "MsgTextsEmpty";
                ob_start();
                $_arr = array(
                    'sql' => MsgDAO::getInstance()->getLastSql(),
                    'columns' => $columns,
                    'conditions' => $conditions,
                    'params' => $params,
                    'joinArray' => $joinArray,
                    'order' => $order,
                    'time' => time()
                );
                var_export($_arr);
                unset($_arr);
                $text = ob_get_clean();
                $to = Yii::app()->params['logmails'];
                SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            }
            $F0 = $F1 = true;
            foreach ($result as $key => $value) {
                $result[$key]['effect_content'] = str_ireplace($result[$key]['account'], $result[$key]['nick_name'], 
                    $result[$key]['effect_content']);
                $result[$key]['Sender'] = str_ireplace($result[$key]['account'], $result[$key]['nick_name'], $result[$key]['Sender']);
                $result[$key]['SendToName'] = str_ireplace($result[$key]['account'], $result[$key]['nick_name'], $result[$key]['SendToName']);
                
                $doc = phpQuery::newDocumentHTML($result[$key]['effect_content']);
                phpQuery::selectDocument($doc);
                
                $result[$key]['effect_content'] = $doc->html();
                
                unset($result[$key]['account']);
                unset($doc);
                
                // 提取正文计算MD5
                pq('font>strong')->remove();
                $_text = pq('font')->eq(0)->html();
                $_text = str_ireplace('<br>', "\n", $_text);
                $_text = str_ireplace('<br/>', "\n", $_text);
                $_text = imsTool::msgClear($_text);
                // ...
                // TODO 需要完善方法！
                $result[$key]['content_md5'] = md5(trim($_text));
                
                // 聚合准备、修复
                if (($F0 && $result[$key]['FolderID'] != 1) || ($F1 && $result[$key]['FolderID'] == 1)) {
                    ($F0 && $result[$key]['FolderID'] != 1) ? $F0 = false : null;
                    ($F1 && $result[$key]['FolderID'] == 1) ? $F1 = false : null;
                    if ($result[$key]['aggregate_hide'] == boolConvert::toStr01(true)) {
                        $columns = array(
                            'aggregate_hide' => boolConvert::toStr01(false)
                        );
                        $conditions = 'msg_id=:msg_id';
                        $params = array(
                            ':msg_id' => $result[$key]['msg_id']
                        );
                        MsgDAO::getInstance()->iupdate($columns, $conditions, $params);
                    }
                } else {
                    if ($result[$key]['aggregate_hide'] == boolConvert::toStr01(false)) {
                        $columns = array(
                            'aggregate_hide' => boolConvert::toStr01(true)
                        );
                        $conditions = 'msg_id=:msg_id';
                        $params = array(
                            ':msg_id' => $result[$key]['msg_id']
                        );
                        MsgDAO::getInstance()->iupdate($columns, $conditions, $params);
                    }
                }
                
                // 标签获取
                $columns = array(
                    'ml.msg_label_id',
                    'ml.label_title',
                    'ml.label_color'
                );
                $conditions = 'msg_id=:msg_id';
                $params = array(
                    ':msg_id' => $result[$key]['msg_id']
                );
                $joinArray = array(
                    array(
                        MsgLabelDAO::getInstance()->getTableName() . ' ml',
                        'mlr.msg_label_id=ml.msg_label_id'
                    )
                );
                $result[$key]['msgLabels'] = MsgLabelRefDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 'mlr');
            }
            $resultAll['Contents'] = $result;
        }
        
        // guest ItemID hide
        if (Yii::app()->session['userInfo']['user_id'] == 99999) {
            $resultAll['Subject'] = preg_replace('/(\d{8})\d{4}/', '$1****', $resultAll['Subject']);
            $resultAll['ItemID'] = preg_replace('/(\d{8})\d{4}/', '$1****', $resultAll['ItemID']);
            $resultAll['user_id'] = 99999;
        }
        
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $resultAll);
    }

    /**
     * @desc 获取消息的相关消息的id
     * @author YangLong
     * @date 2015-11-10
     */
    public function getMsgRelationIds($msgId)
    {
        if (empty($msgId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'msgid cannot empty.');
        }
        
        $columns = array(
            'm.msg_id',
            'm.Sender',
            'm.SendToName',
            'm.FolderID',
            'm.ItemID',
            'm.subject_hash',
            'mtr.ItemId rItemID'
        );
        $conditions = 'm.msg_id=:msg_id';
        $params = array(
            ':msg_id' => $msgId
        );
        $joinArray = array(
            array(
                MsgTextResolveDAO::getInstance()->getTableName() . ' mtr',
                'mtr.msg_id=m.msg_id'
            )
        );
        $msgInfo = MsgDAO::getInstance()->iselect($columns, $conditions, $params, false, $joinArray, 'm');
        
        if (empty($msgInfo)) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING, '', 'msg not found.');
        }
        
        if ($msgInfo['FolderID'] == 1) {
            $UserID = $msgInfo['SendToName'];
        } else {
            $UserID = $msgInfo['Sender'];
        }
        
        $result = array();
        $result['msgid'] = $msgId;
        
        if (! empty($msgInfo['ItemID'])) {
            $result['ItemID'] = $msgInfo['ItemID'];
        } else {
            $result['ItemID'] = $msgInfo['rItemID'];
        }
        
        if ($UserID == 'eBay' || stripos($UserID, '@ebay.com') !== false || $msgInfo['SendToName'] === $msgInfo['Sender']) {
            $result['relationId'] = false;
        } else {
            $columns = array(
                'm.msg_id'
            );
            $conditions = '(Sender=:UserID or SendToName=:UserID)';
            $params = array(
                ':UserID' => $UserID
            );
            $joinArray = array(
                array(
                    MsgTextResolveDAO::getInstance()->getTableName() . ' mtr',
                    'mtr.msg_id=m.msg_id'
                )
            );
            
            if (! empty($result['ItemID'])) {
                $conditions .= ' and mtr.ItemID=:ItemID';
                $params[':ItemID'] = $result['ItemID'];
            } else {
                $conditions .= ' and m.subject_hash=:subject_hash';
                $params[':subject_hash'] = $msgInfo['subject_hash'];
            }
            
            $result['relationId'] = MsgDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 'm');
        }
        
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
    }

    /**
     * @desc 获取消息的客服名
     * @param int $sellerId
     * @param int $md5s
     * @param string $userId
     * @author YangLong
     * @date 2015-06-29
     * @return mixed
     */
    public function getMsgTextsCS($sellerId, $md5s, $userId)
    {
        if (empty($md5s) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '');
        }
        
        $md5s = explode(',', $md5s);
        foreach ($md5s as $key => $value) {
            $md5s[$key] = "'" . $md5s[$key] . "'";
        }
        $md5s = implode(',', $md5s);
        
        $columns = array(
            'rl.content_md5',
            'rl.action_username'
        );
        $conditions = 'rl.content_md5 in (' . $md5s . ') and (m.Sender=:UserId or m.SendToName=:UserId)';
        $params = array(
            ':UserId' => $userId
        );
        $joinArray = array(
            array(
                MsgDAO::getInstance()->getTableName() . ' m',
                'm.msg_id=rl.msg_id'
            )
        );
        $result = MsgReplyLogDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 'rl');
        
        if ($result !== false) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '');
        }
    }

    /**
     * @desc 获取用户的注册站点
     * @param string $userId
     * @author YangLong
     * @date 2015-06-29
     */
    public function getUserRegSite($userId)
    {
        if (empty($userId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '');
        }
        
        $columns = array(
            'UserID',
            'Site'
        );
        $conditions = 'UserID=:UserID';
        $params = array(
            ':UserID' => $userId
        );
        $result = EbayUserInfoDAO::getInstance()->iselect($columns, $conditions, $params, false);
        
        if ($result !== false) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '');
        }
    }

    /**
     * @desc 获取消息详情
     * @param string $msgid
     * @author YangLong
     * @date 2015-06-30
     * @return mixed
     */
    public function getMsg($msgid)
    {
        if (empty($msgid)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '');
        }
        
        $columns = array(
            'm.shop_id',
            'm.Subject',
            'm.MessageID',
            'm.ReceiveDate',
            's.AccountID'
        );
        $conditions = 'm.msg_id=:msg_id';
        $params = array(
            ':msg_id' => $msgid
        );
        $joinArray = array(
            array(
                ShopDAO::getInstance()->getTableName() . ' s',
                'm.shop_id=s.shop_id and s.is_delete=0'
            )
        );
        $result = MsgDAO::getInstance()->iselect($columns, $conditions, $params, false, $joinArray, 'm');
        
        $msgkey = $result['MessageID'];
        $_dir = gmdate('/Ym/', $result['ReceiveDate']) . $result['AccountID'];
        $result['Text'] = FileLog::getInstance()->read(EnumOther::LOG_DIR_MSG_TEXT . $_dir, $msgkey);
        
        unset($result['AccountID']);
        unset($result['MessageID']);
        unset($result['ReceiveDate']);
        
        if (! empty($result) && ! empty($result['Text'])) {
            $tidyConfig = array(
                'indent' => false,
                'output-xhtml' => true,
                'wrap' => 0
            );
            $tidy = new tidy();
            $tidy->parseString($result['Text'], $tidyConfig, 'utf8');
            $tidy->cleanRepair();
            $result['Text'] = $tidy;
            $doc = phpQuery::newDocumentHTML($result['Text']);
            phpQuery::selectDocument($doc);
            $result['Text'] = $doc->find('body')->html();
            
            // guest ItemID hide
            if (Yii::app()->session['userInfo']['user_id'] == 99999) {
                $result['Subject'] = preg_replace('/(\d{8})\d{4}/', '$1****', $result['Subject']);
                $result['Text'] = preg_replace('/(\d{8})\d{4}/', '$1****', $result['Text']);
            }
        } else {
            $subject = "getMsg no find Text";
            $text = "msg_id:{$msgid} Text Type:" . gettype($result['Text']);
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            $result['Text'] = '数据异常';
        }
        
        // 替换为别名
        $columns = array(
            'account',
            'nick_name'
        );
        $conditions = 'shop_id=:shop_id';
        $params = array(
            ':shop_id' => $result['shop_id']
        );
        $shopInfo = ShopDAO::getInstance()->iselect($columns, $conditions, $params, false);
        
        $result['Text'] = str_ireplace($shopInfo['account'], $shopInfo['nick_name'], $result['Text']);
        
        if ($result !== false) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '');
        }
    }

    /**
     * @desc 获取某个买家对卖家的评价统计信息
     * @param int $userId
     * @param int $sellerId
     * @author YangLong
     * @date 2015-07-10
     * @return mixed
     */
    public function getBuyerToSellerFeedbackInfo($userId, $sellerId)
    {
        if (empty($userId) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '');
        }
        
        $columns = array(
            'ItemID',
            'CommentingUser',
            'CommentingUserScore',
            'CommentText',
            'CommentType',
            'CommentTime'
        );
        $conditions = 'CommentingUser=:CommentingUser';
        $params = array(
            ':CommentingUser' => $userId
        );
        $joinArray = array(
            array(
                ShopDAO::getInstance()->getTableName() . ' s',
                's.shop_id=f.shop_id and s.seller_id=:seller_id and s.is_delete=0',
                array(
                    ':seller_id' => $sellerId
                )
            )
        );
        $result = EbayFeedbackTransactionDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 'f');
        
        if ($result !== false) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '');
        }
    }

    /**
     * @desc 无队列回复消息
     * @param string $msgId 信息ID
     * @param string $content 信息内容
     * @param array $imgurl 图片路径数组
     * @param int $emailCopyToSender
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function replyMsg($msgId, $content, $imgurl, $emailCopyToSender)
    {
        $columns = array(
            'm.ExternalMessageID',
            'm.Sender',
            'm.SendToName',
            'm.RecipientUserID',
            'm.FolderID',
            's.token',
            's.account'
        );
        $conditions = 'm.msg_id=:msg_id';
        $params = array(
            ':msg_id' => $msgId
        );
        $joinArray = array(
            array(
                ShopDAO::getInstance()->getTableName() . ' s',
                'm.shop_id=s.shop_id'
            )
        );
        $msgInfo = MsgDAO::getInstance()->iselect($columns, $conditions, $params, false, $joinArray, 'm');
        
        $MessageMediaArray = array();
        empty($imgurl) ? $imgurl = array() : null;
        foreach ($imgurl as $value) {
            $multiPartImageData = is_file($value) ? file_get_contents($value) : null;
            if (empty($multiPartImageData)) {
                continue;
            }
            $PictureName = 'xyt' . rand(111111, 999999);
            
            $xml = MsgDownModel::model()->uploadSiteHostedPictures($msgInfo['token'], $multiPartImageData, $PictureName);
            
            $doc = phpQuery::newDocumentXML($xml);
            phpQuery::selectDocument($doc);
            
            if (pq('Ack')->html() != 'Failure') {
                $picNameOut = pq('SiteHostedPictureDetails>PictureName')->html();
                empty($picNameOut) ? $picNameOut = '' : null;
                $MessageMediaArray[] = array(
                    'MediaName' => $picNameOut,
                    'MediaURL' => pq('SiteHostedPictureDetails>FullURL')->html()
                );
            }
            unset($doc);
        }
        
        $RecipientID = '';
        if ($msgInfo['FolderID'] != 1) {
            $RecipientID = $msgInfo['Sender'];
        }
        $RecipientIDArray = array(
            $RecipientID
        );
        $MessageID = rand(1000000000, 9999999999);
        
        $xml = MsgDownModel::model()->addMemberMessageRTQ($msgInfo['token'], $content, $msgInfo['ExternalMessageID'], $RecipientIDArray, '', 
            $MessageMediaArray, 0, $MessageID, $emailCopyToSender);
        
        if (stripos($xml, '<CorrelationID>' . $MessageID . '</CorrelationID>') && stripos($xml, '<Ack>Success</Ack>')) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '', '');
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', $xml);
        }
    }

    /**
     * @desc 添加新标签
     * @param string $labeltitle 标签标题
     * @param string $labelcolor 标签颜色
     * @param int $sellerId
     * @author YangLong
     * @date 2015-08-26
     * @return mixed
     */
    public function addLabel($labeltitle, $labelcolor, $sellerId)
    {
        $labeltitle = substr(imsTool::safe_replace($labeltitle), 0, 12);
        
        $conditions = array(
            'label_title' => $labeltitle,
            'seller_id' => $sellerId
        );
        $result = MsgLabelDAO::getInstance()->isExists($conditions);
        
        if ($result) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING, 'exist');
        }
        
        $columns = array(
            'seller_id' => $sellerId,
            'label_title' => htmlspecialchars(strip_tags($labeltitle)),
            'label_color' => $labelcolor,
            'create_time' => time()
        );
        $result = MsgLabelDAO::getInstance()->iinsert($columns);
        if ($result !== false) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
    }

    /**
     * @desc 获取标签列表
     * @param int $sellerId
     * @author YangLong
     * @date 2015-08-26
     * @return mixed
     */
    public function getLabelList($sellerId)
    {
        // 账号和站点切换逻辑
        $siteId = isset(Yii::app()->session['switchInfo']['siteId']) ? Yii::app()->session['switchInfo']['siteId'] : - 1;
        $accountId = isset(Yii::app()->session['switchInfo']['accountId']) ? Yii::app()->session['switchInfo']['accountId'] : 0;
        
        $userConfig = UserModel::model()->getUserConfigs();
        $shopIds = implode(',', $userConfig['shops']);
        
        $columns = array(
            'msg_label_id',
            'label_title',
            'label_color',
            'msg_count',
            'create_time'
        );
        $conditions = 'seller_id=:seller_id';
        $params = array(
            ':seller_id' => $sellerId
        );
        $result = MsgLabelDAO::getInstance()->iselect($columns, $conditions, $params);
        if ($result !== false) {
            foreach ($result as $key => $value) {
                if (empty($shopIds)) {
                    $result[$key]['msg_count'] = 0;
                    continue;
                }
                
                $text = "
                SELECT count(*) FROM `msg` `m`
                    JOIN `shop` `s` ON m.shop_id=s.shop_id
                    LEFT JOIN `msg_text_resolve` `tr` ON tr.msg_id=m.msg_id
                    RIGHT JOIN `msg_label_ref` `mlr` ON mlr.msg_id=m.msg_id
                    WHERE mlr.msg_label_id=:param and m.is_delete = '0'
                        and m.shop_id in ({$shopIds})";
                
                $params = array(
                    ':param' => $value['msg_label_id']
                );
                
                if (is_numeric($siteId) && $siteId > - 1) {
                    $text .= ' and s.site_id=:site_id';
                    $params[':site_id'] = $siteId;
                }
                
                if (is_numeric($accountId) && $accountId > 0) {
                    $text .= " and m.shop_id={$accountId}";
                }
                
                $result[$key]['msg_count'] = MsgDAO::getInstance()->setTextQuery($text, $params, 'queryScalar');
            }
            
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
    }

    /**
     * @desc 获取标签列表
     * @param int $sellerId
     * @author YangLong
     * @date 2015-11-09
     * @return mixed
     */
    public function getAutoLabelList($sellerId)
    {
        $userConfig = UserModel::model()->getUserConfigs();
        $shopIds = implode(',', $userConfig['shops']);
        
        $columns = array(
            'mal.msg_auto_label_id',
            'mal.auto_label_name'
        );
        $conditions = 's.seller_id=:seller_id';
        $params = array(
            ':seller_id' => $sellerId
        );
        $joinArray = array(
            array(
                MsgAutoLabelRefDAO::getInstance()->getTableName() . ' malr',
                'malr.msg_auto_label_id=mal.msg_auto_label_id'
            ),
            array(
                MsgDAO::getInstance()->getTableName() . ' m',
                "m.msg_id=malr.msg_id and m.is_delete='" . boolConvert::toStr01(false) . "'"
            ),
            array(
                ShopDAO::getInstance()->getTableName() . ' s',
                's.shop_id=m.shop_id and s.is_delete=0'
            )
        );
        $result = MsgAutoLabelDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 'mal', '', 0, null, 
            'DISTINCT SQL_SMALL_RESULT');
        if ($result !== false) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
    }

    /**
     * @desc 编辑标签
     * @param string $labeltitle
     * @param string $labelcolor
     * @param int $labelid
     * @param int $sellerId
     * @author YangLong
     * @date 2015-08-26
     * @return mixed
     */
    public function editLabel($labeltitle, $labelcolor, $labelid, $sellerId)
    {
        $labeltitle = substr(imsTool::safe_replace($labeltitle), 0, 18);
        
        $conditions = array(
            'label_title' => $labeltitle,
            'seller_id' => $sellerId
        );
        $result = MsgLabelDAO::getInstance()->isExists($conditions);
        
        if ($result) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING, 'exist');
        }
        
        $columns = array(
            'label_title' => $labeltitle,
            'label_color' => $labelcolor
        );
        foreach ($columns as $key => $value) {
            if (empty($value)) {
                unset($columns[$key]);
            }
        }
        $conditions = 'msg_label_id=:msg_label_id and seller_id=:seller_id';
        $params = array(
            ':msg_label_id' => $labelid,
            ':seller_id' => $sellerId
        );
        $result = MsgLabelDAO::getInstance()->iupdate($columns, $conditions, $params);
        if ($result !== false) {
            $columns = array(
                'msg_label_id',
                'label_title',
                'label_color',
                'msg_count'
            );
            $conditions = 'msg_label_id=:msg_label_id and seller_id=:seller_id';
            $params = array(
                ':msg_label_id' => $labelid,
                ':seller_id' => $sellerId
            );
            $result = MsgLabelDAO::getInstance()->iselect($columns, $conditions, $params, false);
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
    }

    /**
     * @desc 删除标签
     * @param int $labelid 标签ID
     * @param int $sellerId
     * @author YangLong
     * @date 2015-08-26
     * @return mixed
     */
    public function delLabel($labelid, $sellerId)
    {
        $conditions = 'msg_label_id=:msg_label_id and seller_id=:seller_id';
        $params = array(
            ':msg_label_id' => $labelid,
            ':seller_id' => $sellerId
        );
        $result = MsgLabelDAO::getInstance()->idelete($conditions, $params);
        if ($result !== false) {
            $conditions = 'msg_label_id=:msg_label_id';
            $params = array(
                ':msg_label_id' => $labelid
            );
            $result = MsgLabelRefDAO::getInstance()->idelete($conditions, $params);
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
    }

    /**
     * @desc 删除标签
     * @param int $labelid
     * @param int $msgid
     * @param int $sellerId
     * @author YangLong
     * @date 2015-08-26
     * @return mixed
     */
    public function setMsgLabel($labelid, $msgid, $sellerId)
    {
        $columns = array(
            'msg_label_id'
        );
        $conditions = 'seller_id=:seller_id and msg_label_id=:msg_label_id';
        $params = array(
            ':seller_id' => $sellerId,
            ':msg_label_id' => $labelid
        );
        $result = MsgLabelDAO::getInstance()->iselect($columns, $conditions, $params, false);
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
        
        $columns = array(
            'msg_label_id' => $labelid,
            'msg_id' => $msgid
        );
        $conditions = 'msg_label_id=:msg_label_id and msg_id=:msg_id';
        $params = array(
            ':msg_label_id' => $labelid,
            ':msg_id' => $msgid
        );
        
        $result = MsgLabelRefDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
        
        $field = 'msg_count';
        $conditions = 'msg_label_id=:msg_label_id';
        $params = array(
            ':msg_label_id' => $labelid
        );
        MsgLabelDAO::getInstance()->increase($field, $conditions, $params);
        
        if ($result !== false) {
            $columns = array(
                'msg_label_id',
                'label_title',
                'label_color'
            );
            $conditions = 'msg_label_id=:msg_label_id';
            $params = array(
                ':msg_label_id' => $labelid
            );
            $result = MsgLabelDAO::getInstance()->iselect($columns, $conditions, $params, false);
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
    }

    /**
     * @desc 取消消息标签
     * @param int $labelid
     * @param int $msgid
     * @param int $sellerId
     * @author YangLong
     * @date 2015-08-27
     * @return mixed
     */
    public function removeMsgLabel($labelid, $msgid, $sellerId)
    {
        $columns = array(
            'msg_label_id'
        );
        $conditions = 'seller_id=:seller_id and msg_label_id=:msg_label_id';
        $params = array(
            ':seller_id' => $sellerId,
            ':msg_label_id' => $labelid
        );
        $result = MsgLabelDAO::getInstance()->iselect($columns, $conditions, $params, false);
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
        
        $conditions = 'msg_label_id=:msg_label_id and msg_id=:msg_id';
        $params = array(
            ':msg_label_id' => $labelid,
            ':msg_id' => $msgid
        );
        $result = MsgLabelRefDAO::getInstance()->idelete($conditions, $params);
        
        $field = 'msg_count';
        $conditions = 'msg_label_id=:msg_label_id and msg_count>0';
        $params = array(
            ':msg_label_id' => $labelid
        );
        MsgLabelDAO::getInstance()->decrease($field, $conditions, $params);
        
        if ($result !== false) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
    }

    /**
     * @desc 获取消息的相关Case信息
     * @param int $msgid
     * @param int $sellerId
     * @author YangLong
     * @date 2015-08-31
     * @return mixed
     */
    public function getMsgCaseInfo($msgid, $sellerId)
    {
        $columns = array(
            'm.ItemID i1',
            'm.Sender',
            'm.SendToName',
            'm.FolderID',
            'mtr.ItemId i2',
            'mtr.OrderId'
        );
        $conditions = 'm.msg_id=:msg_id';
        $params = array(
            ':msg_id' => $msgid
        );
        $joinArray = array(
            array(
                MsgTextResolveDAO::getInstance()->getTableName() . ' mtr',
                'm.msg_id=mtr.msg_id'
            ),
            array(
                ShopDAO::getInstance()->getTableName() . ' s',
                'm.shop_id=s.shop_id and s.seller_id=:seller_id and s.is_delete=0',
                array(
                    ':seller_id' => $sellerId
                )
            )
        );
        $msginfo = MsgDAO::getInstance()->iselect($columns, $conditions, $params, false, $joinArray, 'm');
        
        $itemId = empty($msginfo['i1']) ? $msginfo['i2'] : $msginfo['i1'];
        $userId = $msginfo['FolderID'] == 1 ? $msginfo['SendToName'] : $msginfo['Sender'];
        
        // Case
        $columns = array(
            'c.case_id',
            'c.caseId_type'
        );
        $conditions = 'c.i_itemId=:i_itemId and otherParty_userId=:otherParty_userId';
        $params = array(
            ':i_itemId' => $itemId,
            ':otherParty_userId' => $userId
        );
        $joinArray = array(
            array(
                ShopDAO::getInstance()->getTableName() . ' s',
                'c.shop_id=s.shop_id and s.seller_id=:seller_id and s.is_delete=0',
                array(
                    ':seller_id' => $sellerId
                )
            )
        );
        $result['cases'] = CaseDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 'c');
        
        // Return
        $columns = array(
            'r.return_request_id',
            'r.return_type'
        );
        $conditions = 'rd.D_iD_itemId=:D_iD_itemId';
        $params = array(
            ':D_iD_itemId' => $itemId
        );
        $joinArray = array(
            array(
                ShopDAO::getInstance()->getTableName() . ' s',
                'r.shop_id=s.shop_id and s.seller_id=:seller_id and r.otherParty_userId=:otherParty_userId and s.is_delete=0',
                array(
                    ':seller_id' => $sellerId,
                    ':otherParty_userId' => $userId
                )
            ),
            array(
                ReturnDetailDAO::getInstance()->getTableName() . ' rd',
                'rd.return_id=r.return_request_id'
            )
        );
        $result['returns'] = ReturnDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 'r');
        
        // dispute
        $columns = array(
            'd.disputes_id',
            'd.DisputeReason'
        );
        if (empty($msginfo['OrderId'])) {
            $conditions = 'd.i_ItemID=:i_ItemID and d.BuyerUserID=:BuyerUserID';
            $params = array(
                ':i_ItemID' => $itemId,
                ':BuyerUserID' => $userId
            );
        } else {
            $conditions = 'd.OrderLineItemID=:OrderLineItemID';
            $params = array(
                ':OrderLineItemID' => $msginfo['OrderId']
            );
        }
        $joinArray = array(
            array(
                ShopDAO::getInstance()->getTableName() . ' s',
                'd.shop_id=s.shop_id and s.seller_id=:seller_id and s.is_delete=0',
                array(
                    ':seller_id' => $sellerId
                )
            )
        );
        $result['disputes'] = DisputesDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 'd');
        
        if ($result !== false) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
    }

    /**
     * @desc 获取用户管理的msg列表
     * @param string $buyerId
     * @param int $page
     * @param int $pageSize
     * @author liaojianwen
     * @date 2015-09-25
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function getMsgListByClientId($buyerId, $page, $pageSize)
    {
        if (empty($buyerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'params error');
        }
        
        $columns = array(
            'shop_id'
        );
        $conditions = 'seller_id=:seller_id and is_delete=' . boolConvert::toInt01(false) . ' and status=1';
        $params = array(
            ':seller_id' => Yii::app()->session['userInfo']['seller_id']
        );
        
        $userConfig = UserModel::model()->getUserConfigs();
        $shopIds = implode(',', $userConfig['shops']);
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            if (empty($shopIds)) {
                $shopIds = 0;
            }
            $conditions .= " and shop_id in ({$shopIds}) and is_delete=0";
        }
        
        // 账号和站点切换逻辑
        $siteId = isset(Yii::app()->session['switchInfo']['siteId']) ? Yii::app()->session['switchInfo']['siteId'] : - 1;
        $accountId = isset(Yii::app()->session['switchInfo']['accountId']) ? Yii::app()->session['switchInfo']['accountId'] : 0;
        if (is_numeric($siteId) && $siteId > - 1) {
            $conditions .= ' and site_id=:site_id';
            $params[':site_id'] = $siteId;
        }
        
        $shopArr = ShopDAO::getInstance()->iselect($columns, $conditions, $params);
        
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
        
        $result = MsgDealDAO::getInstance()->getMsgListByClientId($buyerId, $shopId, $page, $pageSize);
        if ($result !== false) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
    }

    /**
     * @desc 设置某条Msg为已处理
     * @param int $msgid
     * @param int $handled handled 的值
     * @author YangLong
     * @date 2015-10-16
     * @return mixed
     */
    public function setMsgHandled($msgid, $handled = true)
    {
        if (empty($msgid)) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING);
        }
        
        $columns = array(
            'handled' => boolConvert::toStr01($handled)
        );
        $conditions = 'msg_id=:msg_id';
        $params = array(
            ':msg_id' => $msgid
        );
        $result = MsgDAO::getInstance()->iupdate($columns, $conditions, $params);
        if ($result !== false) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
    }
}
