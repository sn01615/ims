<?php

/**
 * @desc ebay_feedback_transaction表操作类
 * @author liaojianwen
 * @date 2015-05-15
 */
class EbayFeedbackTransactionDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-05-15
     * @return EbayFeedbackTransactionDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-05-15
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'ebay_feedback_transaction';
        $this->primaryKey = 'ebay_feedback_transaction_id';
        $this->transaction = 'ebay_order_transaction';
        $this->order = 'ebay_orders';
        $this->created = 'create_time';
        $this->shop = 'shop';
    }
    
    /**
     * @desc feedback 列表
     * @param array $paramArr
     * @param int  $page
     * @param int $pageSize
     * @param string $cust
     * @param string $status
     * @author liaojianwen
     * @date 2015-08-25
     * @return Ambigous <multitype:, mixed>
     */
    public function getFeedbackList($paramArr,$page,$pageSize,$cust,$status)
    {
        $userConfig = UserModel::model()->getUserConfigs();
        $shopIds = implode(',', $userConfig['shops']);
        
        $limit = $pageSize;
        $offset = ($page - 1) * $limit;
        $params = array();
        $NOW = time() - 30 * 24 * 60 * 60;
        $selects = 'f.ebay_feedback_transaction_id,f.TransactionID,f.ItemID,f.CommentingUser,f.CommentingUserScore,f.CommentText,f.CommentType,
            f.CommentTime,f.FeedbackID,f.Role,f.OrderLineItemID,f.ItemTitle,f.ItemPrice,f.currencyID,s.nick_name,f.isSendMsg,f.isResponse,f.isChange,
            f.ResponseText,f.FeedbackResponse,isFeedbackOuteDate,isDeclineChange,isRequestOutDate,t.CreatedDate,f.lastSendTime';
        if ($paramArr['param'] === 'comprehensive') {
            $express = "(f.CommentType = 'neutral' or f.CommentType = 'negative')";
            $express .= " and f.CommentTime > {$NOW}";
        } else {
            $express = "f.CommentType = '{$paramArr['param']}'";
        }
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            if (empty($shopIds)) {
                $shopIds = 0;
            }
            $express .= " and s.shop_id in ({$shopIds})";
        }
        if (! empty($cust)) {
            $express .= " and f.CommentingUser like :user";
            $params[':user'] = '%' . $cust . '%';
        }
        if (! empty($status)) {
            switch ($status) {
                case 'waitingResponse':
                    $express .= " and f.isSendMsg = 1";
                    break;
                case 'sendRequest':
                    $express .= " and f.isResponse = 1";
                    break;
                case 'reqtime':
                    $express .= " and f.isRequestOutDate = 1";
                    break;
                case 'fedtime':
                    $express .= " and f.isFeedbackOuteDate = 1";
                    break;
                case 'decline':
                    $express .= " and f.isDeclineChange = 1";
                    break;
            }
        }
        $conditions = "{$express} and f.shop_id in({$paramArr['shop_id']}) and role = 'Seller'";
        $result['list'] = $this->dbCommand->reset()
            ->select($selects, 'SQL_CALC_FOUND_ROWS')
            ->from("{$this->tableName} f")
            ->join("{$this->shop} s", "f.shop_id = s.shop_id")
            ->leftJoin("ebay_order_transaction t","t.OrderLineItemID = f.OrderLineItemID")
            ->where($conditions, $params)
            ->order('CommentTime Desc')
            ->limit($limit, $offset)
            ->queryAll();
        foreach ($result['list'] as &$value) {
            // guest ItemID hide
            if (Yii::app()->session['userInfo']['user_id'] == 99999) {
                $value['ItemID'] = preg_replace('/(\d{8})\d{4}/', '$1****', $value['ItemID']);
            }
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
     * @desc 判断某个feedbackId是否合法,不合法返回false，一般返回数组，里面包含对应token等
     * @param int $feedbackId ebay_feedback_transaction表自增ID
     * @param int $sellerId 二级用户ID
     * @author liaojianwen
     * @date 2015-08-28
     * @return mixed
     */
    public function lawfulFeedbackID($feedbackId, $sellerId)
    {
        $conditions = 'f.ebay_feedback_transaction_id=:feedbackid and s.seller_id=:seller_id';
        $params = array(
            ':feedbackid' => $feedbackId,
            ':seller_id' => $sellerId
        );
        return $this->dbCommand->reset()
            ->select('f.shop_id,f.FeedbackID,f.CommentingUser,f.ItemID,s.token,s.site_id,f.isResponse,s.nick_name')
            ->from("{$this->tableName} f")
            ->join("{$this->shop} s", 'f.shop_id = s.shop_id')
            ->where($conditions, $params)
            ->limit(1)
            ->queryRow();
    }
    
    /**
     * @desc 查询feedback条数
     * @param string $params
     * @author liaojianwen
     * @date 2015-09-06
     * @return mixed
     */
    public function getFeedbackCount($params)
    {
        $userConfig = UserModel::model()->getUserConfigs();
        $shopIds = implode(',', $userConfig['shops']);
        
        $express = "f.shop_id in ({$params})";
        if (Yii::app()->session['userInfo']['seller_id'] != Yii::app()->session['userInfo']['user_id']) {
            if (empty($shopIds)) {
                $shopIds = 0;
            }
            $express .= " and s.shop_id in ({$shopIds})";
        }
        $count['comprehensive'] = $this->dbCommand->reset()
            ->select('count(*) count')
            ->from("feedbackcomprehensive f")
            ->join("shop s","f.shop_id = s.shop_id")
            ->where($express)
            ->limit(1)
            ->queryRow();
        $count['neutral'] = $this->dbCommand->reset()
            ->select('count(*) count')
            ->from("feedbackneutral f")
            ->join("shop s","f.shop_id = s.shop_id")
            ->where($express)
            ->limit(1)
            ->queryRow();
        $count['negative'] = $this->dbCommand->reset()
            ->select('count(*) count')
            ->from("feedbacknegative f")
            ->join("shop s","f.shop_id = s.shop_id")
            ->where($express)
            ->limit(1)
            ->queryRow();
        $count['positive'] = $this->dbCommand->reset()
            ->select('count(*) count')
            ->from("feedbackpositive f")
            ->join("shop s","f.shop_id = s.shop_id")
            ->where($express)
            ->limit(1)
            ->queryRow();
        return $count;
    }
}