<?php

/**
 * @desc ebay_shipping_service_details操作类
 * @author YangLong
 * @date 2015-07-23
 */
class EbayShippingServiceDetailsDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-07-23
     * @return EbayShippingServiceDetailsDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-07-23
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'ebay_shipping_service_details';
        $this->primaryKey = 'ebay_shipping_service_details_id';
        $this->created = 'create_time';
    }

    /**
     * @desc 获取物流服务
     * @param string $ebay_orders_id
     * @param string $shopId <1,2,3,4...>
     * @author liaojianwen
     * @date 2015-11-2
     * @return Ambigous <multitype:, mixed>
     */
    public function getEbayShipService($site_id, $flag)
    {
        $selections = 'ShippingService,Description,ShippingCarrier,ValidForSellingFlow,InternationalService';
        $conditions = "site_id =:site_id  and ValidForSellingFlow=:valid";
        $params = array(
            ':site_id' => $site_id,
            ':valid' => '1'
        );
        if ($flag == 1) {
            $InternationalService = '0';
            $conditions .= " and InternationalService=:InternationalService";
            $params[':InternationalService'] = $InternationalService;
        } elseif ($flag == 2) {
            $InternationalService = '1';
            $conditions .= " and InternationalService=:InternationalService";
            $params[':InternationalService'] = $InternationalService;
        } else {}
        
        $result = $this->dbCommand->reset()
            ->select($selections)
            ->from("ebay_shipping_service_details ")
            ->where($conditions, $params)
            ->queryAll();
        return $result;
    }
}