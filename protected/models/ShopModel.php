<?php

/**
 * @desc 用户处理类
 * @author YangLong
 * @date 2015-02-09
 */
class ShopModel extends BaseModel
{

    /**
     * @desc 覆盖父方法返回ShopModel对象
     * @author YangLong
     * @date 2015-02-09
     * @return ShopModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @desc 获取店铺列表  
     * @param int $page
     * @param int $pageSize
     * @author YangLong,liaojianwen
     * @date 2015-02-09
     * @return array 店铺列表
     */
    public function shopList($page, $pageSize)
    {
        if (! ($page > 0 && $pageSize > 0)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
        
        $result = ShopDAO::getInstance()->shopList($page, $pageSize);
        foreach ($result['list'] as $key => &$value) {
            $value['site_name'] = Utility::getArrayValue(EnumMsgStatus::$siteOptions, $value['site_id']);
        }
        unset($value);
        $accountId = isset(Yii::app()->session['switchInfo']['accountId']) ? Yii::app()->session['switchInfo']['accountId'] : 0;
        if ($accountId > 0) {
            $result['current'] = $accountId;
        }
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
    }
    
    /**
     * @desc 删除店铺
     * @param string $sids
     * @param string $sellerId
     * @author YangLong
     * @date 2015-02-09
     * @return mixed
     */
    public function setShopDel($sids, $sellerId)
    {
        if (empty($sids)) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING);
        }
        
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING);
        }
        
        $columns = array(
            'is_delete' => boolConvert::toInt01(true)
        );
        $conditions = 'seller_id=:seller_id and ' . ShopDAO::getInstance()->getPk() . " in ({$sids})";
        $params = array(
            ':seller_id' => $sellerId
        );
        $result = ShopDAO::getInstance()->iupdate($columns, $conditions, $params);
        
        if ($result !== false) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
    }

    /**
     * @desc 禁用或启用店铺
     * @param string $sids shop table ids.
     * @param int $action
     * @param int $sellerId
     * @author YangLong
     * @date 2015-09-18
     * @return mixed
     */
    public function setShopStatus($sids, $action, $sellerId)
    {
        if (empty($sids)) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING);
        }
        
        if ($action == 'disable') {
            $status = EnumOther::EBAY_ACCOUNT_CLOSED;
        } elseif ($action == 'enable') {
            $status = EnumOther::EBAY_ACCOUNT_NORMAL;
        } else {
            return $this->handleApiFormat(EnumOther::ACK_WARNING);
        }
        
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING);
        }
        
        $columns = array(
            'status' => $status
        );
        $conditions = 'seller_id=:seller_id and ' . ShopDAO::getInstance()->getPk() . " in ({$sids})";
        $params = array(
            ':seller_id' => $sellerId
        );
        $result = ShopDAO::getInstance()->iupdate($columns, $conditions, $params);
        if ($result !== false) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
    }
    
    /**
     * @desc 显示站点列表
     * @author liaojianwen
     * @date 2015-02-26
     * @param boolean $filter
     * @return array
     * @modify 2015-03-06 YangLong 增加只返回有账户的站点的功能
     */
    public function siteList($filter)
    {
        if ($filter) {
            $siteOptionsId = ShopDAO::getInstance()->cSiteList();
            $siteOptions = array();
            foreach ($siteOptionsId as $value) {
                $siteOptions[$value] = EnumMsgStatus::$siteOptions[$value];
            }
        } else {
            $siteOptions = EnumMsgStatus::$siteOptions;
        }
        if (! empty($siteOptions)) {
            $result['status'] = 'true';
            $result['sites'] = $siteOptions;
        } else {
            $result['status'] = 'false';
        }
        $siteId = isset(Yii::app()->session['switchInfo']['siteId']) ? Yii::app()->session['switchInfo']['siteId'] : -1;
        if ($siteId >= 0) {
            $result['current'] = $siteId;
        }
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
    }
    
    /**
     * @desc 获取当前用户店铺列表(用于添加case)
     * @author lvjianfei
     * @date 2015-04-22
     * @return array
     */
    public function getShopName()
    {
    	$result['list'] = ShopDAO::getInstance()->getShopName();
    	if(empty($result['list'])){
    		return $this->handleApiFormat(EnumOther::ACK_FAILURE,'','获取店铺名失败');
    	}
    	return $this->handleApiFormat(EnumOther::ACK_SUCCESS,$result,'');
    }
    
    /**
     * @desc 编辑店铺别名
     * @param number $sellerId
     * @param number $shopId
     * @param string $nickname
     * @author YangLong
     * @date 2015-06-19
     * @return mixed
     */
    public function setShopNickname($sellerId, $shopId, $nickname)
    {
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING);
        }
        
        if (empty($nickname)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '店铺别名不能为空');
        }
        
        $columns = array(
            'nick_name' => $nickname
        );
        $conditions = 'shop_id=:shop_id and seller_id=:seller_id';
        $params = array(
            ':shop_id' => $shopId,
            ':seller_id' => $sellerId
        );
        $result = ShopDAO::getInstance()->iupdate($columns, $conditions, $params);
        
        if ($result !== false) {
            $columns = array(
                'nick_name'
            );
            $conditions = 'shop_id=:shop_id';
            $params = array(
                ':shop_id' => $shopId
            );
            $result = ShopDAO::getInstance()->iselect($columns, $conditions, $params, false);
            
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '保存失败');
        }
    }
    
    /**
     * @desc 获取用户对应店铺
     * @param int $shopId
     * @param int $sellerId
     * @author YangLong
     * @date 2015-09-14
     * @return mixed
     */
    public function getUsersByShopid($shopId, $sellerId)
    {
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING);
        }
        
        $columns = array(
            'user_id',
            'realname',
            'username'
        );
        $conditions = 'is_delete=' . boolConvert::toInt01(false) . ' and pid=:pid';
        $params = array(
            ':pid' => $sellerId
        );
        $result['users'] = UserDAO::getInstance()->iselect($columns, $conditions, $params);
        
        $columns = array(
            'shop_id',
            'user_id'
        );
        $conditions = 'shop_id=:shop_id';
        $params = array(
            ':shop_id' => $shopId
        );
        $result['ref'] = ShopRefUsersDAO::getInstance()->iselect($columns, $conditions, $params);
        
        if ($result['users'] !== false && $result['ref'] !== false) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_WARNING, $result);
        }
    }
    
    /**
     * @desc 设置店铺对应的用户
     * @param array $set
     * @param int $shopId
     * @param int $sellerId
     * @author YangLong
     * @date 2015-09-15
     * @return mixed
     */
    public function setShopUsers($set, $shopId, $sellerId)
    {
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING, '', 'you have not permission');
        }
        
        if (is_array($set)) {
            foreach ($set as &$value) {
                if (isset($value[0]) && isset($value[1])) {
                    $value[0] = (int) $value[0];
                    $value[1] = boolConvert::toInt01($value[1]);
                } else {
                    return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'input data err');
                }
            }
            unset($value);
            
            ShopRefUsersDAO::getInstance()->begintransaction();
            try {
                foreach ($set as $value) {
                    if ($value[1]) {
                        $columns = array(
                            'shop_id' => $shopId,
                            'user_id' => $value[0]
                        );
                        $conditions = 'shop_id=:shop_id and user_id=:user_id';
                        $params = array(
                            ':shop_id' => $shopId,
                            ':user_id' => $value[0]
                        );
                        ShopRefUsersDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                    } else {
                        $conditions = 'shop_id=:shop_id and user_id=:user_id';
                        $params = array(
                            ':shop_id' => $shopId,
                            ':user_id' => $value[0]
                        );
                        ShopRefUsersDAO::getInstance()->idelete($conditions, $params);
                    }
                    
                    $key = md5('user_shops_cache' . $value[0]);
                    iMemcache::getInstance()->delete($key);
                }
                ShopRefUsersDAO::getInstance()->commit();
                
                return $this->handleApiFormat(EnumOther::ACK_SUCCESS);
            } catch (Exception $e) {
                ShopRefUsersDAO::getInstance()->rollback();
                
                return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'database err');
            }
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'input data err');
        }
    }
    
    /**
     * @desc 设置用户对应的店铺
     * @param array $set
     * @param int $userId
     * @param int $sellerId
     * @author YangLong
     * @date 2015-09-17
     * @return mixed
     */
    public function setUserShops($set, $userId, $sellerId)
    {
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING, '', 'you have not permission');
        }
        
        if (is_array($set)) {
            foreach ($set as &$value) {
                if (isset($value[0]) && isset($value[1])) {
                    $value[0] = (int) $value[0];
                    $value[1] = boolConvert::toInt01($value[1]);
                } else {
                    return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'input data err');
                }
            }
            unset($value);
            
            ShopRefUsersDAO::getInstance()->begintransaction();
            try {
                foreach ($set as $value) {
                    if ($value[1]) {
                        $columns = array(
                            'shop_id' => $value[0],
                            'user_id' => $userId
                        );
                        $conditions = 'shop_id=:shop_id and user_id=:user_id';
                        $params = array(
                            ':shop_id' => $value[0],
                            ':user_id' => $userId
                        );
                        ShopRefUsersDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                    } else {
                        $conditions = 'shop_id=:shop_id and user_id=:user_id';
                        $params = array(
                            ':shop_id' => $value[0],
                            ':user_id' => $userId
                        );
                        ShopRefUsersDAO::getInstance()->idelete($conditions, $params);
                    }
                }
                ShopRefUsersDAO::getInstance()->commit();
                
                $key = md5('user_shops_cache' . $userId);
                iMemcache::getInstance()->delete($key);
                
                return $this->handleApiFormat(EnumOther::ACK_SUCCESS);
            } catch (Exception $e) {
                ShopRefUsersDAO::getInstance()->rollback();
                
                return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'database err');
            }
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'input data err');
        }
    }
    
    /**
     * @desc 获取店铺所属的用户信息
     * @param int $userId
     * @param int $sellerId
     * @author YangLong
     * @date 2015-09-17
     * @return mixed
     */
    public function getShopByUserid($userId, $sellerId)
    {
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            return $this->handleApiFormat(EnumOther::ACK_WARNING, '', 'you have not permission');
        }
        
        $columns = array(
            'shop_id',
            'nick_name'
        );
        $conditions = 'seller_id=:seller_id and is_delete=' . boolConvert::toInt01(false);
        $params = array(
            ':seller_id' => $sellerId
        );
        $result['shops'] = ShopDAO::getInstance()->iselect($columns, $conditions, $params);
        
        $columns = array(
            'shop_id',
            'user_id'
        );
        $conditions = 'user_id=:user_id';
        $params = array(
            ':user_id' => $userId
        );
        $result['ref'] = ShopRefUsersDAO::getInstance()->iselect($columns, $conditions, $params);
        
        if ($result['shops'] !== false && $result['ref'] !== false) {
            if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
                foreach ($result['shops'] as $key => $value) {
                    $_uns = true;
                    foreach ($result['ref'] as $key2 => $value2) {
                        if ($value['shop_id'] == $value2['shop_id']) {
                            $_uns = false;
                        }
                    }
                    if ($_uns) {
                        unset($result['shops'][$key]);
                    }
                }
                $result['shops'] = array_values($result['shops']);
            }
            
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_WARNING, $result);
        }
    }
    
}
