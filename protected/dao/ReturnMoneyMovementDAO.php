<?php

/**
 * @desc case_money_movement操作类
 * @author liaojianwen1
 * @date 2015-07-22
 */
class ReturnMoneyMovementDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-07-22
     * @return ReturnMoneyMovementDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-07-22
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'return_money_movement';
        $this->primaryKey = 'return_money_movement_id';
        $this->created = 'create_time';
        $this->return = 'return_request';
        $this->shop = 'shop';
    }
    
    /**
     * @desc 获取退款号
     * @param $returnid
     * @param $sellerId
     * @author liaojianwen
     * @date 2015-07-22
     */
    public function getMoneyMovement($returnid,$sellerId)
    {
        $selects = 'm.externalPaymentTrxnId,m.externalPaymentTrxnType,m.creationDate,m.status';
        $conditions = "m.return_id= {$returnid} and s.seller_id ={$sellerId}";
        $result = $this->dbCommand->reset()
                                  ->select($selects)
                                  ->from("{$this->tableName} m")
                                  ->join("{$this->return} r","r.return_request_id = m.return_id")
                                  ->join("{$this->shop} s","s.shop_id=r.shop_id")
                                  ->where($conditions)
                                  ->queryAll();
        return $result;
    }
}