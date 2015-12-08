<?php

/**
 * @desc shop_ref_users table Data Access Object
 * @author YangLong
 * @date 2015-09-14
 */
class ShopRefUsersDAO extends BaseDAO
{
    
    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-09-14
     * @return ShopRefUsersDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }
    
    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-09-14
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'shop_ref_users';
        $this->primaryKey = 'shop_id,user_id';
        // $this->created = '';
    }
    
}
