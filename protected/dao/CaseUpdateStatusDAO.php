<?php

/**
 * @desc Case状态更新队列表/case_update_status
 * @author YangLong
 * @date 2015-05-26
 */
class CaseUpdateStatusDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-05-26
     * @return CaseUpdateStatusDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-05-26
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'case_update_status';
        $this->primaryKey = 'case_update_status_id';
        $this->created = 'create_time';
    }

    /**
     * @desc 获取Case状态原始XML数据
     * @author YangLong
     * @date 2015-05-26
     * @return mixed
     */
    public function getCasesStatus($picksize = 10)
    {
        $conditions = 'process_sign=:process_sign';
        $params = array(
            ':process_sign' => boolConvert::toInt01(false)
        );
        return $this->dbCommand->reset()
            ->select('case_update_status_id,dataxml')
            ->from($this->tableName)
            ->where($conditions, $params)
            ->order($this->primaryKey)
            ->limit($picksize)
            ->queryAll();
    }

}