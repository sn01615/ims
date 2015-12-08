<?php

/**
 * @desc return处理上传类
 * @author liaojianwen
 * @date 2015-07-01
 */
class ReturnUploadModel extends BaseModel
{
    
    /**
     * @desc 覆盖父方法,返回此对象的(单)实例
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-07-01
     * @return ReturnUploadModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * @desc return 处理
     * @author liaojianwen
     * @date 2015-07-01
     */
     public function executeReturnUpload()
     {
        DaemonLockTool::lock(__METHOD__);
        
        $startTime = time();
        label1:
        
        $Queues = ReturnUploadQueueDAO::getInstance()->getReturnUploadQueueData(EnumOther::RETURN_UPLOAD_PICK_SIZE);
        if ($Queues !== false && is_array($Queues)) {
            foreach ($Queues as $key => $Queue) {
                switch ($Queue['upload_type']) {
                    case 'APPROVE_REQUEST':
                        
                        // accept request
                        $result = $this->approveRequest($Queue);
                        break;
                    case 'PROVIDE_RMA':
                        
                        // provide RMA
                        $result = $this->provideRMA($Queue);
                        break;
                    case 'issueReturnRefund':
                        
                        // issue refund
                        $result = $this->issueReturnRefund($Queue);
                        break;
                    case 'issueReturnPartRefund':
                        
                        // offer partial Refund
                        $result = $this->issueReturnPartRefund($Queue);
                        break;
                    case 'sendReturnMsg':
                        
                        // send msg
                        $result = $this->sendReturnMsg($Queue);
                        break;
                    case 'returnAskHelp':
                        
                        // ask ebay for help
                        $result = $this->askEbayforHelp($Queue);
                        break;
                    case 'declineReturns':
                        
                        // decline request
                        $result = $this->declineRequest($Queue);
                        break;
                }
                
                if (isset($result) && $result !== false) {
                    $conditions = 'return_upload_queue_id=:return_upload_queue_id';
                    $params = array(
                        ':return_upload_queue_id' => $result
                    );
                    ReturnUploadQueueDAO::getInstance()->idelete($conditions, $params);
                }
            }
        } else {
            unset($Queues);
            if ($startTime > (time() - 295)) {
                sleep(2);
                goto label1;
            }
            return false;
        }

     }
     
     /**
      * @desc accept return
      * @param array $Queue
      * @author liaojianwen
      * @date 2015-07-01
      */
     public function approveRequest($Queue)
     {
        $uploadData = unserialize($Queue['upload_data']);
        $returnId_id = $uploadData['returnId_id'];
        $token = $Queue['token'];
        $result = ReturnDownModel::model()->acceptReturn($returnId_id, $token);
        $res = json_decode($result, true);
        if ($res['ackValue'] === 'SUCCESS') {
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Success',
                'Queue' => $Queue,
                'uploadData'=>$uploadData,
                'json' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($res);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Request 接受成功通知 [Success]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return $Queue['return_upload_queue_id'];
        } else {
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Err',
                'Queue' => $Queue,
                'uploadData'=>$uploadData,
                'json' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($res);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Request 接受失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return false;
        }
     
     }
     
     /**
      * @desc 确认退货地址,提供RMA
      * @param  array $Queue
      * @author liaojianwen
      * @date 2015-07-01
      */
     public function provideRMA($Queue)
     {
        $uploadData = unserialize($Queue['upload_data']);
        $returnId_id = $uploadData['returnId_id'];
        $RMA = $uploadData['RMA'];
        $returnAddr = $uploadData['returnAddr'];
        $token = $Queue['token'];
        $result = ReturnDownModel::model()->provideRMA($returnId_id, $RMA, $returnAddr, $token);
        $res = json_decode($result, true);
        if ($res['ackValue'] === 'SUCCESS') {
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Success',
                'Queue' => $Queue,
                'uploadData'=>$uploadData,
                'json' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($res);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Request 添加RMA成功通知 [Success]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return $Queue['return_upload_queue_id'];
        } else {
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Err',
                'Queue' => $Queue,
                'uploadData'=>$uploadData,
                'json' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($res);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Request 添加RMA失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return false;
        }
                 
     }
     
