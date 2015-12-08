<?php

/**
 * @desc Return request下载存储表操作类/return_request_down
 * @author liaojianwen
 * @date 2015-06-15
 */
class ReturnDownDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-06-15
     * @return ReturnDownDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-06-15
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'return_request_down';
        $this->primaryKey = 'down_id';
        $this->created = 'create_time';
    }

    /**
     * @desc 获取已经下载的ruturn数据
     * @param int $taskNumber
     * @author liaojianwen
     * @date 2015-06-15
     * @return Ambigous <multitype:, mixed>
     */
    public function getDownloadData($taskNumber)
    {
        $this->dbCommand->reset();
        $conditions = 'status=:status or (lastruntime<:lastruntime and runcount<=:runcount)';
        $params = array(
            ':status' => boolConvert::toInt01(false),
            ':lastruntime' => time() - EnumOther::RETURN_DATA_REPICK_TIME,
            ':runcount' => EnumOther::RETURN_DATA_MAX_PICK_COUNT
        );
        $result = $this->dbCommand->select('down_id,seller_id,shop_id,AccountID,text_json')
            ->from($this->tableName)
            ->where($conditions, $params)
            ->limit($taskNumber)
            ->order('down_id asc')
            ->queryAll();
        return $result;
    }
}