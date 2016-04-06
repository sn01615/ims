<?php

/**
 * @desc ck1_trackings 表操作类
 * @author YangLong
 * @date 2015-11-02
 */
class Ck1TrackingsDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-11-02
     * @return Ck1TrackingsDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-11-02
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'ck1_trackings';
        $this->primaryKey = 'ck1_trackings_id';
        // $this->created = 'create_time';
        // $this->updated = 'update_time';
    }
}
