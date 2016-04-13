<?php

/**
 * @desc feedback处理类
 * @author liaojianwen
 * @date 2015-08-25
 */
class EbayFeedbackTransactionModel extends BaseModel
{

    /**
     * @desc 覆盖父方法返回EbayFeedbackTransactionModel对象
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-08-25
     * @return EbayFeedbackTransactionModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @desc 获取feedback 列表
     * @param string $type 类型
     * @param integer $page 页数
     * @param integer $pageSize 页码
     * @author liaojianwen
     * @date 2015-08-25
     */
    public function getFeedbackList($type, $page, $pageSize, $cust, $status)
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
        $paramArr['param'] = $type;
        $paramArr['shop_id'] = $shopId;
        // 查数据库
        $res = EbayFeedbackTransactionDAO::getInstance()->getFeedbackList($paramArr, $page, $pageSize, $cust, $status);
        if (empty($res['list'])) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '查询不到数据');
        }
        $result = $this->handleApiFormat(EnumOther::ACK_SUCCESS, $res, '');
        return $result;
    }

    /**
     * @desc  feedback 获取订单信息
     * @param string $orderLineItemID
     * @param string $sellerId
     * @author liaojianwen
     * @date 2015-09-15
     * @return mixed
     */
    public function getFeedbackOrder($orderLineItemID, $sellerId)
    {
        if (empty($orderLineItemID) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'orderLineItemId or sellerId is invalid');
        }
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
        // 查数据库
        $res = EbayOrderTransactionDAO::getInstance()->getFeedbackOrder($orderLineItemID, $shopId);
        if (empty($res)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '查询不到数据');
        }
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $res, '');
    }

    /**
     * @desc 获取备注
     * @param string $itemId
     * @param string $clientId
     * @author liaojianwen
     * @date 2015-08-26
     */
    public function getFeedbackNote($itemId, $clientId)
    {
        if (empty($itemId) && empty($clientId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
        
        $columns = array(
            'author_name',
            'cust',
            'text',
            'create_time'
        );
        $conditions = array(
            'item_id' => $itemId,
            'cust' => $clientId
        );
        $result = ItemNoteDAO::getInstance()->findAllByAttributes($conditions, $columns, array(
            'create_time desc'
        ));
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '查询不到数据');
        }
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
    }

    /**
     * @desc 新增备注
     * @param string $itemId
     * @param string $clientId
     * @param string $text
     * @author liaojianwen
     * @date 2015-08-26
     */
    public function addFeedbackNote($itemId, $clientId, $text)
    {
        if (empty($itemId) && empty($clientId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
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
        $param['item_id'] = $itemId;
        $param['cust'] = $clientId;
        $result = $itemnote->insert($param);
        if ($result === false) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
        } else {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
        }
    }

    /**
     * @desc 生成回复feedback队列 
     * @param string $feedbackId
     * @param  string $text
     * @param string $sellerId
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function responseFeedback($feedbackId, $text, $sellerId)
    {
        FeedbackUploadQueueDAO::getInstance()->begintransaction();
        try {
            if (empty($feedbackId) && empty($text) && empty($sellerId)) {
                return $this->handleApiFormat(EnumOther::ACK_FAILURE);
            }
            $token = EbayFeedbackTransactionDAO::getInstance()->lawfulFeedbackID($feedbackId, $sellerId);
            if ($token === false) {
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'feedbackId Error.');
            } else {
                $FeedbackID = $token['FeedbackID'];
                $site_id = $token['site_id'];
                $CommentingUser = $token['CommentingUser'];
                $columns = array(
                    'upload_type' => __FUNCTION__,
                    'upload_data' => serialize(compact('FeedbackID', 'CommentingUser', 'text', 'site_id')),
                    'token' => $token['token'],
                    'create_time' => time()
                );
                $result = FeedbackUploadQueueDAO::getInstance()->iinsert($columns, true);
                if ($result === false) {
                    FeedbackUploadQueueDAO::getInstance()->rollback();
                    return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
                } else {
                    FeedbackUploadQueueDAO::getInstance()->commit();
                    // update the selelcttion params
                    $conditions = "ebay_feedback_transaction_id =:feedbackid";
                    $params = array(
                        ':feedbackid' => $feedbackId
                    );
                    $columns = array(
                        'isResponse' => 1,
                        'ResponseText' => $text
                    );
                    EbayFeedbackTransactionDAO::getInstance()->iupdate($columns, $conditions, $params);
                    return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
                }
            }
        } catch (Exception $e) {
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(
                array(
                    'getFile' => $e->getFile(),
                    'getLine' => $e->getLine(),
                    'getMessage' => $e->getMessage(),
                    'feedbackID' => $feedbackId,
                    'responseText' => $text,
                    'time' => time()
                ));
            FeedbackUploadQueueDAO::getInstance()->rollback();
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
        }
    }

    /**
     * @desc 批量回复feedback
     * @param array $replyInfo
     * @param string $sellerId
     * @author liaojianwen
     * @date 2015-08-31
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function batchReply($replyInfo, $sellerId)
    {
        if (empty($replyInfo) && empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
        foreach ($replyInfo as $value) {
            $feedbackId = $value['feedbackId'];
            $text = $value['text'];
            $result = $this->responseFeedback($feedbackId, $text, $sellerId);
        }
        return $result;
    }

    /**
     * @desc 发消息
     * @param string $feedbackId feedback主键
     * @param string $content 发送消息内容
     * @param array $imgUrl 图片路径
     * @param boolean $sendMyMsg 
     * @param string $sellerId
     * @author liaojianwen
     * @date 2015-09-02
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function generateContactMsgQueue($feedbackId, $content, $imgUrl, $sendMyMsg, $sellerId)
    {
        if (empty($feedbackId) && empty($sellerId) && empty($content)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
        $mongo = new MongoClient('mongodb://127.0.0.1:27017', array(
            'connect' => false
        ));
        MongoQueue::$connection = $mongo;
        MongoQueue::$database = 'ImsMongoQueue';
        $token = EbayFeedbackTransactionDAO::getInstance()->lawfulFeedbackID($feedbackId, $sellerId);
        if ($token === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'feedbackId  Error.');
        } else {
            $parameters = array(
                'queue_type' => 'ContactMsgUploadQueue',
                'CommentingUser' => $token['CommentingUser'],
                'shopId' => $token['shop_id'],
                'nick_name' => $token['nick_name'],
                'itemId' => $token['ItemID'],
                'token' => $token['token'],
                'sendMyMsg' => $sendMyMsg,
                'text' => $content,
                'siteId' => $token['site_id'],
                'imgUrl' => $imgUrl,
                'create_time' => time()
            );
            
            $res = MongoQueue::push('QueueTracerModel', 'trace', $parameters, time(), false, 200);
            if (! $res['ok']) {
                return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
            } else {
                // update the selelcttion params
                $conditions = "ebay_feedback_transaction_id =:feedbackid";
                $params = array(
                    ':feedbackid' => $feedbackId
                );
                $columns = array(
                    'isSendMsg' => 1,
                    'lastSendTime' => time()
                );
                EbayFeedbackTransactionDAO::getInstance()->iupdate($columns, $conditions, $params);
                return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
            }
        }
    }

    /**
     * @desc 执行给客户发消息队列
     * @param array $parameters
     * @author liaojianwen
     * @date 2015-08-31
     */
    public function executeContactMsgUploadQueue($parameters)
    {
        $token = $parameters['token'];
        $contactUser = $parameters['CommentingUser'];
        $shop_id = $parameters['shopId'];
        $nick_name = $parameters['nick_name'];
        $itemId = $parameters['itemId'];
        $sendMyMsg = $parameters['sendMyMsg'];
        $content = $parameters['text'];
        $siteId = $parameters['siteId'];
        $imgUrl = $parameters['imgUrl'];
        $picPathArr = array();
        $runcount = 0;
        
        // 写下日志
        $columns = array(
            'shop_id' => $shop_id,
            'recipient_id' => $contactUser,
            'body' => $content,
            'create_time' => time()
        );
        MsgCreateLogDAO::getInstance()->iinsert($columns);
        
        if (! empty($imgUrl) && ! empty($token)) {
            if (is_array($imgUrl)) {
                foreach ($imgUrl as $v) {
                    $imsApiUrl = Yii::app()->params['imsApiUrl'] . $v;
                    label:
                    $result = ImsjobsModel::model()->UploadPicture($token, $imsApiUrl, 0);
                    if ($result->Ack == 'Success' || $result->Ack == 'Warning') {
                        $picPathArr[] = $result->SiteHostedPictureDetails->FullURL;
                    } else {
                        iMongo::getInstance()->setCollection('UploadPictureErr')->insert(
                            array(
                                'imsApiUrl' => $imsApiUrl,
                                'result' => $result,
                                'ErrorCode' => $result->Errors->ErrorCode,
                                'LongMessage' => $result->Errors->LongMessage,
                                'ShortMessage' => $result->Errors->ShortMessage,
                                'time' => time()
                            ));
                        if ($runcount < 3) {
                            $runcount ++;
                            goto label;
                        }
                    }
                }
            }
        }
        iMongo::getInstance()->setCollection('UploadPicture')->insert(
            array(
                'pathUrl' => $imgUrl,
                'picPathArr' => $picPathArr,
                'time' => time()
            ));
        $res = ImsjobsModel::model()->addMessagesToPartner($token, $itemId, $content, $sendMyMsg, $picPathArr, $contactUser, $siteId);
        $doc = phpQuery::newDocumentXML($res);
        phpQuery::selectDocument($doc);
        if (! isset($result)) {
            $result = '';
        }
        if (pq('Ack')->html() === 'Success') {
            iMongo::getInstance()->setCollection('addMessagesToPartner')->insert(
                array(
                    'result' => $res,
                    'parameters' => $parameters,
                    'time' => time()
                ));
            
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
        } else {
            iMongo::getInstance()->setCollection('addMessagesToPartnerErr')->insert(
                array(
                    'result' => $res,
                    'parameters' => $parameters,
                    'time' => time()
                ));
            
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($res);
            echo "\n\n回复内容：\n";
            var_export($content);
            echo "\n\n图片信息：\n";
            var_export($picPathArr);
            echo "\n\nUploadPicture result：\n";
            var_export($result);
            $text = ob_get_clean();
            $subject = "给客户发消息失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '');
        }
    }

    /**
     * @desc 保存feedback状态
     * @param string $feedbackId
     * @param boolean $msgStatus
     * @param boolean $requestStatus
     * @param string $sellerId
     * @author liaojianwen
     * @date 2015-09-06
     */
    public function saveFeedbackStatus($feedbackId, $msgStatus, $requestStatus, $requestOutDate, $feedbackOutDate, $declineChange, $sellerId)
    {
        if (empty($feedbackId) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '');
        }
        $columns = array(
            'isSendMsg' => boolConvert::toInt01($msgStatus),
            'isChange' => boolConvert::toInt01($requestStatus),
            'isRequestOutDate' => boolConvert::toInt01($requestOutDate),
            'isFeedbackOuteDate' => boolConvert::toInt01($feedbackOutDate),
            'isDeclineChange' => boolConvert::toInt01($declineChange)
        );
        $conditions = 'ebay_feedback_transaction_id=:feedbackid';
        $params = array(
            ':feedbackid' => $feedbackId
        );
        EbayFeedbackTransactionDAO::getInstance()->iupdate($columns, $conditions, $params);
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
    }

    /**
     * @desc 获取feedback条数
     * @author liaojianwen
     * @date 2015-09-06
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function getFeedbackCount()
    {
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
        $result = EbayFeedbackTransactionDAO::getInstance()->getFeedbackCount($shopId);
        if (! empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '');
        }
    }

    /**
     * @desc 批量发消息给客户
     * @param array $msgInfo
     * @param string $sellerId
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function batchSendMsg($msgInfo, $sellerId)
    {
        if (! is_array($msgInfo) || empty($msgInfo)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'the params can not be null');
        }
        foreach ($msgInfo as $info) {
            
            $feedbackId = $info['feedbackId'];
            $content = $info['text'];
            $imgUrl = isset($info['imgs']) ? $info['imgs'] : '';
            $emailCopyToSender = $info['issendme'];
            $sellerId = Yii::app()->session['userInfo']['seller_id'];
            $result = $this->generateContactMsgQueue($feedbackId, $content, $imgUrl, $emailCopyToSender, $sellerId);
        }
        return $result;
    }
}
    