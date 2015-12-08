<?php

/**
 * @desc Return更新处理类
 * @author liaojianwen
 * @date 2015-07-28
 */
class EbayListingDownModel extends BaseModel
{
    
    private $compatabilityLevel; // eBay API version
    
    private $devID;
    
    private $appID;
    
    private $certID;
    
    private $serverUrl; // eBay 服务器地址
    
    private $userToken; // token
    
    private $siteToUseID; // site id
    
    /**
     * @desc 覆盖父方法返回EbayListingDownModel对象(单)实例
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-07-28
     * @return EbayListingDownModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-03-27
     */
    public function __construct()
    {
        $this->compatabilityLevel = 931; // eBay API version
        if (Yii::app()->params['ebay_api_production']) {
            $this->devID = Yii::app()->params['devIDinfo']['devID'];
            $this->appID = Yii::app()->params['devIDinfo']['appID'];
            $this->certID = Yii::app()->params['devIDinfo']['certID'];
        } else {
            $this->devID = 'cfb73f1d-48f3-4bdf-aa79-07ed14b1f677';
            $this->appID = 'dfda6a3e-7727-43ee-b871-81c9937cb350';
            $this->certID = 'abc4cf49-6531-4555-b16b-bcee34b5aca3';
        }
    }
    
    /**
     * @desc 获取已经下载的Returns数据
     * @param int $taskNumber
     * @author liaojianwen
     * @date 2015-06-16
     * @return Ambigous <string, multitype:, mixed>|boolean
     */
    public function getListingDownData($taskNumber)
    {
        EbayListingDownDAO::getInstance()->begintransaction();
        try {
            // 获取符合条件的数据
            $result = EbayListingDownDAO::getInstance()->getListingDownData($taskNumber);
            
            if (empty($result)) {
                EbayListingDownDAO::getInstance()->rollback();
                return false;
            }
            
            // 拼接ID数组
            $_ids = array();
            foreach ($result as $key => $value) {
                $_ids[] = $value['down_id'];
            }
            $_ids = implode(',', $_ids);
            
            $columns = array(
                'status' => boolConvert::toInt01(true),
                'lastruntime' => time()
            );
            $conditions = "down_id in ({$_ids})";
            EbayListingDownDAO::getInstance()->iupdate($columns, $conditions, array()); // 标记为正在处理
            
            EbayListingDownDAO::getInstance()->increase('runcount', "down_id in ({$_ids})"); // 运行次数+1
            
            EbayListingDownDAO::getInstance()->commit();
            return $result;
        } catch (Exception $e) {
            EbayListingDownDAO::getInstance()->rollback();
            return false;
        }
    }
    
    /**
     * @desc 删除已经处理了的return原始数据 
     * @param string $ids            
     * @author liaojianwen
     * @date 2015-06-16
     * @return Ambigous <boolean, number>
     */
    public function deleteListingDownData($ids)
    {
        return EbayListingDownDAO::getInstance()->deleteByIds($ids);
    }
    
    
    /**
     * @desc 获取listing 信息
     * @param int $startTime
     * @param int $endTime
     * @param string $token
     * @param string $siteid
     * @param int $page
     * @param int $pageSize
     * @author liaojianwen
     * @date 2015-07-28
     */
    public function getEbayListing($token,$startTime,$endTime,$siteid,$page,$pageSize)
    {
         $callName ='GetSellerList';
          if (Yii::app()->params['ebay_api_production']) {
                $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
            } else {
                $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
            }
           $requestXML = '<?xml version="1.0" encoding="utf-8"?>
            <GetSellerListRequest xmlns="urn:ebay:apis:eBLBaseComponents">
            <RequesterCredentials>
				<eBayAuthToken>'.$token.'</eBayAuthToken>
			</RequesterCredentials>'.
              '<EndTimeFrom>'.$this->fmtDate($startTime).'</EndTimeFrom>'.
     		 '<EndTimeTo>'.$this->fmtDate($endTime).'</EndTimeTo>'.
              '<IncludeWatchCount>true</IncludeWatchCount>'.
              '<IncludeVariations>true</IncludeVariations>'.
              '<Pagination>'. 
                    '<EntriesPerPage>'.$pageSize.'</EntriesPerPage>'.
       		        '<PageNumber>'.$page.'</PageNumber>'.
              '</Pagination>'. 
              '<DetailLevel>ReturnAll</DetailLevel>'.
            '</GetSellerListRequest>';
        $session = new eBaySession($this->serverUrl);
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:{$this->compatabilityLevel}";
        $session->headers[] = "X-EBAY-API-DEV-NAME:{$this->devID}";
        $session->headers[] = "X-EBAY-API-APP-NAME:{$this->appID}";
        $session->headers[] = "X-EBAY-API-CERT-NAME:{$this->certID}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        $session->headers[] = "X-EBAY-API-SITEID:{$siteid}";
        
        $tryCount = 0;
        
        label1:
        
        $responseXml = $session->sendHttpRequest($requestXML);
        
        if (stripos($responseXml, '<ack>Failure</ack>')) {
            iMongo::getInstance()->setCollection('getEbayListingF')->insert(array(
                'requestXmlBody' => $requestXML,
                'responseXml' => $responseXml,
                'time' => time(),
                'tryCount' => $tryCount,
                'times' => 1
            ));
            sleep(1);
            $responseXml = $session->sendHttpRequest($requestXML);
        }
        
        if (! XMLTool::IsXML($responseXml)) {
            if ($tryCount < 22) {
                $tryCount ++;
                goto label1;
            }
        }
        
        return $responseXml;
    
    }
    
    
    /**
     * @desc 解析xml并转化为数组
     * @param string $xml XML格式数据
     * @return array array格式数据
     * @author Weixun Luo
     * @date 2014-10-10
     */
    public function xml2Array($xmlString){
        $xmlObject = $this->xml2Object($xmlString);
        return json_decode(json_encode($xmlObject), true);
    }
    
