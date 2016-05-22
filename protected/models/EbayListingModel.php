<?php

/**
 * @desc listing更新处理类
 * @author liaojianwen
 * @date 2015-07-27
 */
class EbayListingModel extends BaseModel
{

    /**
     * @desc 覆盖父方法返回EbayListingModel对象(单)实例
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-07-27
     * @return EbayListingModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @desc 获取listing信息 队列
     * @author liaojianwen
     * @date 2015-07-28
     */
    public function generateEbayListingQueue()
    {
        $shops = MsgDownDAO::getInstance()->getEbShop('listing');
        EbayListingQueueDAO::getInstance()->begintransaction();
        try {
            $i = 125;
            foreach ($shops as $key => $shop) {
                $conditions = 'account_id=:AccountID';
                $params = array(
                    ':AccountID' => $shop['AccountID']
                );
                EbayListingQueueDAO::getInstance()->idelete($conditions, $params);
                $_time = time();
                $fromDate = $_time - 3600 * 24 * 2; // 提前2天开始下载
                $toDate = $_time + 3600 * 24 * 30; // 30天内的数据
                $params = array(
                    'seller_id' => $shop['seller_id'],
                    'shop_id' => $shop['shop_id'],
                    'site_id' => $shop['site_id'],
                    'account_id' => $shop['AccountID'],
                    'token' => $shop['token'],
                    'start_time' => $fromDate,
                    'end_time' => $toDate,
                    'create_time' => time(),
                    'priority' => $i --
                );
                $res = EbayListingQueueDAO::getInstance()->insert($params);
                $columns = array(
                    'listing_down_time' => $_time
                );
                $conditions = 'shop_id=:shop_id';
                $params = array(
                    ':shop_id' => $shop['shop_id']
                );
                ShopDAO::getInstance()->iupdate($columns, $conditions, $params);
            }
            EbayListingQueueDAO::getInstance()->commit();
            return true;
        } catch (Exception $e) {
            EbayListingQueueDAO::getInstance()->rollback();
            return false;
        }
    }

    /**
     * @desc 执行队列下载ebay listing 数据
     * @author liaojianwen
     * @date 2015-07-28
     */
    public function executeEbayListingQueue()
    {
        $Queues = EbayListingQueueDAO::getInstance()->getListingQueueData(1);
        if ($Queues !== false) {
            foreach ($Queues as $key => $Queue) {
                $page = 0;
                while (true) {
                    $page ++;
                    label:
                    $listingData = EbayListingDownModel::model()->getEbayListing($Queue['token'], $Queue['start_time'], $Queue['end_time'], 
                        $Queue['site_id'], $page, 25);
                    $doc = phpQuery::newDocumentXML($listingData);
                    phpQuery::selectDocument($doc);
                    $runcount = 0;
                    if (strtolower(pq('Ack')->html()) != 'success') {
                        
                        iMongo::getInstance()->setCollection('getEbayListingErrA')->insert(
                            array(
                                'status' => 'errA',
                                'xml' => $listingData,
                                'time' => time()
                            ));
                        
                        $runcount ++;
                        if ($runcount < 3) {
                            $columns = array(
                                'process_sign' => 0,
                                'lastruntime' => 0,
                                'runcount' => 0
                            );
                            $conditions = "down_queue_id =:down_queue_id";
                            $params = array(
                                ':down_queue_id' => $Queue['down_queue_id']
                            );
                            EbayListingQueueDAO::getInstance()->iupdate($columns, $conditions, $params);
                            goto label;
                        }
                        continue 2;
                    }
                    $Items = pq('ItemArray>Item');
                    $length = $Items->length;
                    
                    if (! $length) {
                        EbayListingQueueDAO::getInstance()->deleteByPk($Queue['down_queue_id']);
                        break;
                    }
                    $columns = array(
                        'seller_id' => $Queue['seller_id'],
                        'shop_id' => $Queue['shop_id'],
                        'create_time' => time()
                    );
                    EbayListingDownDAO::getInstance()->begintransaction();
                    try {
                        $lid = EbayListingDownDAO::getInstance()->iinsert($columns, true);
                        if ($lid > 0) {
                            
                            file_put_contents(BASE_PATH . "/logs/EbayListingData.{$lid}.log", $listingData);
                            unset($listingData);
                            
                            EbayListingQueueDAO::getInstance()->deleteByPk($Queue['down_queue_id']);
                            EbayListingDownDAO::getInstance()->commit();
                        } else {
                            EbayListingDownDAO::getInstance()->rollback();
                        }
                    } catch (Exception $e) {
                        EbayListingDownDAO::getInstance()->rollback();
                    }
                    if ((integer) $doc['PaginationResult>TotalNumberOfPages']->html() <= $page || $doc['HasMoreItems']->html() == 'false') {
                        break;
                    }
                }
            }
        } else {
            return false;
        }
    }

