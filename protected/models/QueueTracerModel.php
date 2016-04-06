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
        
        $mongoConfig = Yii::app()->params['mongodb_conn'];
        if (isset($mongoConfig['default']) && ! empty($mongoConfig['default']['ip']) && ! empty($mongoConfig['default']['port'])) {
            $conStr = 'mongodb://' . $mongoConfig['default']['ip'] . ':' . $mongoConfig['default']['port'];
        } else {
            throw new Exception('Monogodb config error.');
        }
        
        $mongo = new MongoClient($conStr, array(
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