     /**
      * @desc return 退款
      * @param unknown_type $Queue
      * @author liaojianwen
      * @date 2015-07-01
      */
     public function issueReturnRefund($Queue)
     {
         $uploadData = unserialize($Queue['upload_data']);
         $returnId_id = $uploadData['returnId_id'];
         $itemizedRefundDetail = $uploadData['itemRefundDetail'];
         $token = $Queue['token'];
       
         $result = ReturnDownModel::model()->issueReturnRefund($returnId_id,$itemizedRefundDetail,$token);
         $res = json_decode($result,true);
        if ($res['ackValue'] === 'SUCCESS') {
            CRedisHelper::getInstance()->set(md5('returnRefund' . $Queue['return_upload_queue_id']), 'success', 3600);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Success',
                'Queue' => $Queue,
                'uploadData'=>$uploadData,
                'json' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($res);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Request 退款成功通知 [Success]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return $Queue['return_upload_queue_id'];
        } else {
            CRedisHelper::getInstance()->set(md5('returnRefund' . $Queue['return_upload_queue_id']), 'err', 3600);
            CRedisHelper::getInstance()->set(md5('returnRefund' . $Queue['return_upload_queue_id'] . 'result'), serialize($res), 3600);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Err',
                'Queue' => $Queue,
                'uploadData' => $uploadData,
                'json' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($res);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Request 退款失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return false;
        }
     
     }
     
     /**
      * @desc return 部分退款
      * @param $Queue
      * @author liaojianwen
      * @date 2015-07-02
      */
     public function issueReturnPartRefund($Queue)
     {
        $uploadData = unserialize($Queue['upload_data']);
        $returnId_id = $uploadData['returnId_id'];
        $amount = $uploadData['amount'];
        $currencyId = $uploadData['currencyId'];
        $comments = $uploadData['text'];
        $token = $Queue['token'];
        
        $result = ReturnDownModel::model()->issuePartialRefund($returnId_id, $amount, $currencyId, $comments, $token);
        $res = json_decode($result, true);
        if ($res['ackValue'] === 'SUCCESS') {
            CRedisHelper::getInstance()->set(md5('returnPartRefund' . $Queue['return_upload_queue_id']), 'success', 3600);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Success',
                'Queue' => $Queue,
                'uploadData'=>$uploadData,
                'json' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($res);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Request 部分退款成功通知 [Success]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return $Queue['return_upload_queue_id'];
        } else {
            CRedisHelper::getInstance()->set(md5('returnPartRefund' . $Queue['return_upload_queue_id']), 'err', 3600);
            CRedisHelper::getInstance()->set(md5('returnPartRefund' . $Queue['return_upload_queue_id'] . 'result'), serialize($res), 3600);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Err',
                'Queue' => $Queue,
                'uploadData'=>$uploadData,
                'json' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($res);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Request 部分退款失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return false;
        }
     
     }
     
     /**
      * @daes return 给客户发消息
      * @param   $Queue
      * @author liaojianwen
      * @date 2015-07-01
      */
     public function sendReturnMsg($Queue)
     {
        $uploadData = unserialize($Queue['upload_data']);
        $returnId_id = $uploadData['returnId_id'];
        $comments = $uploadData['text'];
        $token = $Queue['token'];
        
        $result = ReturnDownModel::model()->sendMessage($returnId_id, $comments, $token);
        $res = json_decode($result, true);
        if ($res['ackValue'] === 'SUCCESS') {
            CRedisHelper::getInstance()->set(md5('returnMsg' . $Queue['return_upload_queue_id']), 'success', 3600);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Success',
                'Queue' => $Queue,
                'uploadData'=>$uploadData,
                'json' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($res);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Request 发送消息成功通知 [Success]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return $Queue['return_upload_queue_id'];
        } else {
            CRedisHelper::getInstance()->set(md5('returnMsg' . $Queue['return_upload_queue_id']), 'err', 3600);
            CRedisHelper::getInstance()->set(md5('returnMsg' . $Queue['return_upload_queue_id'] . 'result'), serialize($res), 3600);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Err',
                'Queue' => $Queue,
                'uploadData'=>$uploadData,
                'json' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($res);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Request 发送消息失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return false;
        }
    }
     
