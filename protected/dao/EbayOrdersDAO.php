<?php

/**
 * @desc ebay_orders表操作类
 * @author YangLong
 * @date 2015-06-13
 */
class EbayOrdersDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-06-13
     * @return EbayOrdersDAO
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
        $this->tableName = 'ebay_orders';
        $this->primaryKey = 'ebay_orders_id';
        $this->created = 'create_time';
        $this->shop = 'shop';
    }
    
    /**
     * @desc 获取退款信息
     * @param string  $orderId_id
     * @param string  $sellerId
     * @author liaojianwen
     * @date 2015-07-17
     */
    public function getOrdersRefund($orderId_id,$sellerId)
    {
       $refund = array();
        $conditions = "o.OrderID='{$orderId_id}' and s.seller_id = {$sellerId}";
    	$result = $this->dbCommand->reset()
    	            ->select('MonetaryDetailsXML')
    	            ->from("{$this->tableName} o")
    	            ->join("{$this->shop} s","o.shop_id = s.shop_id")
    	            ->where($conditions)
    	            ->queryScalar();
    	            
    	            $doc = phpQuery::newDocumentXML($result);
                    phpQuery::selectDocument($doc);
                    $refund_length = $doc['>Refunds>Refund']->length();
                    $_refund = $doc['>Refunds>Refund'];
                    for ($j=0; $j < $refund_length; $j++){
                      $refund[] = array(
                            'RefundStatus'=>$_refund->eq($j)->find('>RefundStatus')->html(),
                            'RefundType'=>$_refund->eq($j)->find('>RefundType')->html(),
                            'RefundTo'=>$_refund->eq($j)->find('>RefundTo')->html(),
                            'RefundTime'=>strtotime($_refund->eq($j)->find('>RefundTime')->html()),
                            'RefundAmount'=>$_refund->eq($j)->find('>RefundAmount')->html(),
                            'RcurrencyID'=>$_refund->eq($j)->find('>RefundAmount')->attr('currencyID'),
                            'ReferenceID'=>$_refund->eq($j)->find('>ReferenceID')->html(),
                            'FeeOrCreditAmount'=>$_refund->eq($j)->find('>FeeOrCreditAmount')->html(),
                            'FcurrencyID'=>$_refund->eq($j)->find('>FeeOrCreditAmount')->attr('currencyID')
                        );
                    }
         return $refund;
    }
    
    /**
     * @desc 获取订单信息列表 
     * @param int $page
     * @param int $pageSize
     * @param string $cust
     * @param string $shopId
     * @author liaojianwen
     * @date 2015-10-31
     */
    public function getUpaidOrderList($page,$pageSize,$cust,$shopId)
    {
        $limit = $pageSize;
        $offset = ($page - 1) * $limit;
        $selections = 'o.ebay_orders_id,o.OrderID,o.CreatedTime,o.Subtotal,o.Subtotal_currencyID,o.AddressID,o.BuyerUserID,o.EIASToken,t.OrderLineItemID,
            o.ShippingService,o.ShippingServiceCost,o.ShippingServiceCost_currencyID,o.AdjustmentAmount,o.AdjustmentAmount_currencyID,
            o.Total,o.Total_currencyID,t.QuantityPurchased,t.TransactionPrice,t.TransactionPrice_currencyID,t.VariationSpecificsXML,t.ProductName,
            l.gallery_url,s.nick_name,l.item_id,l.title,i.send_count,i.last_send_time,s.site_id';
        $time_range = time() - 30*24*60*60;
        $conditions = "o.OrderStatus =:status and o.shop_id in ({$shopId})";
       
        $params = array(
            ':status' => 'Active'
        );
        if (! empty($cust)) {
            $conditions .= " and o.BuyerUserID like :cust";
            $params[':cust'] = '%' . $cust . '%';
        }
        $result['list'] = $this->dbCommand->reset()
            ->select($selections, "SQL_CALC_FOUND_ROWS")
            ->from("ebay_orders o")
            ->join("ebay_order_transaction t", "o.ebay_orders_id = t.ebay_orders_id")
            ->join("ebay_listing l", "l.item_id = t.Item_ItemID")
            ->join("shop s", "s.shop_id = o.shop_id")
            ->leftJoin("invoices_count i","i.ebay_orders_id = o.ebay_orders_id")
            ->where($conditions, $params)
            ->andWhere("o.CreatedTime > '{$time_range}'")
            ->order('o.CreatedTime DESC')
            ->limit($limit, $offset)
            ->queryAll();
        foreach ($result['list'] as &$value) {
            $specifics = array();
            $doc = phpQuery::newDocumentXML($value['VariationSpecificsXML']);
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
     * @desc 获取订单明细
     * @param string $order_Id 订单ID
     * @author liaojianwen
     * @date 2015-11-01
     * @return Ambigous <multitype:, mixed>
     */
    public function getOrderTransaction($order_Id)
    {
        $selections = 't.Item_ItemID,t.Item_Title,Item_SKU,QuantityPurchased,TransactionPrice,TransactionPrice_currencyID,
            o.Total,o.Total_currencyID,t.VariationSpecificsXML,t.ProductName,o.ShippingDetailsXML,o.ShippingService';
        $conditions = "o.OrderID=:orderid";
        $params = array(
            ':orderid'=>$order_Id
        );
        $result = $this->dbCommand->reset()
            ->select($selections)
            ->from("ebay_orders o")
            ->join("ebay_order_transaction t", "o.ebay_orders_id = t.ebay_orders_id ")
            ->where($conditions,$params)
            ->order('t.CreatedDate DESC')
            ->queryAll();
        return $result;
        
    }
}