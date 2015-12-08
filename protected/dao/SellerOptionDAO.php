<?php

/**
 * @desc seller_available_option主表
 * @author liaojianwen
 * @date 2015-06-26
 */
class SellerOptionDAO extends BaseDAO
{
    
    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-06-26
     * @return SellerOptionDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-06-26
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'seller_available_option';
        $this->primaryKey = 'seller_available_option_id';
        $this->return = 'return_request';
        $this->created = 'create_time';
        
        $this->shop = 'shop';
    }
    
    /**
     * @desc 获取下一步操作
     * @param int $returnid seller_available_option 外键
     * @param  string $sellerId
     * @author liaojianwen
     * @date 2015-07-06
     * @return array
     */
    public function getSellerOptions($returnid,$sellerId)
    {
        $conditions = "o.return_id ={$returnid} and s.seller_id = {$sellerId}";
        $result = $this->dbCommand->reset()
                                  ->select('actionType,actionURL')
                                  ->from("{$this->tableName} o")
                                  ->join("{$this->return} r" ,"r.return_request_id = o.return_id")
                                  ->join("{$this->shop} s","s.shop_id = r.shop_id")
                                  ->where($conditions)
                                  ->queryAll();
        return $result;
                                  
        
    
    }
}