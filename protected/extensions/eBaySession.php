<?php

/**
 * @desc ebay API基础类
 * @author YangLong
 * @date 2015-03-26
 */
class eBaySession
{

    public $serverUrl;

    public $headers;

    public function __construct($serverUrl, $headers = array())
    {
        $this->serverUrl = $serverUrl;
        $this->headers = $headers;
    }

    /**
     * sendHttpRequest
     * Sends a HTTP request to the server for this session
     * Input:	$requestBody
     * Output:	The HTTP Response as a String
     */
    public function sendHttpRequest($requestBody)
    {
        ignore_user_abort(true);
        $key = 'eBaySessionCount';
        if (! iMemcache::getInstance()->get($key)) {
            iMemcache::getInstance()->set($key, 0, 0);
        }
        iMemcache::getInstance()->increment($key);
        
        // build eBay headers using variables passed via constructor
        $headers = $this->buildEbayHeaders();
        
        // initialise a CURL session
        $connection = curl_init();
        // set the server we are using (could be Sandbox or Production server)
        curl_setopt($connection, CURLOPT_URL, $this->serverUrl);
        
        // stop CURL from verifying the peer's certificate
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
        
        // set the headers using the array of headers
        curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
        
        // set method as POST
        curl_setopt($connection, CURLOPT_POST, 1);
        
        // set timeout
        curl_setopt($connection, CURLOPT_TIMEOUT, 300);
        
        // set the XML body of the request
        curl_setopt($connection, CURLOPT_POSTFIELDS, $requestBody);
        
        // set it to return the transfer as a string from curl_exec
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
        //
        // curl_setopt($connection, CURLOPT_TIMEOUT, 30 );
        
        // Send the Request
        $response = curl_exec($connection);
        
        // close the connection
        curl_close($connection);
        
        // return the response
        return $response;
    }

    /**
     * buildEbayHeaders
     * Generates an array of string to be used as the headers for the HTTP request to eBay
     * Output:	String Array of Headers applicable for this call
     */
    private function buildEbayHeaders()
    {
        return $this->headers;
    }
}
