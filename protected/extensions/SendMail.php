<?php

/**
 * @desc 发送邮件
 * @author YangLong
 * @date 2015-09-08
 */
class SendMail
{

    /**
     * @desc 发送邮件
     * @param string $subject
     * @param string $text
     * @param array $to array(array('168119405@qq.com','杨龙'),array('168119405@qq.com','杨龙'), .... )
     * @author YangLong
     * @date 2015-09-08
     */
    public static function send($subject, $text, $to)
    {
        // 特殊处理 记录404IP
        if (stripos($text, '[exception.CHttpException.404]') !== false) {
            $clientip = imsTool::get_client_ip();
            if ($clientip !== 'UNKNOWN') {
                for ($i = 0; $i < 10; $i ++) {
                    $key = '404IP_' . $clientip . '_' . $i;
                    if (iMemcache::getInstance()->get($key) === false) {
                        iMemcache::getInstance()->set($key, true, 600);
                        break;
                    }
                }
            }
        }
        
        // 404 丢弃
        if (stripos($text, 'Unable to resolve the request ')) {
            iMongo::getInstance()->setCollection('sendMailError')->insert(array(
                'subject' => $subject,
                'text' => $text,
                'to' => $to
            ));
            return false;
        }
        
        $config = Yii::app()->params['smtp_config']['default'];
        $user = $config['username'];
        $pass = $config['password'];
        $smtp = new Smtp($config['server']);
        $smtp->from($config['from']);
        foreach ($to as $key => $value) {
            $smtp->to($value, $key);
        }
        $smtp->auth($user, $pass);
        $smtp->subject($subject);
        $smtp->text($text);
        try {
            $smtp->send();
        } catch (Exception $e) {
            iMongo::getInstance()->setCollection('sendMailError')->insert(array(
                'error' => $e->getMessage(),
                'subject' => $subject,
                'text' => $text,
                'to' => $to
            ));
        }
    }

    /**
     * @desc 异步发送邮件
     * @param string $subject
     * @param string $text
     * @param array $to array(array('168119405@qq.com','杨龙'),array('168119405@qq.com','杨龙'), .... )
     * @author YangLong
     * @date 2015-09-25
     */
    public static function sendSync($subject, $text, $to)
    {
        $key = 'SendMailQueue';
        $value = array(
            'subject' => $subject,
            'text' => $text,
            'to' => $to
        );
        return iMemQueue::getInstance()->push($key, $value);
    }

    /**
     * @desc 运行发送邮件队列
     * @author YangLong
     * @date 2015-09-25
     * @return boolean
     */
    public static function sendSyncRun()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $startTime = time();
        
        label1:
        
        if (time() - $startTime > 295) {
            return false;
        }
        
        $key = 'SendMailQueue';
        $mailinfo = iMemQueue::getInstance()->pop($key);
        if ($mailinfo !== false) {
            $subject = $mailinfo['subject'];
            $text = $mailinfo['text'];
            $to = $mailinfo['to'];
            self::send($subject, $text, $to);
            goto label1;
        } else {
            usleep(200000);
            goto label1;
        }
    }
}
