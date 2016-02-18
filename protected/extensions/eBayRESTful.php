<?php

/**
 * @desc ebay API基础类
 * @author YangLong
 * @date 2016-02-17
 */
class eBayRESTful
{

    public $serverUrl;

    public $headers;

    public $method;

    public function __construct($serverUrl, $method = 'GET', $headers = array())
    {
        $this->serverUrl = $serverUrl;
        $this->headers = $headers;
        $this->method = strtoupper($method);
    }

    /**
     * sendHttpRequest
     * Sends a HTTP request to the server for this session
     * Input: $requestBody
     * Output: The HTTP Response as a String
     */
    public function sendHttpRequest($requestBody)
    {
        // build eBay headers using variables passed via constructor
        $headers = $this->buildEbayHeaders();
        
        // initialise a CURL session
        $connection = curl_init();
        // set the server we are using (could be Sandbox or Production server)
        curl_setopt($connection, CURLOPT_URL, $this->serverUrl);
        
        // stop CURL from verifying the peer's certificate
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, false);
        
        // set the headers using the array of headers
        curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
        
        if ($this->method == 'POST') {
            // set method as POST
            curl_setopt($connection, CURLOPT_POST, true);
            
            // set the body of the request
            curl_setopt($connection, CURLOPT_POSTFIELDS, $requestBody);
        } else {}
        
        // set it to return the transfer as a string from curl_exec
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
        
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
     * Output: String Array of Headers applicable for this call
     */
    private function buildEbayHeaders()
    {
        return $this->headers;
    }
}
