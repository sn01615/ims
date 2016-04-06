<?php

/**
 * @desc Case更新处理类
 * @author YangLong
 * @date 2015-04-20
 */
class CaseUpdateModel extends BaseModel
{

    /**
     * @desc 覆盖父方法返回CaseUpdateModel对象(单)实例
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-03-26
     * @return CaseUpdateModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @desc 生成Case状态更新队列
     * @author YangLong
     * @date 2015-05-26
     * @return boolean
     */
    public function generateCaseUpdateStatusQueue()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $shops = MsgDownDAO::getInstance()->getEbShop('update_status');
        CaseUpdateStatusQueueDAO::getInstance()->begintransaction();
        try {
            foreach ($shops as $key => $shop) {
                $conditions = 'shop_id=:shop_id';
                $params = array(
                    ':shop_id' => $shop['shop_id']
                );
                CaseUpdateStatusQueueDAO::getInstance()->idelete($conditions, $params);
                
                $_time = time();
                $columns = array(
                    'shop_id' => $shop['shop_id'],
                    'site_id' => $shop['site_id'],
                    'token' => $shop['token'],
                    'start_time' => $_time - EnumOther::CASE_UPLOAD_SIZE * 24 * 3600,
                    'create_time' => $_time
                );
                CaseUpdateStatusQueueDAO::getInstance()->iinsert($columns);
                
                $columns = array(
                    'case_update_status_time' => $_time
                );
                $conditions = 'shop_id=:shop_id';
                $params = array(
                    ':shop_id' => $shop['shop_id']
                );
                ShopDAO::getInstance()->iupdate($columns, $conditions, $params);
            }
            
            CaseUpdateStatusQueueDAO::getInstance()->commit();
            return true;
        } catch (Exception $e) {
            CaseUpdateStatusQueueDAO::getInstance()->rollback();
            return false;
        }
    }

    /**
     * @desc 生成Open Case更新(down)队列
     * @author YangLong
     * @date 2015-04-20
     * @return boolean
     */
    public function generateCaseUpdateQueue()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $shops = MsgDownDAO::getInstance()->getEbShop('update');
        
        foreach ($shops as $key => $shop) {
            $conditions = 'AccountID=:AccountID';
            $params = array(
                ':AccountID' => $shop['AccountID']
            );
            CaseUpdateQueueDAO::getInstance()->idelete($conditions, $params);
            
            $_time = time();
            $columns = array(
                'AccountID' => $shop['AccountID'],
                'seller_id' => $shop['seller_id'],
                'shop_id' => $shop['shop_id'],
                'site_id' => $shop['site_id'],
                'token' => $shop['token'],
                'create_time' => $_time
            );
            CaseUpdateQueueDAO::getInstance()->iinsert($columns);
            
            $columns = array(
                'open_case_down_time' => $_time
            );
            $conditions = 'shop_id=:shop_id';
            $params = array(
                ':shop_id' => $shop['shop_id']
            );
            ShopDAO::getInstance()->iupdate($columns, $conditions, $params);
        }
        
        return true;
    }

    /**
     * @desc 运行Case状态更新队列
     * @author YangLong
     * @date 2015-05-26
     * @return boolean
     */
    public function executeCaseUpdateStatusQueue()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $Queues = CaseUpdateStatusQueueDAO::getInstance()->getUpdateStatusQueueData(EnumOther::DOWN_EXECUTESIZE);
        if ($Queues !== false) {
            foreach ($Queues as $key => $Queue) {
                $dataxml = CaseDownModel::model()->getUserCases('', '', $Queue['token'], $Queue['site_id']);
                $doc = phpQuery::newDocumentXML($dataxml);
                phpQuery::selectDocument($doc);
                
                if ($doc['ack']->html() == 'Failure') {
                    continue;
                }
                
                CaseUpdateStatusDAO::getInstance()->begintransaction();
                try {
                    $columns = array(
                        'dataxml' => $dataxml,
                        'create_time' => time()
                    );
                    CaseUpdateStatusDAO::getInstance()->iinsert($columns);
                    
                    $conditions = 'case_update_status_queue_id=:case_update_status_queue_id';
                    $params = array(
                        ':case_update_status_queue_id' => $Queue['case_update_status_queue_id']
                    );
                    CaseUpdateStatusQueueDAO::getInstance()->idelete($conditions, $params);
                    
                    CaseUpdateStatusDAO::getInstance()->commit();
                } catch (Exception $e) {
                    iMongo::getInstance()->setDbname('Case')
                        ->setCollection('CaseUpdateStatusInsert')
                        ->insert(
                        array(
                            'getMessage' => $e->getMessage(),
                            'getFile' => $e->getFile(),
                            'getLine' => $e->getLine()
                        ));
                    CaseUpdateStatusDAO::getInstance()->rollback();
                }
            }
        } else {
            return false;
        }
    }

    /**
     * @desc 运行Open Case更新(down)队列
     * @author YangLong
     * @date 2015-04-20
     * @return boolean
     */
    public function executeCaseUpdateQueue()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $startTime = time();
        
        label1:
        
        if (time() - $startTime > 300) {
            return false;
        }
        
        $Queues = CaseUpdateQueueDAO::getInstance()->getUpdateQueueData(EnumOther::DOWN_EXECUTESIZE);
        
        if ($Queues !== false) {
            $pageSize = 25;
            foreach ($Queues as $key => $Queue) {
                $page = 0;
                while (true) {
                    $page ++;
                    
                    $xmldata = array();
                    $xmldata['Cases'] = CaseDownModel::model()->getUserCases('', '', $Queue['token'], $Queue['site_id'], $page, $pageSize, true);
                    
                    $doc = phpQuery::newDocumentXML($xmldata['Cases']);
                    phpQuery::selectDocument($doc);
                    
                    if ($doc['ack']->html() == 'Failure') {
                        continue 2;
                    }
                    
                    $length = $doc['cases>caseSummary']->length;
                    
                    for ($i = 0; $i < $length; $i ++) {
                        $caseId_id = $doc['cases>caseSummary']->eq($i)
                            ->find('caseId>id')
                            ->html();
                        $caseId_type = $doc['cases>caseSummary']->eq($i)
                            ->find('caseId>type')
                            ->html();
                        
                        if ($caseId_id !== false && $caseId_type !== false) {
                            if ($caseId_type == 'EBP_INR' || $caseId_type == 'EBP_SNAD') {
                                $xmldata['CaseDetail'][$caseId_id] = CaseDownModel::model()->getEBPCaseDetail($caseId_id, $caseId_type, 
                                    $Queue['token'], $Queue['site_id']);
                                $xmldata['ActivityOptions'][$caseId_id] = CaseDownModel::model()->getActivityOptions($caseId_id, $caseId_type, 
                                    $Queue['token'], $Queue['site_id']);
                            } else {
                                $xmldata['CaseDetail'][$caseId_id] = CaseDownModel::model()->getDispute($caseId_id, $Queue['token'], 
                                    $Queue['site_id']);
                            }
                        }
                    }
                    $params = array(
                        'seller_id' => $Queue['seller_id'],
                        'shop_id' => $Queue['shop_id'],
                        'text_json' => base64_encode(serialize($xmldata))
                    );
                    CaseDownDAO::getInstance()->begintransaction();
                    $lid = CaseDownDAO::getInstance()->insert($params);
                    if ($lid !== false) {
                        $conditions = 'case_update_queue_id=:case_update_queue_id';
                        $params = array(
                            ':case_update_queue_id' => $Queue['case_update_queue_id']
                        );
                        CaseUpdateQueueDAO::getInstance()->idelete($conditions, $params);
                        CaseDownDAO::getInstance()->commit();
                    } else {
                        CaseDownDAO::getInstance()->rollback();
                    }
                    if ($doc['errorMessage>error>errorId']->html() == '1302') {
                        break;
                    }
                    if ($doc['errorMessage>error>errorId']->html() > 0) {
                        iMongo::getInstance()->setCollection('CaseUpdateOtherErr')->insert(
                            array(
                                'xmldata' => $xmldata,
                                'time' => time()
                            ));
                        break;
                    }
                }
            }
            
            goto label1;
        } else {
            sleep(5);
            goto label1;
        }
    }

    /**
     * @desc 获取Case状态原始XML数据
     * @author YangLong
     * @date 2015-05-26
     * @return mixed
     */
    public function getCasesStatus($picksize = 10)
    {
        $result = CaseUpdateStatusDAO::getInstance()->getCasesStatus($picksize);
        if ($result === false) {
            $result = $this->handleApiFormat(EnumOther::ACK_FAILURE, '');
        } else {
            $result = $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        }
        return $result;
    }

    /**
     * @desc 删除Case状态原始XML数据
     * @param string $ids
     * @author YangLong
     * @date 2015-05-26
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function deleteCasesStatus($ids)
    {
        if (empty($ids)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '`ids` can not empty.');
        }
        $conditions = CaseUpdateStatusDAO::getInstance()->igetproperty('primaryKey') . ' in (' . $ids . ')';
        $result = CaseUpdateStatusDAO::getInstance()->idelete($conditions, array());
        
        if ($result === false) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'mysql delete err');
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
    }

    /**
     * @desc 解析状态原始XML数据
     * @param mixed $result
     * @author YangLong
     * @date 2015-05-26
     * @return array
     */
    public function parseCasesStatus($result)
    {
        $result = json_decode($result, true);
        
        if (is_array($result) && $result['Ack'] == 'Success') {
            $result = $result['Body'];
        } else {
            return false;
        }
        
        if (is_array($result)) {
            $resultArr = array();
            foreach ($result as $key => $value) {
                $doc = phpQuery::newDocumentXML($value['dataxml']);
                phpQuery::selectDocument($doc);
                
                if (pq('ack')->html() === 'Success') {
                    $cases = pq('cases>caseSummary');
                    $length = $cases->length;
                    
                    for ($i = 0; $i < $length; $i ++) {
                        $_case = $cases->eq($i);
                        $caseId_id = $_case->find('caseId>id')->html();
                        
                        $columns = array(
                            's_cancelTransactionStatus' => $_case->find('status>cancelTransactionStatus')->html(),
                            's_EBPINRStatus' => $_case->find('status>EBPINRStatus')->html(),
                            's_EBPSNADStatus' => $_case->find('status>EBPSNADStatus')->html(),
                            's_INRStatus' => $_case->find('status>INRStatus')->html(),
                            's_PaypalINRStatus' => $_case->find('status>PaypalINRStatus')->html(),
                            's_PaypalSNADStatus' => $_case->find('status>PaypalSNADStatus')->html(),
                            's_returnStatus' => $_case->find('status>returnStatus')->html(),
                            's_SNADStatus' => $_case->find('status>SNADStatus')->html(),
                            's_UPIStatus' => $_case->find('status>UPIStatus')->html()
                        );
                        
                        foreach ($columns as $k => $val) {
                            if ($val === false || $val === null) {
                                unset($columns[$k]);
                            }
                        }
                        
                        $conditions = 'caseId_id=:caseId_id';
                        $params = array(
                            ':caseId_id' => $caseId_id
                        );
                        
                        CaseDAO::getInstance()->iupdate($columns, $conditions, $params);
                    }
                }
                
                $resultArr[] = $value['case_update_status_id'];
            }
            return $resultArr;
        } else {
            return false;
        }
    }
}