     /**
      * @desc return 申请ebay 介入
      * @param  $Queue
      * @author liaojianwen
      * @date 2015-07-01
      */
     
     public function askEbayforHelp($Queue)
     {
        $uploadData = unserialize($Queue['upload_data']);
        $returnId_id = $uploadData['returnId_id'];
        $comments = $uploadData['text'];
        $reason = $uploadData['reason'];
        $token = $Queue['token'];
        
        $result = ReturnDownModel::model()->askEbayHelp($returnId_id, $comments, $reason, $token);
        $res = json_decode($result, true);
        if ($res['ackValue'] === 'SUCCESS') {
            CRedisHelper::getInstance()->set(md5('returnEbayHelp' . $Queue['return_upload_queue_id']), 'success', 3600);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Success',
                'Queue' => $Queue,
                'uploadData'=>$uploadData,
                'json' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($res);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Request 申请ebay介入成功通知 [Success]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return $Queue['return_upload_queue_id'];
        } else {
            CRedisHelper::getInstance()->set(md5('returnEbayHelp' . $Queue['return_upload_queue_id']), 'err', 3600);
            CRedisHelper::getInstance()->set(md5('returnEbayHelp' . $Queue['return_upload_queue_id'] . 'result'), serialize($res), 3600);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Err',
                'Queue' => $Queue,
                'uploadData'=>$uploadData,
                'json' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($res);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Request 申请ebay介入失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return false;
        }
     
     }
     
     /**
      * @desc decline request
      * @author liaojianwen
      * @date 2015-08-10
      * @param string $Queue
      * @return unknown|boolean
      */
     public function declineRequest($Queue)
     {
        $uploadData = unserialize($Queue['upload_data']);
        $returnId_id = $uploadData['returnId_id'];
        $comments = $uploadData['text'];
        $token = $Queue['token'];
        
        $result = ReturnDownModel::model()->declineRequest($returnId_id, $comments, $token);
        $res = json_decode($result, true);
        if ($res['ackValue'] === 'SUCCESS') {
            CRedisHelper::getInstance()->set(md5('returnDecline' . $Queue['return_upload_queue_id']), 'success', 3600);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Success',
                'Queue' => $Queue,
                'uploadData'=>$uploadData,
                'json' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($res);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Request 拒绝成功通知 [Success]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return $Queue['return_upload_queue_id'];
        } else {
            CRedisHelper::getInstance()->set(md5('returnDecline' . $Queue['return_upload_queue_id']), 'err', 3600);
            CRedisHelper::getInstance()->set(md5('returnDecline' . $Queue['return_upload_queue_id'] . 'result'), serialize($res), 3600);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Err',
                'Queue' => $Queue,
                'uploadData'=>$uploadData,
                'json' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($res);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Request 拒绝失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return false;
        }
         
     }
     
     /**
      * @desc 获取return部分退款是否成功
      * @author liaojianwen
      * @date 2015-08-10
      * @param string $uploadId
      * @param string $actionType return操作项
      * @return
      */
     public function getReturnReply($uploadId,$actionType)
     {
         if (empty($uploadId)) {
             $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'uploadId(队列表ID)不能为空！');
         }
         
         label1:
         $startTime = time();
         $result = CRedisHelper::getInstance()->get(md5($actionType . $uploadId));
         if ($result === false) {
         }
         
         iMongo::getInstance()->setCollection('____Return____')->insert(array(
         'result' => $result,
         'time' => time()
         ));
         
         if ($result == 'success' || $result =='err') {
             CRedisHelper::getInstance()->set(md5($actionType . $uploadId), '', 1);
             if ($result == 'success') {
                 return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
             } else {
                 $error = unserialize(CRedisHelper::getInstance()->get(md5($actionType . $uploadId . 'result')));
                 return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result, $error);
             }
         } else {
             if ($startTime > (time() - 180)) {
                 sleep(1);
                 goto label1;
             }
             return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result, $error);
         }
         
     }
}