    /**
     * @desc 解析listing 信息
     * @author liaojianwen
     * @date 2015-07-29
     * @return multitype:mixed
     */
    public function parseEbayListing()
    {
        label:
        
        $listing = EbayListingDownModel::model()->getListingDownData();
        
        if ($listing !== false) {
            foreach ($listing as $key => &$value) {
                
                $listingData = file_get_contents(BASE_PATH . "/logs/EbayListingData.{$value['down_id']}.log");
                
                $doc = phpQuery::newDocumentXML($listingData);
                phpQuery::selectDocument($doc);
                if (pq('Ack')->html() == 'Success') {
                    $_item = pq('ItemArray')->find('Item');
                    $item_length = $_item->length;
                    for ($i = 0; $i < $item_length; $i ++) {
                        
                        $columns_listing = array(
                            'down_id' => $value['down_id'],
                            'shop_id' => $value['shop_id'],
                            'auto_pay' => boolConvert::toInt01(
                                $_item->eq($i)
                                    ->find('>AutoPay')
                                    ->html()),
                            'country_code' => $_item->eq($i)
                                ->find('>Currency')
                                ->html(),
                            'site' => $_item->eq($i)
                                ->find('>Site')
                                ->html(),
                            'title' => $_item->eq($i)
                                ->find('>Title')
                                ->html(),
                            'item_id' => $_item->eq($i)
                                ->find('>ItemID')
                                ->html(),
                            'relist_parent_id' => $_item->eq($i)
                                ->find('RelistParentID')
                                ->html(),
                            'sku' => $_item->eq($i)
                                ->find('>SKU')
                                ->html(),
                            'quantity' => $_item->eq($i)
                                ->find('>Quantity')
                                ->html(),
                            'quantity_sold' => $_item->eq($i)
                                ->find('>SellingStatus>QuantitySold')
                                ->html(),
                            'listing_type' => $_item->eq($i)
                                ->find('>ListingType')
                                ->html(),
                            'listing_status' => $_item->eq($i)
                                ->find('>SellingStatus>ListingStatus')
                                ->html(),
                            'private_listing' => boolConvert::toInt01(
                                $_item->eq($i)
                                    ->find('>PrivateListing')
                                    ->html()),
                            'item_revised' => boolConvert::toInt01(
                                $_item->eq($i)
                                    ->find('>ReviseStatus>ItemRevised')
                                    ->html()),
                            'out_of_stock_control' => boolConvert::toInt01(
                                $_item->eq($i)
                                    ->find('>OutOfStockControl')
                                    ->html()),
                            'primary_category_id' => $_item->eq($i)
                                ->find('>PrimaryCategory>CategoryID')
                                ->html(),
                            'primary_category_name' => $_item->eq($i)
                                ->find('>PrimaryCategory>CategoryName')
                                ->html(),
                            'secondary_category_id' => $_item->eq($i)
                                ->find('>SecondaryCategory>CategoryID')
                                ->html(),
                            'secondary_category_name' => $_item->eq($i)
                                ->find('>SecondaryCategory>CategoryName')
                                ->html(),
                            'dispatch_time_max' => $_item->eq($i)
                                ->find('>DispatchTimeMax')
                                ->html(),
                            'view_item_url' => $_item->eq($i)
                                ->find('>ListingDetails>ViewItemURL')
                                ->html(),
                            'gallery_url' => $_item->eq($i)
                                ->find('>PictureDetails>GalleryURL')
                                ->html(),
                            'buy_it_now_price' => $_item->eq($i)
                                ->find('>BuyItNowPrice')
                                ->html(),
                            'b_currencyID' => $_item->eq($i)
                                ->find('>BuyItNowPrice')
                                ->attr('currencyID'),
                            'current_price' => $_item->eq($i)
                                ->find('>SellingStatus>CurrentPrice')
                                ->html(),
                            'c_currencyID' => $_item->eq($i)
                                ->find('>SellingStatus>CurrentPrice')
                                ->attr('currencyID'),
                            'currency' => $_item->eq($i)
                                ->find('>Currency')
                                ->html(),
                            'location' => $_item->eq($i)
                                ->find('>Location')
                                ->html(),
                            'hit_count' => $_item->eq($i)
                                ->find('>HitCount')
                                ->html(),
                            'start_time' => strtotime(
                                $_item->eq($i)
                                    ->find('>ListingDetails>StartTime')
                                    ->html()),
                            'end_time' => strtotime(
                                $_item->eq($i)
                                    ->find('>ListingDetails>EndTime')
                                    ->html())
                        );
                        foreach ($columns_listing as $key => $columns) {
                            if (empty($columns)) {
                                unset($columns_listing['$key']);
                            }
                        }
                        $_conditions = array(
                            'item_id' => $columns_listing['item_id']
                        );
                        EbayListingDAO::getInstance()->isExists($_conditions, true);
                        if (isset($_conditions['listing_id']) && $_conditions['listing_id'] > 0) {
                            $conditions = 'listing_id=:listing_id';
                            $ps = array(
                                ':listing_id' => $_conditions['listing_id']
                            );
                            unset($columns_listing['item_id']);
                            EbayListingDAO::getInstance()->iupdate($columns_listing, $conditions, $ps);
                            $listing_id = $_conditions['listing_id'];
                        } else {
                            EbayListingDAO::getInstance()->insert($columns_listing);
                            $listing_id = EbayListingDAO::getInstance()->getLastInsertID();
                        }
                        if (empty($listing_id)) {
                            continue;
                        }
                        // 支付方式
                        $paymentMethods = $_item->eq($i)->find('>PaymentMethods');
                        $payment_length = $paymentMethods->length;
                        $conditions = 'listing_id=:listing_id';
                        $params = array(
                            ':listing_id' => $listing_id
                        );
                        for ($j = 0; $j < $payment_length; $j ++) {
                            call_user_func(
                                function () use($_item, $listing_id, $j, $i) {
                                    $paymentMethodInfo = array(
                                        'listing_id' => $listing_id,
                                        'num' => $j,
                                        'payment_methods' => $_item->eq($i)
                                            ->eq($j)
                                            ->find('>PaymentMethods')
                                            ->html()
                                    );
                                    
                                    // clear update old data
                                    $columns = array(
                                        'num' => $j
                                    );
                                    $conditions = 'listing_id=:listing_id and payment_methods=:payment_methods and num!=:num';
                                    $params = array(
                                        ':listing_id' => $paymentMethodInfo['listing_id'],
                                        ':num' => $paymentMethodInfo['num'],
                                        ':payment_methods' => $paymentMethodInfo['payment_methods']
                                    );
                                    EbayListingSkuDAO::getInstance()->iupdate($columns, $conditions, $params);
                                    
                                    $conditions = 'listing_id=:listing_id and num=:num';
                                    $params = array(
                                        ':listing_id' => $paymentMethodInfo['listing_id'],
                                        ':num' => $paymentMethodInfo['num']
                                    );
                                    EbayListingPaymentMethodsDAO::getInstance()->ireplaceinto($paymentMethodInfo, $conditions, $params);
                                });
                        }
                        
                        // Listing的描述
                        $descInfo = array(
                            'listing_id' => $listing_id,
                            'description' => $_item->eq($i)
                                ->find('>Description')
                                ->html()
                        );
                        // listing描述写入
                        EbayListingDescDAO::getInstance()->ireplaceinto($descInfo, $conditions, $params);
                        
                        // Listing的多属性SKU
                        $variations = $_item->eq($i)->find('>Variations>Variation');
                        $variation_length = $variations->length;
                        for ($k = 0; $k < $variation_length; $k ++) {
                            $skuInfo = array(
                                'listing_id' => $listing_id,
                                'num' => $k,
                                'sku' => $variations->eq($k)
                                    ->find('>SKU')
                                    ->html(),
                                'start_price' => $variations->eq($k)
                                    ->find('>StartPrice')
                                    ->html(),
                                'quantity' => $variations->eq($k)
                                    ->find('>Quantity')
                                    ->html(),
                                'quantity_sold' => $variations->eq($k)
                                    ->find('>SellingStatus>QuantitySold')
                                    ->html(),
                                'create_time' => time()
                            );
                            
                            // clear update old data
                            $columns = array(
                                'num' => $k
                            );
                            $conditions = 'listing_id=:listing_id and sku=:sku and num!=:num';
                            $params = array(
                                ':listing_id' => $skuInfo['listing_id'],
                                ':num' => $skuInfo['num'],
                                ':sku' => $skuInfo['sku']
                            );
                            EbayListingSkuDAO::getInstance()->iupdate($columns, $conditions, $params);
                            
                            $conditions = 'listing_id=:listing_id and num=:num';
                            $params = array(
                                ':listing_id' => $skuInfo['listing_id'],
                                ':num' => $skuInfo['num']
                            );
                            EbayListingSkuDAO::getInstance()->ireplaceinto($skuInfo, $conditions, $params);
                        }
                    }
                } else {
                    iMongo::getInstance()->setCollection('ErrorEbayListingData')->insert(
                        array(
                            'down_id' => $value['down_id'],
                            'data' => file_get_contents(BASE_PATH . "/logs/EbayListingData.{$value['down_id']}.log"),
                            'time' => time()
                        ));
                }
                
                EbayListingDownModel::model()->deleteListingDownData($value['down_id']);
                
                unlink(BASE_PATH . "/logs/EbayListingData.{$value['down_id']}.log");
            }
            unset($value);
            
            goto label;
        }
    }

    /**
     * @desc  获取item 图片
     * @param  $itemid
     * @param  $sellerId
     * @author liaojianwen
     * @date 2015-08-26
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function getItemURL($itemid, $sellerId)
    {
        if (empty($itemid) && empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '');
        }
        $result = EbayListingDAO::getInstance()->findByAttributes(array(
            'item_id' => $itemid
        ), array(
            'gallery_url'
        ));
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'the gallery_url is not found');
        }
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
    }
}