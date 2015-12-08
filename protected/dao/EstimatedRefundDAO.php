<?php

/**
 * @desc estimated_refund主表
 * @author liaojianwen
 * @date 2015-06-17
 */
class EstimatedRefundDAO extends BaseDAO
{
    
    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-06-17
     * @return EstimatedRefundDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-006-17
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'estimated_refund';
        $this->primaryKey = 'estimated_refund_id';
        $this->return = 'return_request';
        $this->detail = 'return_request_detail';
        $this->created = 'create_time';
        
        $this->shop = 'shop';
    }
    
    
    
    /**
     * @desc 获取退款信息
     * @param $return_id 
     * @param $sellerId
     * @author liaojianwen
     * @date 2015-06-29
     */
    public function getItemizedRefundDetail($return_id,$sellerId)
    {
        $selects = 'd.S_sTR_estimatedRefundAmount,d.S_sTR_currencyId';
        $conditions = "d.return_id={$return_id} and s.seller_id = {$sellerId}";
        $total = $this->dbCommand->reset()
                        ->select($selects)
                        ->from("{$this->return} r")
                        ->join("{$this->detail} d","d.return_id = r.return_request_id")
                        ->join("{$this->shop} s","r.shop_id = s.shop_id")
                        ->where($conditions)
                        ->limit(1)
                        ->queryRow();
        $selects = 'e.refundFeeType,e.estimatedAmount,e.currencyId,e.restockingFeePercentage';
        $conditions = "r.return_request_id={$return_id} and s.seller_id = {$sellerId}";
        $detail = $this->dbCommand->reset()
                        ->select($selects)
                        ->from("{$this->tableName} e")
                        ->join("{$this->return} r","e.return_id = r.return_request_id")
                        ->join("{$this->shop} s","r.shop_id = s.shop_id")
                        ->where($conditions)
                        ->queryAll();
        foreach ($detail as $value){
            $itemizedRefundDetail[] = array(
                    'refundFeeType'=>$value['refundFeeType'] ,
                    'amount' =>array('value' =>$value['estimatedAmount'],'currencyId'=>$value['currencyId']),
                    'restockingFeePercentage'=>$value['restockingFeePercentage']
            ); 
        }               
       $result = array(
           'totalAmount' => array(
                               'value'=>$total['S_sTR_estimatedRefundAmount'],
                               'currencyId'=> $total['S_sTR_currencyId']
                           ),
           'itemizedRefundDetail'=> $itemizedRefundDetail
       );
       return $result;
    
    }
    
}