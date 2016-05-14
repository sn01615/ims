<?php

/**
 * @desc Return更新处理类
 * @author liaojianwen
 * @date 2015-07-02
 */
class ReturnUpdateModel extends BaseModel
{

    /**
     * @desc 覆盖父方法返回CaseUpdateModel对象(单)实例
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-07-02
     * @return ReturnUpdateModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @desc 生成Return_request下载队列
     * @author liaojianwen
     * @date 2015-07-02
     * @return boolean
     */
    public function generateReturnUpdateQueue()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $shops = MsgDownDAO::getInstance()->getEbShop('return_update');
        ReturnUpdateQueueDAO::getInstance()->begintransaction();
        try {
            foreach ($shops as $key => $shop) {
                $conditions = 'AccountID=:AccountID';
                $params = array(
                    ':AccountID' => $shop['AccountID']
                );
                ReturnUpdateQueueDAO::getInstance()->idelete($conditions, $params);
                $_time = time();
                for ($i = 0; $i < 3; $i ++) {
                    $fromDate = $_time - ($i + 1) * 30 * 24 * 3600;
                    $toDate = $_time - $i * 30 * 24 * 3600;
                    $params = array(
                        'seller_id' => $shop['seller_id'],
                        'shop_id' => $shop['shop_id'],
                        'AccountID' => $shop['AccountID'],
                        'site_id' => $shop['site_id'],
                        'token' => $shop['token'],
                        'start_time' => $fromDate,
                        'end_time' => $toDate,
                        'priority' => 20 - $i
                    );
                    ReturnUpdateQueueDAO::getInstance()->insert($params);
                }
                
                $columns = array(
                    'return_update_time' => $_time
                );
                $conditions = 'shop_id=:shop_id';
                $params = array(
                    ':shop_id' => $shop['shop_id']
                );
                ShopDAO::getInstance()->iupdate($columns, $conditions, $params);
            }
            
            ReturnUpdateQueueDAO::getInstance()->commit();
            return true;
        } catch (Exception $e) {
            ReturnUpdateQueueDAO::getInstance()->rollback();
            return false;
        }
    }

    /**
     * @desc 运行下载队列
     * @author liaojianwen
     * @date 2015-06-14
     * @return boolean
     */
    public function executeReturnUpdateQueue()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $startTime = time();
        
        label1:
        
        if (time() - $startTime > 290) {
            return;
        }
        
        $pagesize = 200;
        
        $Queues = ReturnUpdateQueueDAO::getInstance()->getUpdateQueueData(EnumOther::RETURN_EXECUTESIZE);
        if ($Queues !== false) {
            foreach ($Queues as $key => $Queue) {
                $page = 0;
                while (true) {
                    $page ++;
                    
                    if ($page > 100) {
                        
                        // 发送邮件通知
                        ob_start();
                        echo __METHOD__;
                        echo "\n";
                        var_dump($Queue);
                        $text = ob_get_clean();
                        $subject = "Fatal error: page > 100";
                        $to = Yii::app()->params['logmails'];
                        SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
                        
                        break;
                    }
                    
                    $xmldata = array();
                    $xmldata['Returns'] = ReturnDownModel::model()->getUserReturns($Queue['start_time'], $Queue['end_time'], '', '', '', 
                        $Queue['token'], $Queue['site_id'], $page, $pagesize);
                    
                    $doc = phpQuery::newDocumentXML($xmldata['Returns']);
                    phpQuery::selectDocument($doc);
                    if (pq('ack') == 'Failure') {
                        continue 2;
                    }
                    
                    $returns = pq('returns');
                    $length = $returns->length;
                    
                    if (! $length) {
                        ReturnUpdateQueueDAO::getInstance()->deleteByPk($Queue['return_update_queue_id']);
                        break;
                    }
                    
                    for ($i = 0; $i < $length; $i ++) {
                        $return_id = pq('ReturnId>id')->eq($i)->html();
                        if ($return_id !== false) {
                            $runcount = 0;
                            label:
                            
                            $xmldata['ReturnDetail'][$return_id] = ReturnDownModel::model()->getReturnDetailInfo($return_id, $Queue['token']);
                            
                            if (empty($xmldata['ReturnDetail'][$return_id])) {
                                $runcount ++;
                                if ($runcount > 3) {
                                    goto label2;
                                }
                                goto label;
                            }
                            
                            label2:
                            
                            // $xmldata['ActivityOptions'][$return_id] = ReturnDownModel::model()->getActivityOptions($return_id, $Queue['token'], $Queue['site_id']);
                            FileLog::getInstance()->write(
                                EnumOther::LOG_DIR_RETURN_TEMP_FILE_DATA . gmdate('/Y/m/d/') . EnumOther::LOG_DIR_RETURN_TEMP_UPDATE_TAG, 
                                md5($return_id), ReturnDownModel::model()->getFileData($return_id, $Queue['token']));
                            // $xmldata['FileData'][$return_id] = ReturnDownModel::model()->getFileData($return_id, $Queue['token']);
                            $xmldata['FileData'][$return_id] = gmdate('/Y/m/d/') . EnumOther::LOG_DIR_RETURN_TEMP_UPDATE_TAG;
                        }
                    }
                    $columns = array(
                        'seller_id' => $Queue['seller_id'],
                        'shop_id' => $Queue['shop_id'],
                        'AccountID' => $Queue['AccountID'],
                        'text_json' => base64_encode(serialize($xmldata)),
                        'create_time' => time()
                    );
                    
                    ReturnDownDAO::getInstance()->begintransaction();
                    
                    try {
                        $lid = ReturnDownDAO::getInstance()->iinsert($columns);
                        if ($lid !== false) {
                            ReturnUpdateQueueDAO::getInstance()->deleteByPk($Queue['return_update_queue_id']);
                            ReturnDownDAO::getInstance()->commit();
                        } else {
                            ReturnDownDAO::getInstance()->rollback();
                        }
                    } catch (Exception $e) {
                        ReturnDownDAO::getInstance()->rollback();
                    }
                    unset($columns);
                    if ($page >= pq('paginationOutput>totalPages')->html()) {
                        continue 2;
                    }
                    
                    if (pq('errorMessage>error>errorId') > 0) {
                        iMongo::getInstance()->setCollection('getUserRetrunsErrB')->insert(
                            array(
                                'xml' => $xmldata['Returns'],
                                'time' => time()
                            ));
                        break;
                    }
                }
            }
            
            goto label1;
        } else {
            DaemonLockTool::lock(__METHOD__ . 'one');
            sleep(5);
            goto label1;
        }
    }
}
