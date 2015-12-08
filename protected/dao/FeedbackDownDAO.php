<?php

/**
 * @desc feedback下载存储表操作类/feedback_down
 * @author liaojianwen
 * @date 2015-05-18
 */
class FeedbackDownDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-03-27
     * @return FeedbackDownDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-05-18
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'feedback_down';
        $this->primaryKey = 'down_id';
        $this->created = 'create_time';
    }

    /**
     * @desc 获取已经下载的feedback数据
     * @param int $taskNumber
     * @author liaojianwen
     * @date 2015-05-18
     * @return Ambigous <multitype:, mixed>
     */
    public function getFeedbackDownData($taskNumber)
    {
        $this->dbCommand->reset();
        $conditions = 'status=:status or (pick_time<:pick_time and pick_count<=:pick_count)';
        $params = array(
            ':status' => boolConvert::toInt01(false),
            ':pick_time' => time() - EnumOther::FEEDBACK_DATA_REPICK_TIME,
            ':pick_count' => EnumOther::FEEDBACK_DATA_MAX_PICK_COUNT
        );
        $result = $this->dbCommand->select('down_id,seller_id,shop_id,text_json')
            ->from($this->tableName)
            ->where($conditions, $params)
            ->limit($taskNumber)
            ->order('down_id asc')
            ->queryAll();
        return $result;
    }
}