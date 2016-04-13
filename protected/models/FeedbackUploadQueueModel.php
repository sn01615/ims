<?php

/**
 * @desc feedback处理上传类
 * @author liaojianwen
 * @date 2015-08-28
 */
class FeedbackUploadQueueModel extends BaseModel
{

    /**
     * @desc 覆盖父方法,返回此对象的(单)实例
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-08-28
     * @return FeedbackUploadQueueModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @desc feedback 处理
     * @author liaojianwen
     * @date 2015-08-28
     */
    public function executeFeedbackUpload()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $startTime = time();
        label1:
        
        $Queues = FeedbackUploadQueueDAO::getInstance()->getFeedbackUploadQueueData(EnumOther::FEEDBACK_UPLOAD_PICK_SIZE);
        if ($Queues !== false && is_array($Queues)) {
            foreach ($Queues as $key => $Queue) {
                switch ($Queue['upload_type']) {
                    case 'responseFeedback':
                        
                        // response feedback
                        $result = $this->responseFeedback($Queue);
                        break;
                }
                
                if (isset($result) && $result !== false) {
                    $conditions = 'feedback_upload_queue_id=:feedback_upload_queue_id';
                    $params = array(
                        ':feedback_upload_queue_id' => $Queue['feedback_upload_queue_id']
                    );
                    FeedbackUploadQueueDAO::getInstance()->idelete($conditions, $params);
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
     * @desc 处理回复feedback
     * @param array $Queue
     * @author liaojianwen
     * @date 2015-08-28
     * @return unknown|boolean
     */
    public function responseFeedback($Queue)
    {
        $uploadData = unserialize($Queue['upload_data']);
        $FeedbackID = $uploadData['FeedbackID'];
        $CommentUser = $uploadData['CommentingUser'];
        $responseText = $uploadData['text'];
        $siteId = $uploadData['site_id'];
        $token = $Queue['token'];
        $result = FeedbackDownModel::model()->responseFeedback($token, $FeedbackID, $CommentUser, $responseText, $siteId);
        $res = json_decode($result, true);
        if ($res['ackValue'] === 'SUCCESS') {
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(
                array(
                    'type' => 'Success',
                    'Queue' => $Queue,
                    'json' => $result,
                    'time' => time()
                ));
            // 回复feedback通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n回复内容：\n";
            var_export($responseText);
            $text = ob_get_clean();
            $subject = "Feedback回复成功通知 [Success]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return $Queue['feedback_upload_queue_id'];
        } else {
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(
                array(
                    'type' => 'Err',
                    'Queue' => $Queue,
                    'json' => $result,
                    'time' => time()
                ));
            // 回复feedback通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n回复内容：\n";
            var_export($responseText);
            $text = ob_get_clean();
            $subject = "Feedback回复失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return false;
        }
    }
}