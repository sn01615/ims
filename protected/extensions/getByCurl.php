<?php

/**
 * @desc 根据URL获取内容
 * @author YangLong
 * @date 2015-04-22
 */
class getByCurl
{

    /**
     * @desc 根据URL获取内容
     * @param string $url
     * @param integer $error CURL 状态码
     * @author YangLong
     * @date 2015-04-22
     * @return string
     */
    static public function get($url, &$error = null)
    {
        $connection = curl_init();
        curl_setopt($connection, CURLOPT_URL, $url);
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($connection);
        $error = curl_errno($connection);
        curl_close($connection);
        return $response;
    }
    
    /**
     * @desc 根据URL发送/获取内容
     * @param string $url
     * @param string $postdata
     * @author YangLong
     * @date 2015-04-22
     * @return string
     */
    static public function post($url, $postdata)
    {
        $connection = curl_init();
        curl_setopt($connection, CURLOPT_URL, $url);
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($connection, CURLOPT_POST, 1);
        curl_setopt($connection, CURLOPT_POSTFIELDS, $postdata);
        $response = curl_exec($connection);
        curl_close($connection);
        return $response;
    }
}
