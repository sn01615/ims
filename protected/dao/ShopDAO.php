<?php
/**
 * @desc shop表操作类
 * @author liaojianwen
 * @date 2015-2-5
 */
class ShopDAO extends BaseDAO
{
    /**
     * @desc 对象实例重用
     * @author liaojianwen
     * @date 2015-2-5
     * @param string $className 需要实例化的类名
     * @return ShopDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 初始化
     * @author liaojianwen
     * @date 2015-2-5
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'shop';
        $this->primaryKey = 'shop_id';
    }

    /**
     * @desc 存储TOKEN
     * @param array $columns
     * @return boolean
     * @author liaojianwen
     * @date 2015-2-5
     */
    public function saveToken($columns)
    {
        $data = array(
            'AccountID' => $columns['AccountID'],
            'is_delete' => boolConvert::toInt01(false)
        );
        $exist = $this->isExists($data, true);
        if ($columns['AccountState'] == 'Active') {
            $columns['status'] = EnumOther::EBAY_ACCOUNT_NORMAL;
        } else {
            $columns['status'] = EnumOther::EBAY_ACCOUNT_CLOSED;
        }
        if (! $exist) {
            // 新账号，记录不存在，插入
            $columns['created_time'] = time();
            $columns['updated_time'] = time();
            $result['Ack'] = 'Success';
            $this->dbCommand->reset();
            $result['affected_rows'] = $this->dbCommand->insert($this->tableName, $columns);
        } else {
            $data = $this->findByAttributes($data, array(
                'shop_id'
            ));
            $columns['updated_time'] = time();
            $result['Ack'] = 'Success';
            $result['affected_rows'] = $this->updateByPk($data['shop_id'], $columns);
        }
        if ($result['affected_rows'] === 0) {
            $result = array(
                'Ack' => 'Failure',
                'error' => array(
                    'error_code' => 'none',
                    'error_message' => "insert/update error."
                )
            );
        }
        return $result;
    }

    /**
     * @desc 获取店铺列表
     * @param int $page            
     * @param int $pageSize            
     * @author YangLong
     * @date 2015-02-12
     */
    public function shopList($page, $pageSize)
    {
        $userConfig = UserModel::model()->getUserConfigs();
        $shopIds = implode(',', $userConfig['shops']);
        
        $limit = $pageSize;
        $offset = ($page - 1) * $limit;
        $conditions = 'seller_id=:seller_id and is_delete=' . boolConvert::toInt01(false);
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            if (empty($shopIds)) {
                $shopIds = 0;
            }
            $conditions .= " and shop_id in ({$shopIds})";
        }
        $params = array(
            ':seller_id' => Yii::app()->session['userInfo']['seller_id']
        );
        $siteId = isset(Yii::app()->session['switchInfo']['siteId']) ? Yii::app()->session['switchInfo']['siteId'] : - 1;
        if ($siteId > - 1) {
            $conditions .= ' and site_id=:siteId';
            $params[':siteId'] = $siteId;
        }
        
        $result['list'] = $this->dbCommand->select('shop_id,seller_id,site_id,nick_name,status,updated_time,HardExpirationTime', 'SQL_CALC_FOUND_ROWS')
            ->from($this->tableName)
            ->where($conditions, $params)
            ->limit($limit, $offset)
            ->queryAll();
        $result['page'] = array(
            'page' => $page,
            'pageSize' => $pageSize
        );
        $result['page']['count'] = $this->dbCommand->setText('select found_rows()')->queryScalar();
        return $result;
    }

    /**
     * @desc 获取有店铺站点列表
     * @return array 结果数组
     * @author YangLong
     * @date 2015-03-06
     */
    public function cSiteList()
    {
        $userConfig = UserModel::model()->getUserConfigs();
        $shopIds = implode(',', $userConfig['shops']);
        
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $conditions = "seller_id={$sellerId} and is_delete=" . boolConvert::toInt01(false) . " and `status`=" . EnumOther::EBAY_ACCOUNT_NORMAL;
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            if (empty($shopIds)) {
                $shopIds = 0;
            }
            $conditions .= " and shop_id in ({$shopIds})";
        }
        $result = $this->dbCommand->select('site_id')
            ->from($this->tableName)
            ->where($conditions)
            ->group('site_id')
            ->queryColumn();
        return $result;
    }

    /**
     * @desc 获取当前用户店铺列表(用于添加case)
     * @author lvjianfei
     * @date 2015-04-22
     * @return array
     */
    public function getShopName()
    {
        $conditions = 'seller_id=:seller_id and is_delete=' . boolConvert::toInt01(false);
        $params = array(
            ':seller_id' => Yii::app()->session['userInfo']['seller_id']
        );
        $siteId = isset(Yii::app()->session['switchInfo']['siteId']) ? Yii::app()->session['switchInfo']['siteId'] : -1;
        if ($siteId > -1) {
            $conditions .= ' and site_id=:siteId and status=1';
            $params[':siteId'] = $siteId;
        }
        $this->dbCommand->reset();
        $result = $this->dbCommand->select('shop_id,account,nick_name,seller_id')
        	->from($this->tableName)
        	->where($conditions,$params)
        	->queryAll();
        return $result;
    }
    
    /**
     * @desc 获取token,site_id
     * @param string $shopId
     * @param string $sellerId
     * @author liaojianwen
     * @date 2015-09-23
     */
    public function lawfulshopId($shopId, $sellerId)
    {
        $conditions = "shop_id = {$shopId} and seller_id = {$sellerId}";
        
        $result = $this->dbCommand->reset()
            ->select('token,site_id,nick_name')
            ->from($this->tableName)
            ->where($conditions)
            ->queryRow();
        return $result;
    }
    
    /**
     * @desc 获取订单关联的店铺的token
     * @param string $orderID
     * @param string $sellerId
     * @author liaojianwen
     * @date 2015-11-03 
     * @return mixed
     */
    public function getInvoicesToken($orderID,$sellerId)
    {
        $conditions = "o.OrderID =:orderid and s.seller_id=:sellerid";
        $params = array(
            ':orderid' => $orderID,
            ':sellerid' => $sellerId
        );
        $result = $this->dbCommand->reset()
            ->select('token,site_id,o.shop_id')
            ->from("ebay_orders o")
            ->join("shop s", "o.shop_id = s.shop_id")
            ->where($conditions, $params)
            ->queryRow();
        return $result;
    }
}