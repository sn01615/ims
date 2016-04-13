<?php

/**
 * @desc case_note操作类
 * @author lvjianfei
 * @date 2015-04-03
 */
class ItemNoteDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author lvjianfei
     * @date 2015-04-03
     * @return ItemNoteDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author lvjianfei
     * @date 2015-04-03
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'item_note';
        $this->primaryKey = 'item_note_id';
        $this->created = 'create_time';
        $this->shop = 'shop';
        $this->case = 'case';
        $this->msg = MsgDAO::getInstance()->getTableName();
        $this->return = 'return_request';
        $this->detail = 'return_request_detail';
    }

    /**
     * @desc 获取对应case的备注信息
     * @param caseid  case的id
     * @param shopId  当前用户所拥有店铺的id
     * @author lvjianfei,liaojianwen
     * @return resultArr 
     * @date 2015-04-08
     * @modify 2015-04-21
     */
    public function getItemNote($itemId, $shopId, $type, $dealId)
    {
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $selects = 'a.author_name,a.text,a.create_time';
        if ($type === 'case') {
            $res = $this->dbCommand->reset()
                ->select('c.user_userId,c.user_role,c.otherParty_userId,c.otherParty_role')
                ->from("{$this->case} c")
                ->join("{$this->shop} s", "c.shop_id = s.shop_id")
                ->where("c.case_id ={$dealId} and s.seller_id = {$sellerId}")
                ->limit(1)
                ->queryRow();
            if ($res['user_role'] == 'BUYER') {
                $cust = $res['user_userId'];
            } elseif ($res['otherParty_role'] == 'BUYER') {
                $cust = $res['otherParty_userId'];
            } else {
                $cust = '';
            }
            
            // $condition = "b.shop_id in ({$shopId}) and a.item_id={$itemId} and a.cust = '{$cust}'";
            $param = "a.item_id = b.i_itemId and b.case_id ={$dealId}";
        } elseif ($type === 'return') {
            $res = $this->dbCommand->reset()
                ->select('d.S_buyerLoginName')
                ->from("{$this->return} r")
                ->join("{$this->shop} s", "s.shop_id = r.shop_id")
                ->join("{$this->detail} d", "r.return_request_id = d.return_id")
                ->where("r.return_request_id ={$dealId} and s.seller_id = {$sellerId}")
                ->limit(1)
                ->queryRow();
            $cust = $res['S_buyerLoginName'];
            $param = "b.return_request_id = d.return_id  and d.return_id ={$dealId}";
            $this->case = $this->return;
        } else {
            $res = $this->dbCommand->reset()
                ->select('m.Sender,m.SendToName,s.account')
                ->from("{$this->msg} m")
                ->join("{$this->shop} s", "m.shop_id = s.shop_id")
                ->where("m.msg_id ={$dealId} and s.seller_id = {$sellerId}")
                ->limit(1)
                ->queryRow();
            if ($res['account'] != $res['Sender'] && $res['Sender'] != 'eBay') {
                $cust = $res['Sender'];
            } else {
                $cust = $res['SendToName'];
            }
            // $condition = "b.shop_id in ({$shopId}) and a.item_id={$itemId} and a.cust = '{$cust}'";
            $param = "a.item_id = b.ItemID and b.msg_id ={$dealId}";
            $this->case = $this->msg;
        }
        $condition = "b.shop_id in ({$shopId}) and a.item_id='{$itemId}' and a.cust = '{$cust}'";
        $express = $this->dbCommand->reset()
            ->select($selects)
            ->from("{$this->tableName} a");
        if ($type == 'return') {
            return $this->dbCommand->Join("{$this->detail} d", "a.item_id = d.D_iD_itemId ")
                ->join("{$this->case} b", $param)
                ->where($condition)
                ->order("{$this->primaryKey} desc")
                ->queryAll();
        } else {
            return $this->dbCommand->join("{$this->case} b", $param)
                ->where($condition)
                ->order("{$this->primaryKey} desc")
                ->queryAll();
        }
    }
}
