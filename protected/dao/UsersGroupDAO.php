<?php

/**
 * @desc users_group 表操作类
 * @author YangLong
 * @date 2015-08-20
 */
class UsersGroupDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-08-20
     * @return UsersGroupDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-08-20
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'users_group';
        $this->primaryKey = 'users_group_id';
        // $this->created = 'create_time';
    }
}