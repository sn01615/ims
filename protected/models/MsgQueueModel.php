<?php

/**
 * @desc 信息定时任务处理类
 * @author heguangquan
 * @date 2015-01-28
 */
class MsgQueueModel extends BaseModel
{

    const VESRION = '905';
    
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
     * @desc 覆盖父方法返回MsgQueueModel对象
     * @author heguangquan
     * @date 2014-10-22
     * @return MsgQueueModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @desc 获取消息文件夹列表信息
     * @param string $token
     * @param string $startime
     * @param string $endtime
     * @return boolean
     * @author YangLong
     * @date 2015-02-16
     */
    private function getMessagesSummary($token, $startime = '', $endtime = '', $site_id = 0)
    {
        if (empty($token)) {
            return false;
        } else {
            $this->setRequestToken($token, $site_id);
            $getmymessagesrequest = new GetMyMessagesRequestType();
            if (! empty($startime)) {
                $getmymessagesrequest->setEndTime($startime);
            }
            if (! empty($endtime)) {
                $getmymessagesrequest->setStartTime($endtime);
            }
            $getmymessagesrequest->addDetailLevel("ReturnSummary");
            $getmymessagesrequest->setVersion(self::VESRION);
            $getmymessagesrequest->setWarningLevel("High");
            $response = $this->proxy->GetMyMessages($getmymessagesrequest);
            if (! is_null($response->Summary) && $response->Ack == 'Success') {
                if (is_array($response->Summary->FolderSummary)) {
                    $result = array();
                    foreach ($response->Summary->FolderSummary as $key => $Folder) {
                        $result[] = array(
                            'FolderID' => $Folder->FolderID,
                            'FolderName' => $Folder->FolderName,
                            'NewAlertCount' => $Folder->NewAlertCount,
                            'NewMessageCount' => $Folder->NewMessageCount,
                            'TotalAlertCount' => $Folder->TotalAlertCount,
                            'TotalMessageCount' => $Folder->TotalMessageCount,
                            'NewHighPriorityCount' => $Folder->NewHighPriorityCount,
                            'TotalHighPriorityCount' => $Folder->TotalHighPriorityCount
                        );
                    }
                    return $result;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    private function get_check_offset($shop_id)
    {
        $timer = array(
            24 => 168,
            10 => 48,
            5 => 12,
            2 => 5
        );
        foreach ($timer as $key => $value) {
            $key = md5(__METHOD__ . "-{$shop_id}-" . $key);
            if (iMemcache::getInstance()->get($key) == false) {
                iMemcache::getInstance()->set($key, true, $key * 3600);
                return $value * 3600;
            }
        }
        return 0;
    }

    /**
     * @desc 生成常规下载队列
     * @author YangLong
     * @date 2015-01-28
     * @return mixed
     */
    public function generateMsgDownQueue()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $startTime = time();
        
        label1:
        
        $shops = MsgDownDAO::getInstance()->getEbShop();
        foreach ($shops as $key => &$shop) {
            $folders = $this->uploadFoldersInfo($shop);
            $priority = self::P_DEFAULTPRIORITY; // 默认优先级
            $newer = false;
            if (empty($shop['msg_down_time'])) {
                $newer = true;
                $priority = self::P_NEWPRIORITY; // 新人优先级
                $shop['msg_down_time'] = time() - 3600 * 24 * self::D_FIRSTDOWNLOADSIZE;
            }
            $start = $shop['msg_down_time'] - EnumOther::OVARLAP_TIME - $this->get_check_offset($shop['shop_id']);
            $end = time() + EnumOther::OVARLAP_TIME;
            MsgDownDAO::getInstance()->makeQueue($shop, $folders, $priority, $start, $end, true);
            if ($newer) {
                // 新人历史任务
                $priority = self::P_HISTORYPRIORITY; // 历史任务基准优先级
                $tSize = 3 * 24 * 3600;
                for ($i = 1; $i < 30; $i ++) {
                    $end = $start - ($i - 1) * $tSize + EnumOther::OVARLAP_TIME;
                    MsgDownDAO::getInstance()->makeQueue($shop, $folders, $priority - $i, $end - $tSize, $end, true);
                }
            }
            
            // 校验任务
            if ((time() - $shop['msg_check_down_time']) > EnumOther::MSG_CHECK_TIME) {
                if (! $newer) {
                    $columns = array(
                        'msg_check_down_time' => time()
                    );
                    $conditions = 'shop_id=:shop_id';
                    $params = array(
                        ':shop_id' => $shop['shop_id']
                    );
                    ShopDAO::getInstance()->iupdate($columns, $conditions, $params);
                    MsgDownDAO::getInstance()->makeQueue($shop, $folders, 11, time() - EnumOther::MSG_CHECK_SIZE, time());
                    
                    iMongo::getInstance()->setCollection('makeMsgCheckQ')->insert(
                        array(
                            'shop_id' => $shop['shop_id'],
                            'time' => time()
                        ));
                }
            }
        }
        unset($shop);
        
        // if ($startTime > (time() - 600)) {
        // sleep(30);
        // goto label1;
        // }
    }

    /**
     * @desc 更新店铺文件夹信息
     * @param array $shop 店铺信息数据
     * @author YangLong
     * @date 2015-03-20
     * @return Ambigous <Ambigous, multitype:, mixed>
     */
    private function uploadFoldersInfo($shop)
    {
        // 获取文件夹信息，判断是否为空 ，为空调用接口下载并保存
        $folders = MsgDownDAO::getInstance()->getShopFolders($shop['shop_id']);
        if (empty($folders)) {
            // 立即更新文件夹信息 一次性
            $foldersinfo = $this->getMessagesSummary($shop['token'], null, null, $shop['site_id']);
            if ($foldersinfo !== false) {
                // 保存获取到的文件夹信息到数据库
                $Folderinsertstatus = MsgDownDAO::getInstance()->setShopFolders($foldersinfo, $shop['shop_id']);
                if ($Folderinsertstatus !== false) {
                    // 插入成功
                    $folders = MsgDownDAO::getInstance()->getShopFolders($shop['shop_id']);
                } else {
                    // 插入失败，低概率
                }
            } else {
                // 获取文件信息失败
            }
        }
        foreach ($folders as $key => $folder) {
            if ($folders[$key]['FolderID'] <= 200) {
                unset($folders[$key]);
            }
        }
        array_unshift($folders, array(
            'FolderID' => 0
        ), array(
            'FolderID' => 1
        ), array(
            'FolderID' => 6
        ));
        return $folders;
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
     * @desc 获取信息解析的数据
     * @param int $taskNumber 任务数量
     * @author heguangquan
     * @return array Ack：获取数据状态;body：内容
     * @date 2015-01-29
     */
    public function getParseMsgDown($taskNumber)
    {
        if (empty($taskNumber)) {
            return array(
                'Ack' => 'Failure',
                'body' => ''
            );
        }
        $objDowDAO = MsgDownDAO::getInstance();
        $paramArr['status'] = EnumOther::MSG_DOWN_NOTSTATUS;
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
            ), array(
                'status' => EnumOther::MSG_DOWN_DEALSTATUS,
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
     * @author heguangquan
     * @date 2015-02-02
     * @return array Ack:删除信息状态
     */
    public function deteleMsgDown($strId)
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
     * @desc 生成回复信息队列
     * @param int $msgId 信息表ID
     * @param array $imgUrl
     * @param string $copy
     * @param string $content 信息内容
     * @author heguanguan,lvjianfei,YangLong
     * @date 2015-03-04
     * @return array PK.etc
     */
    public function replyMsg($msgId, $imgUrl, $copy, $content)
    {
        if (empty($msgId) || empty($content)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '内容不能为空');
        }
        
        $replyInfo = MsgReplyDAO::getInstance()->getShopMsgInfo($msgId);
        
        if ($replyInfo !== false) {
            if ($replyInfo['ResponseEnabled'] == '0') {
                return $this->handleApiFormat(EnumOther::ACK_WARNING, '', '这条消息不能回复');
            }
            unset($replyInfo['ResponseEnabled']);
            
            $replyInfo['msg_id'] = $msgId;
            $replyInfo['content'] = $content;
            $replyInfo['copy'] = boolConvert::toInt01($copy);
            $replyInfo['imgUrl'] = json_encode($imgUrl);
            
            $queuePk = EbayMsgReplyQueueDAO::getInstance()->iinsert($replyInfo, true);
            
            // push memcache queue
            $key = 'msg_reply_queue_mem';
            $replyInfo['down_queue_id'] = $queuePk;
            iMemQueue::getInstance()->push($key, $replyInfo);
            
            if ($queuePk !== false) {
                
                // 写日志
                $columns = array(
                    'msg_id' => $replyInfo['msg_id'],
                    'msg_content' => $replyInfo['content'],
                    'content_md5' => md5(imsTool::msgClear($replyInfo['content'])),
                    'copy_to_sender' => boolConvert::toInt01($replyInfo['copy']),
                    'image_urls' => $replyInfo['imgUrl'],
                    'action_username' => Yii::app()->session['userInfo']['username'],
                    'create_time' => time()
                );
                MsgReplyLogDAO::getInstance()->iinsert($columns);
                
                // 标记为回复
                $columns = array(
                    'Replied' => boolConvert::toStr01(true)
                );
                $conditions = 'msg_id=:msg_id';
                $params = array(
                    ':msg_id' => $msgId
                );
                MsgDAO::getInstance()->iupdate($columns, $conditions, $params);
                
                $result = array();
                $result['Pk'] = $queuePk;
                
                return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
            } else {
                
                // 发送邮件通知
                ob_start();
                echo "时间：\n";
                echo date('Y-m-d H:i:s');
                echo "\n";
                echo time();
                echo "\n";
                echo "返回结果：\n";
                var_export($queuePk);
                echo "\n\n回复内容：\n";
                var_export($replyInfo);
                $text = ob_get_clean();
                $subject = "消息回复失败通知：推送队列失败\n";
                $to = Yii::app()->params['logmails'];
                SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
                
                return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '提交失败');
            }
        } else {
            
            // 发送邮件通知
            ob_start();
            echo "时间：\n";
            echo date('Y-m-d H:i:s');
            echo "\n";
            echo time();
            echo "\n";
            echo "msgId：\n";
            var_export($msgId);
            echo "\n\n回复内容：\n";
            var_export($replyInfo);
            $text = ob_get_clean();
            $subject = "消息回复失败通知：【严重错误】getShopMsgInfo 失败\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '提交失败，严重错误，getShopMsgInfo失败');
        }
    }

    /**
     * @desc 获取消息回复队列里的消息是否已回复成功(阻塞式)
     * @param int $qpk
     * @author YangLong
     * @date 2015-07-27
     * @return mixed
     */
    public function getMsgReplyStatus($qpk)
    {
        if (empty($qpk)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'qpk(队列表ID)不能为空！');
        }
        
        label1:
        $replyStatus = iMemcache::getInstance()->get(md5("msg_reply_status_{$qpk}"));
        if ($replyStatus !== false) {
            if ($replyStatus === 'Success') {
                return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $replyStatus);
            } else {
                $error = iMemcache::getInstance()->get(md5("msg_reply_status_{$qpk}_error"));
                return $this->handleApiFormat(EnumOther::ACK_FAILURE, $replyStatus, $error);
            }
        } else {
            usleep(200000);
            goto label1;
        }
    }
}
