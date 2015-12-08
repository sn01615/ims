<?php

/**
 * @desc IMS验证工具
 * @author YangLong
 * @date 2015-03-30
 */
class imsTokenTool
{

    private $mkey;
    
    private $error;

    private static $_instance;
    
    /**
     * @desc 初始化
     * @author YangLong
     * @date 2015-03-30
     */
    private function __construct()
    {
        $this->mkey = Yii::app()->params['imsTokenTool_mkey'];
        $this->error = Yii::app()->params['imsTokenTool_error'];
    }
    
    /**
     * @desc 获取对象
     * @author YangLong
     * @return imsTokenTool
     * @date 2015-03-30
     */
    public static function getInstance()
    {
        if (! (self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * @desc 根据key获取动态密码
     * @author YangLong
     * @date 2015-03-30
     */
    public function getToken()
    {
        $_time = (int) (time() / $this->error);
        return $this->xmd5($_time - 1) . 'U' . $this->xmd5($_time) . 'U' . $this->xmd5($_time + 1);
    }

    /**
     * @desc 验证key是否有效
     * @param string $key
     * @date 2015-03-30
     */
    public function verifyKey($key)
    {
        $_keys = explode('U', $key);
        $_mk = $this->getToken();
        $_mk = explode('U', $_mk);
        $_mk = $_mk[1];
        foreach ($_keys as $k => $v) {
            if ($v == $_mk) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * @desc 加上密匙生成的MD5
     * @param mixed $obj
     * @author YangLong
     * @date 2015-03-30
     */
    private function xmd5($obj)
    {
        return md5($this->mkey . md5($obj));
    }
}