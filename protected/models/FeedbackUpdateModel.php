<?php

/**
 * @desc Feedback更新/下载处理类
 * @author liaojianwen
 * @date 2015-05-18
 */
class FeedbackUpdateModel extends BaseModel
{
    
    /**
     * @desc 覆盖父方法返回FeedbackUpdateModel对象(单)实例
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-05-18
     * @return FeedbackUpdateModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
    
    /**
     * @desc 生成feedback 下载队列
     * @author liaojianwen
     * @date 2015-05-19
     */
    public function generateFeedbackUpdateQueue()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $shops = MsgDownDAO::getInstance()->getEbShop('feedback');
        FeedbackUpdateQueueDAO::getInstance()->begintransaction();
        try {
            foreach ($shops as $key => $shop) {
                $conditions = 'accountid=:AccountID';
                $params = array(
                    ':AccountID' => $shop['AccountID']
                );
                FeedbackUpdateQueueDAO::getInstance()->idelete($conditions, $params);
                
                $_time = time();
                $columns = array(
                    'accountid' => $shop['AccountID'],
                    'seller_id' => $shop['seller_id'],
                    'shop_id' => $shop['shop_id'],
                    'site_id' => $shop['site_id'],
                    'token' => $shop['token'],
                    'create_time' => $_time
                );
                
                FeedbackUpdateQueueDAO::getInstance()->iinsert($columns);
                
                $columns = array(
                    'feedback_down_time' => $_time
                );
                $conditions = 'shop_id=:shop_id';
                $params = array(
                    ':shop_id' => $shop['shop_id']
                );
                ShopDAO::getInstance()->iupdate($columns, $conditions, $params);
            }
            
            FeedbackUpdateQueueDAO::getInstance()->commit();
            return true;
        } catch (Exception $e) {
            FeedbackUpdateQueueDAO::getInstance()->rollback();
            return false;
        }
    }
    
    /**
     * @desc 执行feedback的下载队列
     * @author liaojianwen
     * @date 2015-05-19
     * @return boolean
     */
    public function executeFeedbackUpdateQueue()
    {
         DaemonLockTool::lock(__METHOD__);
        
        $Queues = FeedbackUpdateQueueDAO::getInstance()->getFeedbackUpdateQueueData(EnumOther::DOWN_EXECUTESIZE);
        if ($Queues !== false) {
            $page = CRedisHelper::getInstance()->get('IMS_FEEDBACK_PAGE');
            if (empty($page)) {
                $page = 0;
            }
            $i = 0;
            foreach ($Queues as $key => $value) {
                $id = intval(substr($page, strpos($page, '_') + 1));
                if ($id == $value['feedback_update_queue_id']) {
                    $page = intval(substr($page, 0, strpos($page, '_')));
                } else {
                    $page = 0;
                }
                while (true) {
                    $page ++;
                    $i ++;
                    $xmldata = FeedbackDownModel::model()->getFeedback($value['token'], $value['site_id'], $page, 100);
                    $params = array(
                        'seller_id' => $value['seller_id'],
                        'shop_id' => $value['shop_id'],
                        'text_json' => $xmldata
                    );
                    FeedbackDownDAO::getInstance()->begintransaction();
                    try {
                        $lids = FeedbackDownDAO::getInstance()->insert($params);
                        if ($lids !== false) {
                            FeedbackDownDAO::getInstance()->commit();
                        } else {
                            FeedbackDownDAO::getInstance()->rollback();
                        }
                    } catch (Exception $e) {
                        FeedbackDownDAO::getInstance()->rollback();
                    }
                    $doc = phpQuery::newDocumentXML($xmldata);
                    phpQuery::selectDocument($doc);
                    if ((integer)$doc['PaginationResult>TotalNumberOfPages']->html() <= $page) {
                        $conditions = 'feedback_update_queue_id = :feedback_update_queue_id';
                        $params = array(
                            ':feedback_update_queue_id' => $value['feedback_update_queue_id']
                        );
                        FeedbackUpdateQueueDAO::getInstance()->idelete($conditions, $params); 
                        break;
                    }
                    if ($i === 4) {
                        CRedisHelper::getInstance()->set('IMS_FEEDBACK_PAGE', $page . '_' . $value['feedback_update_queue_id'], 3600);
                        $columns = array(
                            'process_sign' => 0
                        );
                        
                        $conditions = array(
                            'feedback_update_queue_id' => $value['feedback_update_queue_id']
                        );
                        FeedbackUpdateQueueDAO::getInstance()->update($conditions, $columns);
                        break;
                    }
                }
            }
        } else {
            return false;
        }
    }
}