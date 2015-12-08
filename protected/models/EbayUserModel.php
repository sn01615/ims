<?php

/**
 * @desc eaby user info model
 * @author YangLong
 * @date 2015-09-06
 */
class EbayUserModel extends BaseModel
{
    
    /**
     * @desc 覆盖父方法返回此对象的一个实例
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-09-06
     * @return EbayUserModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * @desc 获取ebay用户列表
     * @param int $page
     * @param int $pageSize
     * @param int $sellerId
     * @param string $keyword
     * @param string $searchType
     * @author YangLong
     * @date 2015-09-06
     * @return mixed
     */
    public function getEbayUserList($page, $pageSize, $sellerId, $keyword, $searchType)
    {
        $userConfig = UserModel::model()->getUserConfigs();
        $shopIds = implode(',', $userConfig['shops']);
        $keyword =trim($keyword);
        $columns = array(
            'u.ebay_user_info_id as uid',
            'u.EIASToken',
            'u.UserID',
            'u.regaddr_CityName',
            'u.regaddr_CompanyName',
            'u.regaddr_Country',
            'u.regaddr_CountryName',
            'u.regaddr_Name',
            'u.regaddr_Phone',
            'u.regaddr_PostalCode',
            'u.regaddr_StateOrProvince',
            'u.regaddr_Street',
            'u.regaddr_Street1',
            'u.regaddr_Street2'
        );
        $conditions = 's.seller_id=:seller_id and is_delete=' . boolConvert::toInt01(false);
        $params = array(
            ':seller_id' => $sellerId
        );
        $joinArray = array(
            array(
                ShopDAO::getInstance()->getTableName() . ' s',
                's.shop_id=u.shop_id and s.is_delete=0'
            ),
            array(
                EbayUserShopsDAO::getInstance()->getTableName() . ' us',
                'u.ebay_user_info_id=us.ebay_user_info_id',
                'left' => true
            )
        );
        
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            if (empty($shopIds)) {
                $shopIds = 0;
            }
            $conditions .= " and (s.shop_id in ({$shopIds}) or us.shop_id in ({$shopIds}))";
        }
        
        // 获取切换店铺信息
        $accountId = isset(Yii::app()->session['switchInfo']['accountId']) ? Yii::app()->session['switchInfo']['accountId'] : 0;
        if ($accountId > 0) {
            $conditions .= " and (s.shop_id={$accountId} or us.shop_id={$accountId})";
        }
        
