<?php

/**
 * @desc 监控发送邮件之类 
 * @author YangLong
 * @date 2015-09-08
 */
class MonitorModel extends BaseModel
{
    
    /**
     * @desc 覆盖父方法,返回此对象的(单)实例
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-09-08
     * @return MonitorModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * @desc 发送统计邮件
     * @author YangLong
     * @date 2015-09-12
     */
    public function jobsTatusMail()
    {
        $text = "SELECT count(*) FROM ebay_orders_down where base64data like '%<ErrorCode>518</ErrorCode>%';";
        $result['call limit'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM ebay_orders_down where base64data like '%<ErrorCode>10007</ErrorCode>%';";
        $result['Internal error'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        
        $text = "SELECT count(*) FROM case_down;";
        $result['case_down'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM case_down_queue;";
        $result['case_down_queue'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM case_update_queue;";
        $result['case_update_queue'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM case_update_status;";
        $result['case_update_status'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM case_update_status_queue;";
        $result['case_update_status_queue'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM case_upload_queue;";
        $result['case_upload_queue'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM disputes_down;";
        $result['disputes_down'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM eb_msg_down;";
        $result['eb_msg_down'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM eb_msg_down_queue;";
        $result['eb_msg_down_queue'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM eb_msg_reply_queue;";
        $result['eb_msg_reply_queue'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM ebay_listing_down;";
        $result['ebay_listing_down'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM ebay_listing_queue;";
        $result['ebay_listing_queue'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM ebay_orders_down;";
        $result['ebay_orders_down'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM ebay_orders_down_queue;";
        $result['ebay_orders_down_queue'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM feedback_down;";
        $result['feedback_down'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM feedback_update_queue;";
        $result['feedback_update_queue'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM msg_reply_log;";
        $result['msg_reply_log'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM return_request_down;";
        $result['return_request_down'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM return_request_queue;";
        $result['return_request_queue'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM return_update_queue;";
        $result['return_update_queue'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        $text = "SELECT count(*) FROM return_upload_queue;";
        $result['return_upload_queue'] = MsgDAO::getInstance()->setTextQuery($text, array(), 'queryScalar');
        
        $to = array(
            '杨龙' => '168119405@qq.com'
        );
        ob_start();
        var_export($result);
        $out = ob_get_clean();
        SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . "队列与下载剩余统计信息", $out, $to);
    }
    
    /**
     * @desc test
     * @author YangLong
     * @date 2015-09-15
     */
    public function saveNotification()
    {
        // 发送邮件通知
        ob_start();
        var_export($_REQUEST);
        $text = ob_get_clean();
        $subject = "info";
        $to = Yii::app()->params['logmails'];
        SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
    }
    
}
