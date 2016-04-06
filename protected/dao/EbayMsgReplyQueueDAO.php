<?php

/**
 * @desc eb_msg_reply_queue 表DAO
 * @author YangLong
 * @date 2015-04-27
 */
class EbayMsgReplyQueueDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-04-27
     * @return EbayMsgReplyQueueDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-04-20
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'eb_msg_reply_queue';
        $this->primaryKey = 'down_queue_id';
        $this->created = 'create_time';
    }

    /**
     * @desc 获取回复队列数据
     * @param int $taskNumber
     * @author YangLong
     * @date 2015-04-20
     * @return mixed
     */
    public function getReplyQueueData($taskNumber)
    {
        $conditions = 'process_sign=' . boolConvert::toInt01(false);
        $result = $this->dbCommand->reset()
            ->select('down_queue_id,msg_id,token,ExternalMessageID,Sender,content,imgUrl,copy')
            ->from($this->tableName)
            ->where($conditions)
            ->limit($taskNumber)
            ->queryAll();
        if (empty($result)) {
            return false;
        }
        $_tmp = array();
        foreach ($result as $value) {
            $_tmp[] = $value['down_queue_id'];
        }
        $_tmp = implode(',', $_tmp);
        $columns = array(
            'process_sign' => boolConvert::toInt01(true)
        );
        $conditions = "down_queue_id in ({$_tmp})";
        $params = array();
        $this->dbCommand->reset()->update($this->tableName, $columns, $conditions, $params);
        return $result;
    }
}
