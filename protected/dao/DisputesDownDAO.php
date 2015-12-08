<?php

/**
 * @desc disputes_down表操作类
 * @author YangLong
 * @date 2015-08-13
 */
class DisputesDownDAO extends BaseDAO
{
    
    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-08-13
     * @return DisputesDownDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }
    
    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-06-13
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'disputes_down';
        $this->primaryKey = 'disputes_down_id';
        $this->created = 'create_time';
    }
    
    /**
     * @desc 获取已经下载的ruturn数据
     * @param int $taskNumber
     * @author liaojianwen
     * @date 2015-08-18
     * @return Ambigous <multitype:, mixed>
     */
    public function getDownloadDisputes($taskNumber)
    {
        $this->dbCommand->reset();
        $conditions = '(process_sign=:sign or (picktime<:picktime and pickcount<=:pickcount)) and type_flag = :type_flag';
        $params = array(
            ':sign' => boolConvert::toInt01(false),
            ':picktime' => time() - EnumOther::DISPUTE_DATA_REPICK_TIME,
            ':pickcount' => EnumOther::DISPUTE_DATA_MAX_PICK_COUNT,
            ':type_flag' => 'list'
        );
        $result = $this->dbCommand->select('disputes_down_id,shop_id,data_xml,type_flag')
            ->from($this->tableName)
            ->where($conditions, $params)
            ->limit($taskNumber)
            ->order('disputes_down_id asc')
            ->queryAll();
        return $result;
    }
}