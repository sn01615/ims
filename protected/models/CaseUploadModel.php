<?php

/**
 * @desc Case处理上传类
 * @author YangLong
 * @date 2015-04-16
 */
class CaseUploadModel extends BaseModel
{
    
    /**
     * @desc 覆盖父方法,返回此对象的(单)实例
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-04-16
     * @return CaseUploadModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @desc 运行Case处理队列
     * @author YangLong
     * @date 2015-04-16
     * @return null|boolean
     */
    public function executeCaseUpload()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $startTime = time();
        
        label1:
        
        if (time() - $startTime > 595) {
            return false;
        }
        
        $Queues = CaseUploadQueueDAO::getInstance()->getUploadQueueData(EnumOther::CASE_UPLOAD_PICK_SIZE);
        if ($Queues !== false && is_array($Queues)) {
            foreach ($Queues as $key => $Queue) {
                
                // response message to buyer
                if ($Queue['upload_type'] == 'addResponse') {
                    $result = $this->addResponse($Queue);
                }
                
                // provide Tracking Info
                if ($Queue['upload_type'] == 'addTrackingInfo') {
                    $result = $this->addTrackingInfo($Queue);
                }
                
                // provide addShipping Info
                if ($Queue['upload_type'] == 'addShippingInfo') {
                    $result = $this->addShippingInfo($Queue);
                }
                
                // 全额退款
                if ($Queue['upload_type'] == 'fullRefund') {
                    $result = $this->fullRefund($Queue);
                }
                
                // 部分退款
                if ($Queue['upload_type'] == 'partialRefund') {
                    $result = $this->partialRefund($Queue);
                }
                
                // 退款并退货和提供退货地址
                if ($Queue['upload_type'] == 'returnItemRefund') {
                    $result = $this->returnItemRefund($Queue);
                }
                
                // 升级Case(eBay介入)
                if ($Queue['upload_type'] == 'ebayHelp') {
                    $result = $this->ebayHelp($Queue);
                }
                
                // 新增Case
                if ($Queue['upload_type'] == 'addDispute') {
                    $result = $this->addDispute($Queue);
                }
                
                // 提供退货地址
                if ($Queue['upload_type'] == 'provideReturnInfo') {
                    $result = $this->provideReturnInfo($Queue);
                }
                
                // 向ebay 申诉
                if ($Queue['upload_type'] == 'appealEbay') {
                    $result = $this->appealEbay($Queue);
                }
                
                /* TODO more type */
                
                // if success, delete case upload queue by case_upload_queue_id
                if (isset($result) && $result !== false) {
                    $conditions = 'case_upload_queue_id=:case_upload_queue_id';
                    $params = array(
                        ':case_upload_queue_id' => $result
                    );
                    CaseUploadQueueDAO::getInstance()->idelete($conditions, $params);
                }
            }
        } else {
            sleep(5);
            goto label1;
        }
    }
    
    /**
     * @desc Case回复消息
     * @param array $Queue 队列数据
     * @author YangLong
     * @date 2015-04-20
     * @return int|boolean
     */
    private function addResponse($Queue)
    {
        $uploadData = unserialize($Queue['upload_data']);
        if ($uploadData['caseType'] == 'EBP_INR' || $uploadData['caseType'] == 'EBP_SNAD') {
            $caseId = $uploadData['caseId_id'];
            $caseType = $uploadData['caseType'];
            $messageToBuyer = $uploadData['responseText'];
            $token = $Queue['token'];
            
            $result = CaseDownModel::model()->offerOtherSolution($caseId, $caseType, $messageToBuyer, $token);
        } else {
            $disputeActivity = $uploadData['disputeActivity'];
            $disputeID = $uploadData['caseId_id'];
            $token = $Queue['token'];
            $messageText = $uploadData['responseText'];
            $siteid = $uploadData['siteid'];
            
            $result = CaseDownModel::model()->addDisputeResponse($disputeActivity, $disputeID, $token, $messageText, $siteid);
        }
        $doc = phpQuery::newDocumentXML($result);
        phpQuery::selectDocument($doc);
        if (pq('ack')->html() == 'Success') {
            // file_put_contents(__FUNCTION__ . 'Success.log', $doc . "\n", FILE_APPEND);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Success',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'xml' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 回复消息成功通知 [Success]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return $Queue['case_upload_queue_id'];
        } else {
            // file_put_contents(__FUNCTION__ . 'Err.log', $doc . "\n", FILE_APPEND);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Err',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'xml' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 回复消息失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return false;
        }
    }
    
    /**
     * @desc Case提供物流跟踪号
     * @param array $Queue 队列数据
     * @author YangLong
     * @date 2015-04-20
     * @return int|boolean
     */
    private function addTrackingInfo($Queue)
    {
        $uploadData = unserialize($Queue['upload_data']);
        
        $caseId = $uploadData['caseId_id'];
        $caseType = $uploadData['caseType'];
        $carrierUsed = $uploadData['carrier'];
        $trackingNumber = $uploadData['trackingNum'];
        $token = $Queue['token'];
        $comments = $uploadData['responseText'];
        
        $result = CaseDownModel::model()->provideTrackingInfo($caseId, $caseType, $carrierUsed, $trackingNumber, $token, $comments);
        
        $doc = phpQuery::newDocumentXML($result);
        phpQuery::selectDocument($doc);
        if (pq('ack')->html() == 'Success') {
            // file_put_contents(__FUNCTION__ . 'Success.log', $doc . "\n", FILE_APPEND);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Success',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'xml' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 添加物流单号成功通知 [Success]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return $Queue['case_upload_queue_id'];
        } else {
            // file_put_contents(__FUNCTION__ . 'Err.log', $doc . "\n", FILE_APPEND);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Err',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'xml' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 添加物流单号失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return false;
        }
    }
    
    /**
     * @desc 提供物流信息（无跟踪号）
     * @param array $Queue 队列数据
     * @author YangLong
     * @date 2015-04-20
     * @return int|boolean
     */
    private function addShippingInfo($Queue)
    {
        $uploadData = unserialize($Queue['upload_data']);
        
        $caseId = $uploadData['caseId_id'];
        $caseType = $uploadData['caseType'];
        $carrierUsed = $uploadData['carrier'];
        $shippedDate = $this->fmtDate($uploadData['shipdate']);
        $token = $Queue['token'];
        $comments = $uploadData['responseText'];
        
        $result = CaseDownModel::model()->provideShippingInfo($caseId, $caseType, $carrierUsed, $shippedDate, $token, $comments);
        
        $doc = phpQuery::newDocumentXML($result);
        phpQuery::selectDocument($doc);
        if (pq('ack')->html() == 'Success') {
            // file_put_contents(__FUNCTION__ . 'Success.log', $doc . "\n", FILE_APPEND);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Success',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'xml' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 添加发货信息成功通知 [Success]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return $Queue['case_upload_queue_id'];
        } else {
            // file_put_contents(__FUNCTION__ . 'Err.log', $doc . "\n", FILE_APPEND);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Err',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'xml' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 添加发货信息失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return false;
        }
    }
    
    /**
     * @desc 全额退款
     * @param array $Queue 队列数据
     * @author YangLong
     * @date 2015-04-20
     * @return int|boolean
     */
    private function fullRefund($Queue)
    {
        $uploadData = unserialize($Queue['upload_data']);
        
        $caseId = $uploadData['caseId_id'];
        $caseType = $uploadData['caseType'];
        $comments = $uploadData['responseText'];
        $token = $Queue['token'];
        
        $result = CaseDownModel::model()->issueFullRefund($caseId, $caseType, $comments, $token);
        
        $doc = phpQuery::newDocumentXML($result);
        phpQuery::selectDocument($doc);
        if (pq('ack')->html() == 'Success') {
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Success',
                'Queue' => serialize($Queue),
                'uploadData' => $uploadData,
                'xml' => $result,
                'time' => time()
            ));
            
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 全额退款成功通知 [Success]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            
            return $Queue['case_upload_queue_id'];
        } else {
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Err',
                'Queue' => serialize($Queue),
                'uploadData' => $uploadData,
                'xml' => $result,
                'time' => time()
            ));
            
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 全额退款失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            
            return false;
        }
    }

    /**
     * @desc 部分退款
     * @param array $Queue 队列数据
     * @author YangLong
     * @date 2015-04-20
     * @return int|boolean
     */
    private function partialRefund($Queue)
    {
        $uploadData = unserialize($Queue['upload_data']);
        
        $amount = $uploadData['amount'];
        $caseId = $uploadData['caseId_id'];
        $caseType = $uploadData['caseType'];
        $comments = $uploadData['responseText'];
        $token = $Queue['token'];
        
        $result = CaseDownModel::model()->issuePartialRefund($amount, $caseId, $caseType, $comments, $token);
        
        $doc = phpQuery::newDocumentXML($result);
        phpQuery::selectDocument($doc);
        if (pq('ack')->html() == 'Success') {
            // file_put_contents(__FUNCTION__ . 'Success.log', $doc . "\n", FILE_APPEND);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Success',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'xml' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 部分退款成功通知 [Success]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return $Queue['case_upload_queue_id'];
        } else {
            // file_put_contents(__FUNCTION__ . 'Err.log', $doc . "\n", FILE_APPEND);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Err',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'xml' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 部分退款失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return false;
        }
    }
    
    /**
     * @desc 退款并退货
     * @param array $Queue 队列数据
     * @author YangLong
     * @date 2015-04-20
     * @return int|boolean
     */
    private function returnItemRefund($Queue)
    {
        $uploadData = unserialize($Queue['upload_data']);
        
        $caseId = $uploadData['caseId_id'];
        $token = $Queue['token'];
        $returnMerchandiseAuthorization = $uploadData['merchatAuth'];
        $additionalReturnInstructions = $uploadData['responseText'];
        $city = $uploadData['city'];
        $country = $uploadData['country'];
        $name = $uploadData['contractName'];
        $postalCode = $uploadData['postcode'];
        $stateOrProvince = $uploadData['state'];
        $street1 = $uploadData['street'];
        $street2 = $uploadData['street2'];
        
        $result = CaseDownModel::model()->offerRefundUponReturn($caseId, $token, $returnMerchandiseAuthorization, $additionalReturnInstructions, $city, $country, $name, $postalCode, $stateOrProvince, $street1, $street2);
        
        $doc = phpQuery::newDocumentXML($result);
        phpQuery::selectDocument($doc);
        if (pq('ack')->html() == 'Success') {
            // file_put_contents(__FUNCTION__ . 'Success.log', $doc . "\n", FILE_APPEND);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Success',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'xml' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 退款并退货成功通知 [Success]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return $Queue['case_upload_queue_id'];
        } else {
            // file_put_contents(__FUNCTION__ . 'Err.log', $doc . "\n", FILE_APPEND);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Err',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'xml' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 退款并退货失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return false;
        }
    }
    
    /**
     * @desc 升级Case(eBay介入)
     * @param array $Queue 队列数据
     * @author YangLong
     * @date 2015-04-20
     * @return int|boolean
     */
    private function ebayHelp($Queue)
    {
        $uploadData = unserialize($Queue['upload_data']);
        
        $caseId = $uploadData['caseId_id'];
        $caseType = $uploadData['caseType'];
        $comments = $uploadData['responseText'];
        $token = $Queue['token'];
        
        if ($caseType == 'EBP_INR') {
            $sellerINRReason = $uploadData['reason'];
            $sellerSNADReason = '';
        } elseif ($caseType == 'EBP_SNAD') {
            $sellerINRReason = '';
            $sellerSNADReason = $uploadData['reason'];
        } else {
            // file_put_contents(__FUNCTION__ . 'Err2.log', $Queue['upload_data'] . "\n", FILE_APPEND);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Err2',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'time' => time()
            ));
            return false;
        }
        
        $result = CaseDownModel::model()->escalateToCustomerSupport($caseId, $caseType, $comments, $token, $sellerINRReason, $sellerSNADReason);
        
        $doc = phpQuery::newDocumentXML($result);
        phpQuery::selectDocument($doc);
        if (pq('ack')->html() == 'Success') {
            // file_put_contents(__FUNCTION__ . 'Success.log', $doc . "\n", FILE_APPEND);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Success',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'xml' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 申请ebay介入成功通知 [Success]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return $Queue['case_upload_queue_id'];
        } else {
            // file_put_contents(__FUNCTION__ . 'Err.log', $doc . "\n", FILE_APPEND);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Err',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'xml' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 申请ebay介入失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return false;
        }
    }
    
    /**
     * @desc 提供退货地址
     * @param $Queue
     * @author liaojianwen
     * @date 2015-07-08
     */
    public function provideReturnInfo($Queue)
    {
        $uploadData = unserialize($Queue['upload_data']);    
    
        $caseId = $uploadData['caseId_id'];
        $caseType = $uploadData['caseType'];
        $token = $Queue['token'];
        $returnMerchandiseAuthorization = $uploadData['returnMerchandiseAuthorization'];
        $city = $uploadData['city'];
        $country = $uploadData['country'];
        $name = $uploadData['name'];
        $postalCode = $uploadData['postalCode'];
        $stateOrProvince = $uploadData['stateOrProvince'];
        $street1 = $uploadData['street1'];
        $street2 = $uploadData['street2'];
        $result = CaseDownModel::model()->provideReturnInfo($caseId,$caseType,$token,$city,$country,$name,$postalCode,$stateOrProvince,$street1,$street2);
        
        $doc = phpQuery::newDocumentXML($result);
        phpQuery::selectDocument($doc);
        if (pq('ack')->html() == 'Success') {
            // file_put_contents(__FUNCTION__ . 'Success.log', $doc . "\n", FILE_APPEND);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Success',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'xml' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 提供退货地址成功通知 [Success]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return $Queue['case_upload_queue_id'];
        } else {
            // file_put_contents(__FUNCTION__ . 'Err.log', $doc . "\n", FILE_APPEND);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Err',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'xml' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 提供退货地址失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return false;
        }
    }
    
    /**
     * @desc 向ebay 申诉
     * @param array $Queue
     * @author liaojianwen
     * @date 2015-07-22
     * @return unknown|boolean
     */
    public function appealEbay($Queue)
    {
        $uploadData = unserialize($Queue['upload_data']);
        $caseId = $uploadData['caseId_id'];
        $caseType = $uploadData['caseType'];
        $token = $Queue['token'];
        $appealReason = $uploadData['appealReason'];
        $comments = $uploadData['responseText'];
        $result = CaseDownModel::Model()->appealToCustomerSupport($appealReason, $caseId, $caseType, $comments, $token);
        
        $doc = phpQuery::newDocumentXML($result);
        phpQuery::selectDocument($doc);
        if (pq('ack')->html() == 'Success') {
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Success',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'xml' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 申诉成功通知 [Success]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return $Queue['case_upload_queue_id'];
        } else {
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Err',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'xml' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 申诉失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return false;
        }
    }
    
    /**
     * @desc 新增Case
     * @param array $Queue 队列数据
     * @author YangLong
     * @date 2015-04-22
     * @return int|boolean
     */
    private function addDispute($Queue)
    {
        $uploadData = unserialize($Queue['upload_data']);
        $token = $Queue['token'];
        $DisputeReason = $uploadData['DisputeReason'];
        $DisputeExplanation = $uploadData['DisputeExplanation'];
        $ItemID = $uploadData['ItemID'];
        $TransactionID = $uploadData['TransactionID'];
        $OrderLineItemID = $uploadData['OrderLineItemID'];
        $siteId = $uploadData['siteId'];
        $result = CaseDownModel::model()->addDispute($token, $DisputeReason, $DisputeExplanation, $ItemID, $OrderLineItemID, $TransactionID, $siteId);
        
        $doc = phpQuery::newDocumentXML($result);
        phpQuery::selectDocument($doc);
        if (pq('ack')->html() == 'Success') {
            // file_put_contents(__FUNCTION__ . 'Success.log', $doc . "\n", FILE_APPEND);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Success',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'xml' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 新增成功通知 [Success]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return true;
        } else {
            // file_put_contents(__FUNCTION__ . 'Err.log', $doc . "\n", FILE_APPEND);
            iMongo::getInstance()->setCollection(__FUNCTION__)->insert(array(
                'type' => 'Err',
                'Queue' => serialize($Queue),
                'uploadData'=>$uploadData,
                'xml' => $result,
                'time' => time()
            ));
            // 发送邮件通知
            ob_start();
            echo "apiResult：\n";
            var_export($result);
            echo "\n\n队列内容：\n";
            var_export($Queue);
            $text = ob_get_clean();
            $subject = "Case 新增失败通知 [Failure]\n";
            $to = Yii::app()->params['logmails'];
            SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
            return false;
        }
    }
    
    /**
     * @desc 获取格式化的GMT时间
     * @param int $int 时间戳
     * @author YangLong
     * @date 2015-02-12
     * @return string
     */
    private function fmtDate($int)
    {
        return gmdate('Y-m-d\TH:i:s\Z', $int);
    }
}