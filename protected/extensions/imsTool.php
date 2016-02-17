<?php

/**
 * @desc 工具箱
 * @author YangLong
 * @date 2015-03-09
 */
class imsTool
{

    const ECHO_DEBUG_INFO = TRUE;

    private static $timerArr;

    static public function ehcoDebugInfo($info = '')
    {
        if (self::ECHO_DEBUG_INFO) {
            echo $info;
            echo "\t" . 'Memory Usage:' . self::formatmemorynumber(memory_get_usage()) . "\t";
            echo 'Memory Limit:' . self::formatmemorynumber(memory_get_peak_usage()) . " time:" . time() . " :" . date('h:i:s') . "\n";
        }
    }

    static private function formatmemorynumber($number)
    {
        return substr(round(($number / 1024 / 1024), 4) . '00000', 0, 6) . ' MB ';
    }

    static public function timerInit($tag)
    {
        self::$timerArr[$tag] = array();
    }

    static public function timer($tag, $note)
    {
        self::$timerArr[$tag][] = array(
            microtime(true),
            $note
        );
    }

    static public function timerget($tag)
    {
        return self::$timerArr[$tag];
    }

    static public function xmlHeader()
    {
        header('Content-Type: text/xml');
    }

    static public function utf8Header()
    {
        header('Content-Type: text/html;charset=utf-8');
    }
    
    /**
     * @desc 通过url字符串获取值(专)
     * @param string $str
     * @author YangLong
     * @date 2015-06-27
     * @return multitype:Ambigous <>
     */
    static public function get($str)
    {
        if (empty($str))
            return array();
        $data = array();
        $parameter = explode('?', $str);
        $parameter = array_pop($parameter);
        $parameter = explode('&', $parameter);
        foreach ($parameter as $val) {
            $tmp = explode('=', $val);
            $data[$tmp[0]] = $tmp[1];
        }
        return $data;
    }
    
    /**
     * @desc 文本统一空白符(专)
     * @param string $str
     * @author YangLong
     * @date 2015-06-29
     * @return string
     */
    static public function msgClear($str)
    {
        $str = trim(preg_replace('/[\s\n\t\r]+/', ' ', $str));
        return $str;
    }
    
    /**
     * @desc 消息标题前缀去除方法(专)
     * @param string $str
     * @author YangLong
     * @date 2015-07-03
     * @return string
     */
    static public function subjectClear($str)
    {
        $str = str_ireplace('：', ':', $str);
        $str = explode(': ', $str);
        $str = array_pop($str);
        $str = trim($str);
        return $str;
    }
    
    /**
     * @desc 过滤危险字符
     * @param string $string
     * @author YangLong
     * @date 2015-09-02
     * @return string
     */
    static public function safe_replace($string)
    {
        $string = str_replace('`', '', $string);
        $string = str_replace('\'', '', $string);
        $string = str_replace('\\', '', $string);
        $string = str_replace('/', '', $string);
        $string = str_replace('"', '', $string);
        $string = str_replace('*', '', $string);
        $string = str_replace('<', '', $string);
        $string = str_replace('>', '', $string);
        $string = str_replace(';', '', $string);
        $string = str_replace('{', '', $string);
        $string = str_replace('}', '', $string);
        $string = str_replace('%', '', $string);
        return $string;
    }
    
    /**
     * @desc 获取客户IP地址
     * @author YangLong
     * @date 2015-09-28
     * @return Ambigous <string, unknown>
     */
    static public function get_client_ip()
    {
        $ipaddress = '';
        if (@$_SERVER['HTTP_CLIENT_IP'])
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        elseif (@$_SERVER['HTTP_X_FORWARDED_FOR'])
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        elseif (@$_SERVER['HTTP_X_FORWARDED'])
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        elseif (@$_SERVER['HTTP_FORWARDED_FOR'])
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        elseif (@$_SERVER['HTTP_FORWARDED'])
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        elseif (@$_SERVER['REMOTE_ADDR'])
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
    
    /**
     * @desc 判断IP是否被封,如果被封则结束运行
     * @author YangLong
     * @date 2015-09-28
     * @return null
     */
    static public function ipAlow()
    {
        $clientip = imsTool::get_client_ip();
        if ($clientip !== 'UNKNOWN') {
            $count = 0;
            for ($i = 0; $i < 10; $i ++) {
                $key = md5("404iplist{$clientip}_{$i}");
                if (iMemcache::getInstance()->get($key) !== false) {
                    $count ++;
                }
            }
            if ($count > 9) {
                self::utf8Header();
                echo "<h2>:(</h2>\n<h5>Your IP address has been blocked.</h5>";
                die();
            }
        }
    }
    
    /**
     * @desc 清理重复记录
     * @param string $dao
     * @param array $apk
     * @author YangLong
     * @date 2015-11-02
     * @return null
     */
    static public function clearDuplication($dao, $apk)
    {
        array_shift($apk);
        foreach ($apk as $value) {
            $conditions = array();
            $params = array();
            foreach ($value as $key => $val) {
                $conditions[] = "{$key}=:{$key}";
                $params[":{$key}"] = $val;
            }
            $conditions = implode(' and ', $conditions);
            $result = $dao::getInstance()->idelete($conditions, $params);
            
            // 发送邮件通知
            ob_start();
            echo "date:\n";
            echo date('Y-m-d H:i:s');
            echo "\ngmdate:\n";
            echo gmdate('Y-m-d H:i:s');
            echo "\n" . 'table:' . "\n";
            echo $dao::getInstance()->getTableName();
            echo "\npk:\n";
            echo $dao::getInstance()->getPk();
            echo "\n受影响记录条数：{$result}\n";
            var_dump($value);
            $text = ob_get_clean();
            $subject = "[Error.data.duplication.delete]";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
        }
    }
    
    /**
     * @desc 检查params里有没有重复并E-mail报警
     * @param array $columns
     * @param array $params
     * @author YangLong
     * @date 2015-11-23
     * @return null
     */
    static public function paramsDuplicationCkeck($columns, $params)
    {
        foreach ($columns as $key => $value) {
            if (isset($params[':' . $key])) {
                // 发送邮件通知
                ob_start();
                var_export($columns);
                echo "\n\n";
                var_export($params);
                $text = ob_get_clean();
                $subject = "[Error.code.params.duplication]";
                $to = Yii::app()->params['logmails'];
                SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
                die();
            }
        }
    }
    
    /**
     * @desc 删除非打印字符
     * @param $str $param 需要过滤的字符串
     * @param string $note 标题前缀
     * @author YangLong
     * @date 2015-12-04
     * @return string 过滤后的字符串
     */
    static public function removeNonPrintable($str, $note = '？')
    {
        $_length1 = strlen($str);
        $str2 = preg_replace('/[\x00-\x08\x1f]/u', '', $str);
        $_length2 = strlen($str2);
        if ($_length1 !== $_length2) {
            // $subject = "{$note} clean";
            // ob_start();
            // echo "--------------------------------------------\n";
            // echo $str;
            // echo "\n--------------------------------------------";
            // $text = ob_get_clean();
            // $to = Yii::app()->params['logmails'];
            // SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            file_put_contents(BASE_PATH . '/filelog/' . date('Y-m-d_h.i.s_') . microtime(true), $str);
        }
        return $str2;
    }
    
}
