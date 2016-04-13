<?php

/**
 * @desc Case下载存储表操作类/case_down
 * @author YangLong
 * @date 2015-03-27
 */
class CaseDownDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-03-27
     * @return CaseDownDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-03-27
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'case_down';
        $this->primaryKey = 'down_id';
        $this->created = 'create_time';
    }

    /**
     * @desc 获取已经下载的Case数据
     * @param int $taskNumber
     * @author YangLong
     * @date 2015-03-30
     * @return Ambigous <multitype:, mixed>
     */
    public function getDownloadData($taskNumber)
    {
        $this->dbCommand->reset();
        $conditions = 'status=:status or (pick_time<:pick_time and pick_count<=:pick_count)';
        $params = array(
            ':status' => boolConvert::toInt01(false),
            ':pick_time' => time() - EnumOther::CASE_DATA_REPICK_TIME,
            ':pick_count' => EnumOther::CASE_DATA_MAX_PICK_COUNT
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
