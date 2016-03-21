<?php

/**
 * @desc memcache queue class
 * @author YangLong
 * @date 2015-09-20
 */
class iMemQueue
{
    
    /**
     * @desc iMemQueue实例
     * @var iMemQueue
     */
    private static $_instance;
    
    /**
     * @desc 获取对象
     * @author YangLong
     * @date 2015-09-20
     * @return iMemQueue
     */
    public static function getInstance()
    {
        if (! (self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * @desc 初始化队列
     * @param string $key
     * @author YangLong
     * @date 2015-09-20
     * @return boolean
     */
    private function init($key)
    {
        $result = array();
        
        $result['head'] = iMemcache::getInstance()->get(md5("{$key}_head"));
        if ($result['head'] === false) {
            
            // 发送邮件通知
            ob_start();
            var_dump($key);
            var_dump(md5("{$key}_head"));
            $text = ob_get_clean();
            $subject = "Fatal error: iMemQueue init get head error.";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            
            iMemcache::getInstance()->set(md5("{$key}_head"), 0, 0);
            $result['head'] = 0;
        }
        $result['tail'] = iMemcache::getInstance()->get(md5("{$key}_tail"));
        if ($result['tail'] === false) {
            
            // 发送邮件通知
            ob_start();
            var_dump($key);
            var_dump(md5("{$key}_tail"));
            $text = ob_get_clean();
            $subject = "Fatal error: iMemQueue init get tail error.";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            
            iMemcache::getInstance()->set(md5("{$key}_tail"), 0, 0);
            $result['tail'] = 0;
        }
        
        $result['length'] = $result['tail'] - $result['head'];
        
        return $result;
    }
    
    /**
     * @desc push queue
     * @param string $key
     * @param mixed $value
     * @author YangLong
     * @date 2015-09-20
     * @return boolean
     */
    public function push($key, $value)
    {
        $result = $this->init($key);
        iMemcache::getInstance()->increment(md5("{$key}_tail"));
        return iMemcache::getInstance()->set(md5("{$key}_{$result['tail']}_queue"), $value, 0);
    }
    
    /**
     * @desc pop queue
     * @param string $key
     * @author YangLong
     * @date 2015-09-20
     * @return mixed
     */
    public function pop($key)
    {
        $_result = $this->init($key);
        if ($_result['length'] === 0) {
            return false;
        }
        $result = iMemcache::getInstance()->get(md5("{$key}_{$_result['head']}_queue"));
        iMemcache::getInstance()->increment(md5("{$key}_head"));
        iMemcache::getInstance()->delete(md5("{$key}_{$_result['head']}_queue"));
        return $result;
    }
}