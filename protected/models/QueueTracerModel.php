<?php

/**
 * @desc 任务运行类
 * @author YangLong
 * @desc 2015-08-12
 */
class QueueTracerModel extends MongoJob
{

    public static $context;

    public static function trace()
    {
        $parameters = func_get_args();
        
        $result = false;
        
        if (isset($parameters[0])) {
            if ($parameters[0]['queue_type'] == 'DisputesDownQueue') {
                $result = DisputesModel::model()->executeDisputesDownQueue($parameters[0]);
            } elseif ($parameters[0]['queue_type'] == 'ContactMsgUploadQueue') {
                $result = EbayFeedbackTransactionModel::model()->executeContactMsgUploadQueue($parameters[0]);
            }
        }
        
        return $result;
    }
    
    /**
     * @desc 守护进程
     * @author YangLong
     * @date 2015-08-14
     */
    public static function daemon()
    {
        DaemonLockTool::lock(__METHOD__ . (int) (gmdate('i') / 30));
        
        $startTime = time();
        
        $mongo = new MongoClient('mongodb://127.0.0.1:27017', array(
            'connect' => false
        ));
        MongoQueue::$connection = $mongo;
        MongoQueue::$database = 'ImsMongoQueue';
        
        while (true) {
            $result = MongoQueue::run();
            if (! $result) {
                sleep(5);
            }
            if (time() - $startTime > 600) {
                break;
            }
        }
    }
    
}
