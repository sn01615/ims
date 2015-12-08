<?php

/**
 * @desc ebay_order_transaction表操作类
 * @author YangLong
 * @date 2015-05-05
 */
class EbayOrderTransactionDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-05-04
     * @return EbayOrderTransactionDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-05-04
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'ebay_order_transaction';
        $this->primaryKey = 'ebay_order_transaction_id';
        $this->order = 'ebay_orders';
        $this->created = 'create_time';
        $this->shop = 'shop';
        $this->listing = 'ebay_listing';
    }
    
    /**
     * @desc 通过orderLineItemId 获取productName
     * @param $orderLineItemId
     * @param $sellerId
     * @author liaojianwen
     * @date 2015-07-10
     */
    public function getProductName($orderLineItemId, $sellerId)
    {
        $conditions = "o.OrderLineItemID = '{$orderLineItemId}' and s.seller_id = {$sellerId}";
        $res = $this->dbCommand->reset()
            ->select('ProductName')
            ->from("{$this->tableName} o")
            ->join("{$this->shop} s", "s.shop_id = o.shop_id")
            ->where($conditions)
            ->queryScalar();
        $result['productName'] = html_entity_decode(html_entity_decode($res));
        return $result;
    }
    
    /**
     * @desc 新增case 时查找订单信息
     * @param $ItemID
     * @param $BuyerUserID
     * @param $shop_id
     * @author liaojianwen
     * @date 2015-07-14
     */
    public function searchOrder($ItemID, $BuyerUserID, $shop_id, $page, $pageSize,$rangeTime)
    {
        $limit = $pageSize;
        $offset = ($page - 1) * $limit;
        $selections ="TransactionID,Item_ItemID ItemID,t.Item_SKU,t.Variation_SKU,QuantityPurchased,o.OrderID,o.BuyerUserID ,o.CreatedTime created_time,t.ActualShippingCost,t.ActualShippingCost_currencyID,t.VariationSpecificsXML
            ,t.OrderLineItemID,l.gallery_url,t.TransactionPrice,t.TransactionPrice_currencyID,CustomLabel,t.ProductName";
        $conditions = "t.shop_id = :shop_id";
        $params[':shop_id'] = $shop_id;
        if (! empty($ItemID)) {
            $conditions .= " and t.Item_ItemID like :itemID";
            $params[':itemID'] = '%' . $ItemID . '%';
        }
        if (! empty($BuyerUserID)) {
            $conditions .= " and o.BuyerUserID like :BuyerID";
            $params[':BuyerID'] = '%' . $BuyerUserID . '%';
        }
        $result['list'] = $this->dbCommand->reset()
                               ->select($selections,'SQL_CALC_FOUND_ROWS')
                               ->from("{$this->tableName} t")
                               ->join("{$this->order} o","o.ebay_orders_id = t.ebay_orders_id")
                               ->join("{$this->listing} l","l.item_id = t.Item_ItemID and l.shop_id = t.shop_id")
                               ->where($conditions,$params)
//                                ->andwhere("l.listing_status <>'Ended'")
                               ->andwhere("o.CreatedTime > '{$rangeTime}'")
                               ->order("o.CreatedTime DESC")
                               ->limit($limit, $offset)
                               ->queryAll();
        foreach($result['list'] as &$value){
            $specifics = array();
            $doc = phpQuery::newDocumentXML($value['VariationSpecificsXML']);
            phpQuery::selectDocument($doc);
            $varition = pq('NameValueList ');
            $length = $varition->length;
            for($i=0; $i< $length; $i++){
                $name = $varition->eq($i)->find('Name')->html();
                $res = $varition->eq($i)->find('Value')->html();
                $specifics[$name] = $res;
            }
            $value['VariationSpecifics'] = $specifics;
        }
        $result['count'] = $this->dbCommand->reset()
            ->setText('select found_rows()')
            ->queryScalar();
        $result['page'] = array(
            'page' => $page,
            'pagesize' => $pageSize
        );
        return $result;
    }
    
    /**
     * @desc  评价中获取订单信息
     * @param string $orderLineItemID
     * @param string $shopId
     * @author liaojianwen
     * @date 2015-09-15
     * @return mixed
     */
    public function getFeedbackOrder($orderLineItemID, $shopId)
    {
        $selections = "t.ProductName,t.CustomLabel,QuantityPurchased,o.CreatedTime, t.TransactionPrice,t.TransactionPrice_currencyID,
            t.VariationSpecificsXML,t.ActualShippingCost,t.ActualShippingCost_currencyID";
        $conditions = "t.OrderLineItemID = '{$orderLineItemID}' and t.shop_id in ({$shopId})";
        $result = $this->dbCommand->reset()
            ->select($selections)
            ->from("{$this->tableName} t")
            ->join("{$this->order} o", "o.ebay_orders_id = t.ebay_orders_id")
            ->where($conditions)
            ->limit(1)
            ->queryRow();
        $specifics = array();
        if (! empty($result['VariationSpecificsXML'])) {
            $doc = phpQuery::newDocumentXML($result['VariationSpecificsXML']);
            phpQuery::selectDocument($doc);
            $varition = pq('NameValueList ');
            $length = $varition->length;
            for ($i = 0; $i < $length; $i ++) {
                $name = $varition->eq($i)
                    ->find('Name')
                    ->html();
                $res = $varition->eq($i)
                    ->find('Value')
                    ->html();
                $specifics[$name] = $res;
            }
            $result['VariationSpecifics'] = $specifics;
        }
        return $result;
    }
}