        $groups = '';
        $order = 'u.ebay_user_info_id desc';
        $limit = $pageSize;
        $offset = ($page - 1) * $pageSize;
        if (! empty($keyword) && ! empty($searchType)) {
            if ($searchType == 'userid') {
                $conditions .= ' and UserID like :keyword';
                $params[':keyword'] = "%{$keyword}%";
            }
            if ($searchType == 'username') {
                $keywords = explode(' ', $keyword);
                foreach ($keywords as $key => &$value) {
                    $value = trim($value);
                    if (empty($value)) {
                        unset($keywords[$key]);
                    }
                }
                unset($value);
                $keyword = implode(' ', $keywords);
                
                $joinArray[] = array(
                    EbayOrdersDAO::getInstance()->getTableName() . ' o',
                    'u.EIASToken=o.EIASToken'
                );
                $joinArray[] = array(
                    EbayOrderTransactionDAO::getInstance()->getTableName() . ' t',
                    'o.ebay_orders_id=t.ebay_orders_id'
                );
                $conditions .= ' and (t.Buyer_UserFirstName like :keyword or t.Buyer_UserLastName like :keyword or CONCAT(t.Buyer_UserFirstName,\' \',t.Buyer_UserLastName) like :keyword)';
                $params[':keyword'] = "%{$keyword}%";
                
                $groups = 'u.' . EbayUserInfoDAO::getInstance()->getPk();
            }
        }
        $result['list'] = EbayUserInfoDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 'u', $order, $limit, $offset, 'DISTINCT SQL_CALC_FOUND_ROWS', $groups);
        $result['count'] = EbayUserInfoDAO::getInstance()->setTextQuery('SELECT FOUND_ROWS()', array(), 'queryScalar');
        $pageInfo['page'] = $page;
        $pageInfo['pageSize'] = $pageSize;
        $result['pageInfo'] = $pageInfo;
        if ($result['list'] !== false) {
            foreach ($result['list'] as $key => &$value) {
                $value['_count_orders'] = EbayUserInfoDAO::getInstance()->setTextQuery("SELECT count(*) FROM
                    ebay_orders as o
                    where o.EIASToken='{$value['EIASToken']}'", array(), 'queryScalar');
                $value['_count_trans'] = EbayUserInfoDAO::getInstance()->setTextQuery("SELECT count(*) FROM
                    ebay_orders as o
                    join ebay_order_transaction as t
                    on o.ebay_orders_id=t.ebay_orders_id
                    where o.EIASToken='{$value['EIASToken']}'", array(), 'queryScalar');
                $value['_count_cases'] = EbayUserInfoDAO::getInstance()->setTextQuery("SELECT caseId_type,count(*) as num
                    FROM `case` where otherParty_userId='{$value['UserID']}' or user_userId ='{$value['UserID']}' group by caseId_type", array(), 'queryAll');
                $value['_count_disputes'] = EbayUserInfoDAO::getInstance()->setTextQuery("SELECT DisputeReason,count(*) as num
                    FROM `disputes` where BuyerUserID='{$value['UserID']}' group by DisputeReason", array(), 'queryAll');
                $value['_count_Neutral'] = EbayUserInfoDAO::getInstance()->setTextQuery("SELECT count(*) FROM
                    ebay_feedback_transaction where CommentType='Neutral'
                    and CommentingUser='{$value['UserID']}'", array(), 'queryScalar');
                $value['_count_Negative'] = EbayUserInfoDAO::getInstance()->setTextQuery("SELECT count(*) FROM
                    ebay_feedback_transaction where CommentType='Negative'
                    and CommentingUser='{$value['UserID']}'", array(), 'queryScalar');
                
                $columns = array(
                    't.Buyer_UserFirstName',
                    't.Buyer_UserLastName'
                );
                $conditions = 'o.EIASToken=:EIASToken';
                $params = array(
                    ':EIASToken' => $value['EIASToken']
                );
                $joinArray = array(
                    array(
                        EbayOrderTransactionDAO::getInstance()->getTableName() . ' t',
                        'o.ebay_orders_id=t.ebay_orders_id'
                    )
                );
                $value['usernames'] = EbayOrdersDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 'o', '', 0, null, 'DISTINCT');
            }
            unset($value);
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
    }
    
    /**
     * @desc 获取用户信息
     * @param int $uid
     * @author YangLong
     * @date 2015-09-07
     * @return mixed
     */
    public function getEbayUserInfoByUid($uid)
    {
        $columns = array(
            'UserID',
            'Email',
            'Site',
            'RegistrationDate'
        );
        $conditions = 'ebay_user_info_id=:ebay_user_info_id';
        $params = array(
            ':ebay_user_info_id' => $uid
        );
        $joinArray = array();
        $result = EbayUserInfoDAO::getInstance()->iselect($columns, $conditions, $params, false, $joinArray, 'u');
        if ($result !== false) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
    }
    
    /**
     * @desc 用户列表中给用户发消息生成队列
     * @param string $shopId
     * @param string $content
     * @param string $imgUrl
     * @param string $sendMyMsg
     * @param string $buyerId
     * @param string $itemId
     * @param string $sellerId
     * @author liaojianwen
     * @date 2015-09-23
     * @return mixed
     */
    public function generateMsgQueue($shopId, $content, $imgUrl, $sendMyMsg, $buyerId, $itemId, $sellerId)
    {
        if (empty($shopId) || empty($sellerId) || empty($content) || empty($buyerId) || empty($itemId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
        $mongo = new MongoClient('mongodb://127.0.0.1:27017', array(
            'connect' => false
        ));
        MongoQueue::$connection = $mongo;
        MongoQueue::$database = 'ImsMongoQueue';
        $token = ShopDAO::getInstance()->lawfulshopId($shopId, $sellerId);
        if ($token === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'feedbackId  Error.');
        } else {
            $parameters = array(
                'queue_type' => 'ContactMsgUploadQueue',
                'CommentingUser' => $buyerId,
                'shopId' => $shopId,
                'nick_name' => $token['nick_name'],
                'itemId' => $itemId,
                'token' => $token['token'],
                'sendMyMsg' => $sendMyMsg,
                'text' => $content,
                'siteId' => $token['site_id'],
                'imgUrl' => $imgUrl,
                'create_time' => time()
            );
            
            $res = MongoQueue::push('QueueTracerModel', 'trace', $parameters, time(), false, 200);
            if (! $res['ok']) {
                return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
            } else {
                return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
            }
        }
    }
    
    /**
     * @desc 获取客户关联的备注
     * @param string  $clientId 客户ID
     * @author liaojianwen
     * @date 2015-09-23
     * @return mixed
     */
    public function getCustNote($clientId)
    {
        if(empty($clientId)){
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
        $columns = array(
            'author_name',
            'cust',
            'item_id',
            'text',
            'create_time'
        );
        $conditions = array(
            'cust' => $clientId
        );
        $result = ItemNoteDAO::getInstance()->findAllByAttributes($conditions,$columns,array('create_time desc'));
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '查询不到数据');
        }
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
    }
    
    /**
     * @desc 设置语言
     * @author YangLong
     * @date 2015-10-19
     */
    public function setLanguage($lang)
    {
        if (empty($lang)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'param "lang" cannot empty!');
        }
        
        setcookie("cLanguage", $lang, time() + 3600 * 24 * 365 * 10);
        if (strlen($lang) > 0 && is_dir('public/lang/' . $lang)) {
            Yii::app()->session['cLanguage'] = $lang;
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'Language pack does not exist! orz');
        }
    }
}