    /**
     * @desc 解析xml并转化为对象
     * @param string $xml XML格式数据
     * @return object object格式数据
     * @author Weixun Luo
     * @date 2014-10-10
     */
    protected function xml2Object($xmlString){
        return simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);
    }
       /**
     * @desc 获取格式化的GMT时间
     * @param int $date
     * @author YangLong
     * @date 2015-02-12
     * @return string
     */
    private function fmtDate($date)
    {
        return gmdate('Y-m-d\TH:i:s\Z',$date);
    }
    
    
    
    public function getSellerList()
    {
           $token = 'AgAAAA**AQAAAA**aAAAAA**lP4+VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AElYGkAZKFogydj6x9nY+seQ**+uQAAA**AAMAAA**+P6R0gQ0z30XPNdYXYVHcHueeIp9vlyg2uN9lhDlJFwQ4KGhzl9trw013I91BCGemaTzUEHdArF0yUFZV6qHMdY9Vme/Ii4sUD9YmjwsYDKiX3Tr7e6wnfvGqO7HJtL8jGPb/iyciMiBMFBZRLaK3BQzYCTgQrsRVWrkZXkCaSCPKpKqhPtqa8Qv7sbBHmqCGkaGHW2eEqZTQWYCVua1kmi74XbU4JFvHEzZy+JRtn620er5vDoA8l+5zKzpQR2ofxnteFd2gO5g5GQsGi7pWr5vAsBD2lLPuaWgcoH2IDrwBfsoi3XTAEqQfwWJLRU2fR2z399NwnVJxmJZYYZJyarfgbLsroRzALoh67ld46auITYSPDx/tdWQQ0v8miebxyR+Ev9drivX7Iev6+ujjTitJMM4hbDMQP4wUGwv6fObhkkpgSkprNpnpQtwYgqJnkVyoPi4VgKJjVkn2zZMYxvzZsGv83T9lm3esSST1y3wbnQbFoVxWbmIwax0ybsLIQ8j2HIIlO+7DGpyRcX2vcQgP4HJSWt1fMW5JkOxZj25YNLONhDRfxR/9lmniO8eEcVbX4G4nf6XL/RUrys3+jwBmlZC7Bwcjvdz5YVlwbvY/2aA/ubshj6fgCVvTL/+gfA4GxlhW+3ucF/xLWoLm14ysKaFb6inxBeAGL1zw9a5fjVsaetIG8GRxFms7ICX/M//HxH5h5bMzUDu7S2qApLA7xcK0ng9HFuNeFsc39KZZLwuJZnWCn5sSB1KFZci';
           $startTime = '1435384800';
           $endTime = '1437955800';

        $siteid=0;
        $callName ='GetSellerList';
           if (Yii::app()->params['ebay_api_production']) {
                $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
            } else {
                $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
            }
           $requestXML = '<?xml version="1.0" encoding="utf-8"?>
            <GetSellerListRequest xmlns="urn:ebay:apis:eBLBaseComponents">
            <RequesterCredentials>
				<eBayAuthToken>'.$token.'</eBayAuthToken>
			</RequesterCredentials>'.
              '<EndTimeFrom>'.$this->fmtDate($startTime).'</EndTimeFrom>'.
     		 '<EndTimeTo>'.$this->fmtDate($endTime).'</EndTimeTo>'.
              '<IncludeWatchCount>true</IncludeWatchCount>'.
              '<Pagination>'. 
                    '<EntriesPerPage>100</EntriesPerPage>'.
       		        '<PageNumber>1</PageNumber>'.
              '</Pagination>'. 
              '<DetailLevel>ReturnAll</DetailLevel>'.
            '</GetSellerListRequest>';
            // @see http://developer.ebay.com/Devzone/return-management/Concepts/MakingACall.html
            $session = new eBaySession($this->serverUrl);
        $session->headers[] = "X-EBAY-API-COMPATIBILITY-LEVEL:931";
        $session->headers[] = "X-EBAY-API-DEV-NAME:{$this->devID}";
        $session->headers[] = "X-EBAY-API-APP-NAME:{$this->appID}";
        $session->headers[] = "X-EBAY-API-CERT-NAME:{$this->certID}";
        $session->headers[] = "X-EBAY-API-CALL-NAME:{$callName}";
        $session->headers[] = "X-EBAY-API-SITEID:{$siteid}";
            
            $responseXml = $session->sendHttpRequest($requestXML);
            print_r($responseXml);
    
    }
    
    
    
    
}