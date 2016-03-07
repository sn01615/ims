<?php

/**
 * @desc Msg表操作类
 * @author liaojianwen
 * @date 2015-1-28
 */
class MsgDealDAO extends BaseDAO
{

    private $shop;

    private $msg;

    private $msg_tp_class;

    private $msg_tp_list;
    
    private $msg_text;
    
    private $msg_content;

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-1-28           
     * @return MsgDealDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 初始化
     * @author liaojianwen
     * @date 2015-1-28
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = MsgDAO::getInstance()->getTableName();
        $this->primaryKey = 'msg_id';
        
        $this->shop = 'shop';
        $this->msg = 'msg';
        $this->msg_tp_class = 'msg_tp_class';
        $this->msg_tp_list = 'msg_tp_list';
        $this->msg_text = 'msg_text';
        $this->msg_content = 'msg_content';
    }

    /**
     * @desc 获取回复邮件时需要的必要信息
     * @param string $mids  
     * @author YangLong
     * @date 2015-01-30          
     * @return array 回复邮件信息
     * 
     */
    public function getReplyRInfo($mids)
    {
        $this->dbCommand->reset();
        return $this->dbCommand->select('m.ExternalMessageID,m.Sender,s.token,s.site_id')
            ->from("{$this->tableName} m")
            ->join("{$this->shop} s", "m.shop_id=s.shop_id")
            ->where("m.{$this->primaryKey} in ({$mids}) and s.`status`=" . EnumOther::EBAY_ACCOUNT_NORMAL)
            ->queryAll();
    }

    /**
     * @desc 处理各个接口方法
     * @param string $ids            
     * @param string $setExpress  
     * @author liaojianwen
     * @date 2015-01-30          
     * @return int 影响行数
     */
    public function dealMsg($ids, $setExpress)
    {
        $this->dbCommand->reset();
        $sql = "update " . $this->tableName . " set {$setExpress} where " . $this->primaryKey . " in({$ids})";
        $this->dbCommand->setText($sql);
        $result = $this->dbCommand->execute();
        if ($result > 0) {
            $_arr['status'] = true;
        } else {
            $_arr['status'] = false;
        }
        return $_arr;
    }

    /**
     * @desc 获取消息字段
     * @param string $express  查询条件         
     * @param integer $param   查询条件值        
     * @param integer $page    页码      
     * @param integer $pageSize  页大小 
     * @param string $searchCon
     * @param string $listType
     * @author liaojianwen
     * @date 2015-01-30         
     * @return array 消息列表数据
     * @modify 2015-03-05 YangLong
     */
    public function getMessageList($express, $param, $page, $pageSize, $searchCon, $listType)
    {
        $msg = $this->tableName;
        $shop = $this->shop;
        $limit = $pageSize;
        $offset = ($page - 1) * $limit;
        $pageInfo['page'] = $page;
        $pageInfo['pageSize'] = $pageSize;
        $selects = 'm.msg_id,m.Sender,m.RecipientUserID,m.SendToName,
                    m.Subject,m.MessageID,m.Flagged,m.Read,m.ReceiveDate,m.FolderID,m.Replied,
                    m.is_star,m.handled,m.is_delete,m.send_status,m.shop_id,
                    s.account,s.nick_name,m.is_img,
                    o.BuyerCheckoutMessage';
        $params = array(
            ':param' => $param['param']
        );
        $order = array(
            'ReceiveDate DESC',
            'msg_id DESC'
        );
        
        $conditions = "{$express} and m.shop_id in ({$param['shop_id']})";
        
        $this->dbCommand->reset()
            ->select($selects, 'SQL_CALC_FOUND_ROWS')
            ->from($msg . ' m')
            ->join(ShopDAO::getInstance()->getTableName() . ' s', 'm.shop_id=s.shop_id and s.is_delete=0')
            ->leftJoin(MsgTextResolveDAO::getInstance()->getTableName() . ' tr', 'tr.msg_id=m.msg_id')
            ->leftJoin(EbayOrdersDAO::getInstance()->getTableName() . ' o', 'o.OrderID=tr.OrderId and o.shop_id=m.shop_id')
            ->where($conditions, $params);
        
        if ($listType == EnumOther::IMS_LABEL) {
            $this->dbCommand->join(MsgLabelRefDAO::getInstance()->getTableName() . ' mlr', 'mlr.msg_id=m.msg_id');
        }
        
        // auto tag
        if ($listType == EnumOther::IMS_AUTOTAG) {
            $this->dbCommand->join(MsgAutoLabelRefDAO::getInstance()->getTableName() . ' malr', 'malr.msg_id=m.msg_id');
        }
        
        if (! empty($searchCon) || $searchCon == '0') {
            $otherParam = "CONCAT(m.`Sender`,m.`SendToName`,m.`Subject`,s.`nick_name`) like :context";
            $this->dbCommand->andwhere($otherParam, array(
                ':context' => '%' . $searchCon . '%'
            ));
        }
        $this->dbCommand->order($order)->limit($limit, $offset);
        $result = $this->dbCommand->queryALL();
        
        $total = $this->dbCommand->reset()
            ->setText('select found_rows()')
            ->queryScalar();
        $total = intval($total);
        
        foreach ($result as $_key => $_value) {
            // 别名
            $result[$_key]['Sender'] = str_ireplace($result[$_key]['account'], $result[$_key]['nick_name'], $result[$_key]['Sender']);
            $result[$_key]['RecipientUserID'] = str_ireplace($result[$_key]['account'], $result[$_key]['nick_name'], $result[$_key]['RecipientUserID']);
            $result[$_key]['SendToName'] = str_ireplace($result[$_key]['account'], $result[$_key]['nick_name'], $result[$_key]['SendToName']);
            $result[$_key]['Subject'] = str_ireplace($result[$_key]['account'], $result[$_key]['nick_name'], $result[$_key]['Subject']);
            unset($result[$_key]['account']);
            unset($result[$_key]['nick_name']);
            
            // 标签获取
            $columns = array(
                'ml.msg_label_id',
                'ml.label_title',
                'ml.label_color'
            );
            $conditions = 'msg_id=:msg_id';
            $params = array(
                ':msg_id' => $result[$_key]['msg_id']
            );
            $joinArray = array(
                array(
                    MsgLabelDAO::getInstance()->getTableName() . ' ml',
                    'mlr.msg_label_id=ml.msg_label_id'
                )
            );
            $result[$_key]['labels'] = MsgLabelRefDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 'mlr');
            
            // guest ItemID hide
            if (Yii::app()->session['userInfo']['user_id'] == 99999) {
                $result[$_key]['Subject'] = preg_replace('/(\d{8})\d{4}/', '$1****', $result[$_key]['Subject']);
            }
        }
        
        return array(
            'list' => $result,
            'count' => $total,
            'page' => $pageInfo
        );
    }
    
    /**
     * @desc 查询待处理数
     * @param string $express 查询条件
     * @param array $param 查询条件的值
     * @author liaojianwen
     * @date 2015-06-11
     * @modify 2015-09-20 YangLong 分开统计
     * @return mixed
     */
    public function getDisposeCount($express, $param)
    {
        $params = array(
            ':param' => $param['param']
        );
        $conditions = "{$express} and shop_id in ({$param['shop_id']})";
        $res = $this->dbCommand->reset()
            ->select('count(*) as count')
            ->from($this->tableName)
            ->where($conditions, $params)
            ->group('Read')
            ->order('Read asc')
            ->queryColumn();
        return $res;
    }
    
    /**
     * @desc 添加模板分类
     * @param string $classname 模板类名称         
     * @param integer $pid   模板父ID  
     * @author YangLong
     * @date 2015-02-12
     * @return bool | int 影响行数
     */
    public function addTpClass($classname, $pid)
    {
        $table = $this->msg_tp_class;
        if ($pid > 0) {
            $data = $this->dbCommand->select('tp_class_id')
                ->from($table)
                ->where('tp_class_id=' . $pid)
                ->queryAll();
        }
        if ($pid == 0 || ! empty($data)) {
            $columns = array(
                'classname' => $classname,
                'pid' => $pid,
                'update_time' => time(),
                'create_time' => time()
            );
            $this->dbCommand->reset();
            return $this->dbCommand->insert($table, $columns);
        } else {
            return false;
        }
    }

    /**
     * @desc 获取模板分类列表
     * @param integer $pid  父ID
     * @param boolean $alternative jstree格式
     * @author YangLong
     * @date 2015-02-12
     * @return array 模板分类列表
     */
    public function getTpClassList($pid = -1, $alternative = false)
    {
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $table = $this->msg_tp_class;
        $conditions = 'is_delete=:is_delete and seller_id=:seller_id';
        $params = array(
            ':is_delete' => boolConvert::toInt01(false),
            ':seller_id' => $sellerId
        );
        if ($alternative) {
            $sel = 'tp_class_id id,pid parent,classname text';
        } else {
            $sel = 'tp_class_id,pid,classname';
        }
        if ($pid !== - 1) {
            $conditions .= ' and pid=:pid';
            $params[':pid'] = $pid;
        }
        $data = $this->dbCommand->select($sel)
            ->from($table)
            ->where($conditions, $params)
            ->queryAll();
        if ($alternative) {
            foreach ($data as &$row) {
                if ($row['parent'] == '0') {
                    $row['parent'] = '#';
                }
            }
            unset($row);
        }
        return $data;
    }
    
    /**
     * @desc 模板分类的编辑和添加
     * @param int $cid 分类ID
     * @param int $pid 分类的父ID
     * @param string $classname 分类名称
     * @return array
     * @author YangLong
     * @date 2015-02-25
     */
    public function tpClassEdit($cid, $pid, $classname)
    {
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $table = $this->msg_tp_class;
        if (empty($cid)) {
            // add new
            $columns = array(
                'seller_id' => $sellerId,
                'pid' => $pid,
                'classname' => $classname,
                'update_time' => time(),
                'create_time' => time()
            );
            $result['Ack'] = 'Success';
            $result['affected_rows'] = $this->dbCommand->insert($table, $columns);
            // 获取刚刚插入的数据
            $conditions = 'tp_class_id=:tp_class_id';
            $params = array(
                ':tp_class_id' => $this->dbConnection->lastInsertID
            );
            $this->dbCommand->reset();
            $result['data'] = $this->dbCommand->select('tp_class_id id,pid parent,classname text')
                ->from($table)
                ->where($conditions, $params)
                ->limit(1)
                ->queryRow();
        } else {
            // edit
            $columns = array(
                'classname' => $classname,
                'update_time' => time()
            );
            if ($pid !== - 1) {
                $columns['pid'] = $pid;
            }
            $conditions = 'tp_class_id=:tp_class_id and seller_id=:seller_id';
            $params = array(
                ':tp_class_id' => $cid,
                ':seller_id' => $sellerId
            );
            $result['Ack'] = 'Success';
            $result['affected_rows'] = $this->dbCommand->update($table, $columns, $conditions, $params);
            // 获取刚刚更新的数据
            $conditions = 'tp_class_id=:tp_class_id';
            $params = array(
                ':tp_class_id' => $cid
            );
            $this->dbCommand->reset();
            $result['data'] = $this->dbCommand->select('tp_class_id id,pid parent,classname text')
                ->from($table)
                ->where($conditions, $params)
                ->limit(1)
                ->queryRow();
        }
        return $result;
    }
    
    /**
     * @desc 模板分类的移动
     * @param int $cid 分类ID
     * @param int $pid 分类的父ID
     * @return array
     * @author YangLong
     * @date 2015-03-02
     */
    public function tpClassMove($cid, $pid)
    {
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $table = $this->msg_tp_class;
        
        // move
        $columns = array(
            'pid' => $pid,
            'update_time' => time()
        );
        $conditions = 'tp_class_id=:tp_class_id and seller_id=:seller_id';
        $params = array(
            ':tp_class_id' => $cid,
            ':seller_id' => $sellerId
        );
        $result['Ack'] = 'Success';
        $result['affected_rows'] = $this->dbCommand->update($table, $columns, $conditions, $params);
        // 获取刚刚更新的数据
        $conditions = 'tp_class_id=:tp_class_id';
        $params = array(
            ':tp_class_id' => $cid
        );
        $this->dbCommand->reset();
        $result['data'] = $this->dbCommand->select('tp_class_id id,pid parent,classname text')
            ->from($table)
            ->where($conditions, $params)
            ->limit(1)
            ->queryRow();
        return $result;
    }
    
    /**
     * @desc 根据消息ID获取token
     * @param string $mids 信息ID
     * @author YangLong
     * @date 2015-02-12
     * @return array token信息
     */
    public function getTokensByMids($mids)
    {
        if (empty($mids)) {
            return false;
        }
        
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $params = array(
            ':seller_id' => $sellerId
        );
        $columns = array(
            'm.MessageID',
            's.token',
            'm.Read',
            's.site_id',
            's.shop_id'
        );
        return $this->dbCommand->select($columns)
            ->from(MsgDAO::getInstance()->getTableName() . ' m')
            ->join('shop s', 'm.shop_id=s.shop_id')
            ->where("s.seller_id=:seller_id and m.msg_id in ($mids)", $params)
            ->queryAll();
    }

    /**
     * @desc 获取模板类别列表
     * @param int $pid  模板父ID
     * @param int $sellerId 客户ID
     * @param array $pageInfo     分页
     * @param int $type   是否分页
     * @author heguangquan
     * @date 2015-02-03
     * @return array $tpList 模板列表数据
     */
    public function getTpList($pid, $sellerId, $pageInfo, $type)
    {
        if (empty($sellerId) || empty($pageInfo)) {
            return array(
                'list' => '',
                'count' => ''
            );
        }
        
        $tpClass = $this->msg_tp_class;
        $tpList = $this->msg_tp_list;
        $limit = $pageInfo['pageSize'];
        $offset = ($pageInfo['page'] - 1) * $limit;
        
        $conditions = "c.seller_id=:sellerId and l.is_delete=:delete";
        $params = array(
            ':sellerId' => $sellerId,
            ':delete' => 0
        );
        if ($pid !== '0') {
            $pid = $this->getSubClassId($pid, $sellerId);
            $conditions .= " and l.class_id in ({$pid})";
        }
        $this->dbCommand->reset();
        $this->dbCommand->select('l.tp_list_id,l.class_id,l.title,l.content,c.classname', 'SQL_CALC_FOUND_ROWS')
            ->from("{$tpList} l")
            ->join("{$tpClass} c", "c.tp_class_id = l.class_id")
            ->where($conditions, $params)
            ->order("l.update_time desc");
        if (empty($type)) {
            $this->dbCommand->limit($limit, $offset);
        }
        $tpArr['list'] = $this->dbCommand->queryAll();
        
        $this->dbCommand->reset();
        $pageInfo['count'] = $this->dbCommand->setText('select found_rows()')->queryScalar(); // 获取总记录行数
        $tpArr['page'] = $pageInfo;
        
        return $tpArr;
    }

    /**
     * @desc 递归获取子类ID 
     * @param int $pid
     * @param int $sellerId
     * @return string
     * @author YangLong
     * @date 2015-02-25
     */
    private function getSubClassId($pid, $sellerId)
    {
        $tpClass = $this->msg_tp_class;
        $params = array(
            ':seller_id' => $sellerId,
            ':is_delete' => boolConvert::toInt01(false)
        );
        $all = $this->dbCommand->select('tp_class_id,pid')
            ->from($tpClass)
            ->where('seller_id=:seller_id and is_delete=:is_delete', $params)
            ->queryAll();
        $result = "$pid";
        $pid = explode(',', $pid);
        foreach ($pid as $value) {
            $result .= ',' . $this->getSubClassIdOne($all, $value);
        }
        $result = CInputFilter::norepeatInts($result);
        return $result;
    }
    
    /**
     * @desc 从数组里递归获取子类ID
     * @param array $all
     * @author YangLong
     * @date 2015-02-20
     * @return string
     */
    private function getSubClassIdOne($all, $pid)
    {
        $result = "$pid";
        foreach ($all as $key => $value) {
            if ($value['pid'] == $pid) {
                $result .= ',' . $value['tp_class_id'];
                $result .= ',' . $this->getSubClassIdOne($all, $value['tp_class_id']);
            }
        }
        return $result;
    }
    
    /**
     * @desc 删除模板列表
     * @param string $tpId 删除模板的ID
     * @author heguangquan
     * @date 2015-02-03
     */
    public function deteleTpList($tid)
    {
        if (empty($tid)) {
            return false;
        }
        $this->dbCommand->reset();
        $result = $this->dbCommand->update($this->msg_tp_list, array(
            'is_delete' => boolConvert::toInt01(true)
        ), "tp_list_id in (" . $tid . ")");
        return $result;
    }
	
    /**
     * @desc 添加模板信息
     * @param int $pid 父ID
     * @param string $className 需要实例化的类名
     * @param string $title 标题
     * @param string $content 内容
     * @author heguangquan
     * @date 2015-02-03
     */
    public function addTpList($sellerId, $pid, $className, $title, $content)
    {
        if (empty($pid) || empty($title) || empty($content) || empty($sellerId)) {
            return false;
        }
        
        $this->dbCommand->reset();
        $tpColumns = array(
            'class_id' => $pid,
            'title' => $title,
            'content' => $content,
            'is_delete' => boolConvert::toInt01(false),
            'update_time' => time(),
            'create_time' => time()
        );
        
        if (! empty($className)) {
            $columns = array(
                'seller_id' => $sellerId,
                'pid' => $pid,
                'classname' => $className,
                'is_delete' => boolConvert::toInt01(false),
                'update_time' => time(),
                'create_time' => time()
            );
            $this->dbCommand->insert($this->msg_tp_class, $columns);
            $tid = $this->dbConnection->getLastInsertID();
            if (! empty($tid)) {
                $tpColumns['class_id'] = $tid;
                $this->dbCommand->reset();
                $this->dbCommand->insert($this->msg_tp_list, $tpColumns);
                $result = $this->dbConnection->getLastInsertID();
            }
        } else {
            $this->dbCommand->insert($this->msg_tp_list, $tpColumns);
            $result = $this->dbConnection->getLastInsertID();
        }
        return $result;
    }

    /**
     * @desc 模板编辑和添加
     * @param int $tpId 添加模板分类的父ID
     * @param int $classId 模板列表的父ID
     * @param string $className 需要实例化的类名
     * @param string $title 问题标题
     * @param string $content 内容
     * @param int $sellerId 客户ID
     * @author YangLong
     * @date 2015-02-12
     * @return number|mixed 添加模板的ID
     */
    public function tpEdit($tpId, $classId, $className, $title, $content, $sellerId)
    {
        // 新建分类
        if (! empty($className)) {
            $this->dbCommand->reset();
            $this->dbCommand->insert($this->msg_tp_class, array(
                'seller_id' => $sellerId,
                'classname' => $className,
                'pid' => $classId,
                'update_time' => time(),
                'create_time' => time()
            ));
            $classId = $this->dbConnection->getLastInsertID();
        }
        // 插入,$tpId==='0'时,新建一条记录
        if (empty($tpId)) {
            $this->dbCommand->reset();
            $result['status'] = $this->dbCommand->insert($this->msg_tp_list, array(
                'class_id' => $classId,
                'title' => $title,
                'content' => $content,
                'update_time' => time()
            ));
            $params = array(
                ':tp_list_id' => $this->dbConnection->getLastInsertID()
            );
            $this->dbCommand->reset();
            $result['row'] = $this->dbCommand->select('tp_list_id,class_id,title,content')
                ->from($this->msg_tp_list)
                ->where('tp_list_id=:tp_list_id', $params)
                ->limit(1)
                ->queryRow();
        } else {
            // 升级
            $this->dbCommand->reset();
            $p = $this->dbCommand->select('tp_list_id')
                ->from("{$this->msg_tp_list} l")
                ->join("{$this->msg_tp_class} c", 'l.class_id=c.tp_class_id and c.seller_id=:seller_id', array(
                    ':seller_id' => $sellerId
                ))
                ->queryScalar();
            // 判断是否存在和有权修改,由于没有seller_id字段,需要通过msg_tp_class表的该字段来判断是否是自己的记录，防止update了别人的记录
            if (! empty($p)) {
                // 存在更新
                $columns = array(
                    'title' => $title,
                    'content' => $content,
                    'update_time' => time()
                );
                if (! empty($classId)) {
                    // $classId为空的时候表示不修改分类
                    $columns['class_id'] = $classId;
                }
                $params = array(
                    ':tp_list_id' => $tpId
                );
                $this->dbCommand->reset();
                $result['status'] = $this->dbCommand->update($this->msg_tp_list, $columns, 'tp_list_id=:tp_list_id', $params);
                
                $this->dbCommand->reset();
                $result['row'] = $this->dbCommand->select('tp_list_id,class_id,title,content')
                    ->from($this->msg_tp_list)
                    ->where('tp_list_id=:tp_list_id', $params)
                    ->limit(1)
                    ->queryRow();
            } else {
                $result['status'] = false;
                $result['msg'] = 'tid error, no record found. tid is tp_list_id.';
            }
        }
        return $result;
    }

    /**
     * @desc 根据 模板的自增ID获取模板详情，包括分类名称
     * @param int $tpId
     * @param int $sellerId
     * @author YangLong
     * @date 2015-01-12
     * @return mixed
     */
    public function getTp($tpId, $sellerId)
    {
        return $this->dbCommand->select('t.tp_list_id,t.class_id,t.title,t.content,tc.classname')
            ->from("{$this->msg_tp_list} t")
            ->join("{$this->msg_tp_class} tc", 't.class_id=tc.tp_class_id')
            ->where('tc.seller_id=:seller_id and t.tp_list_id=:tp_list_id', array(
                ':seller_id' => $sellerId,
                ':tp_list_id' => $tpId
            ))
            ->limit(1)
            ->queryRow();
    }
    
    /**
     * @desc 获取模板明细
     * @param int $tid  模板主键
     * @author heguangquan
     * @date 2015-02-03
     * @return array | bool 模板明细
     */
    public function getTpDetail($tid)
    {
        if (empty($tid)) {
            return false;
        }
        
        $tpClass = $this->msg_tp_class;
        $tpList = $this->msg_tp_list;
        $this->dbCommand->reset();
        $result = $this->dbCommand->select("tp_list_id,title,content,{$tpClass}.tp_class_id,{$tpClass}.classname,{$tpClass}.pid")
            ->from($tpList)
            ->join($tpClass, "{$tpClass}.tp_class_id = {$tpList}.class_id")
            ->where('tp_list_id=:listId', array(
                ':listId' => $tid
            ))
            ->queryAll();
        $resultArr = array_pop($result);
        $record['pid'] = $resultArr['pid'];
        $i = 0;
        $recordCount = 1;
        while (($recordCount > 0)) {
            $i ++;
            $this->dbCommand->reset();
            $record = $this->dbCommand->select('classname,pid')
                ->from($tpClass)
                ->where('tp_class_id=:classId', array(
                    ':classId' => $record['pid']
                ))
                ->queryAll();
            $recordCount = count($record);
            $record = array_pop($record);
            if ($recordCount) {
                $resultArr['classname' . $i] = $record['classname'];
            }
        }
        ;
        return $resultArr;
    }

    /**
     * @desc 将模板分类标记为已删除
     * @param string $cids            
     * @return number
     * @author YangLong
     */
    public function deleteTpClass($cids)
    {
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        
        $table = $this->msg_tp_class;
        $list = $this->msg_tp_list;
        $cids = $this->getSubClassId($cids, $sellerId);
        $columns = array(
            'is_delete' => boolConvert::toInt01(true)
        );
        $conditions = "tp_class_id in ({$cids})";
        $result['Ack'] = 'Success';
        $result['affected_rows'] = $this->dbCommand->update($table, $columns, $conditions);
        $conditions = "class_id in ({$cids})";
        $result['list_affected_rows'] = $this->dbCommand->update($list, $columns, $conditions);
        $result['data'] = $cids;
        return $result;
    }

    /**
     * @desc 根据message的自增ID获取token和MessageID
     * @author YangLong
     * @date 2015-02-12
     * @param string $mids            
     * @return Ambigous <multitype:, mixed>
     */
    public function getMIByMID($mids)
    {
        $m = $this->msg;
        $s = $this->shop;
        return $this->dbCommand->select('m.MessageID,s.token,s.site_id')
            ->from("$m m")
            ->join("$s s", 'm.shop_id=s.shop_id')
            ->where("msg_id in ($mids)")
            ->queryAll();
    }
    
    /**
     * @desc 获取内容页上一封及下一封信息的ID
     * @param int $receiveDate 接收时间
     * @param array $paramArr 条件
     * @param array $paramVal 条件值
     * @param int $shopId 店铺ID,多个店铺ID以,隔开
     * @param int $msgId 当前邮件的ID
     * @author heguangquan
     * @date 2015-02-12
     * @return array ID(上一封、下一封)
     * @modify YangLong 2015-05-28 重构SQL优化性能
     */
    public function getPreNexID($receiveDate, $paramArr, $paramVal, $shopId, $msgId)
    {
        $msg = $this->msg;
        $shop = $this->shop;
        // 获取上一封邮件的ID
        $pre = $paramArr;
        $pre[] = "ReceiveDate > {$receiveDate} and shop_id in ({$shopId})";
        $pre[] = 'msg_id <> ' . $msgId;
        $preArr = $this->dbCommand->reset()
            ->select('msg_id preId')
            ->from($msg)
            ->where($pre, $paramVal)
            ->order('ReceiveDate asc')
            ->limit(1)
            ->queryRow();
        // 获取下一封邮件的ID
        $next = $paramArr;
        $next[] = "shop_id in ({$shopId}) and ReceiveDate <{$receiveDate}";
        
        $nextArr = $this->dbCommand->reset()
            ->select('msg_id nexId')
            ->from($msg)
            ->where($next, $paramVal)
            ->order('ReceiveDate desc')
            ->limit(1)
            ->queryRow();
        $resultArr = array();
        if (! empty($preArr)) {
            $resultArr = array_merge($resultArr, $preArr);
        }
        if (! empty($nextArr)) {
            $resultArr = array_merge($resultArr, $nextArr);
        }
        return $resultArr;
    }
    
    /**
     * @desc 获取用户关联的消息
     * @param string $buyerId
     * @param string $shopId
     * @author liaojianwen
     * @date 2015-09-25
     * @return Ambigous <multitype:, mixed>
     */
    public function getMsgListByClientId($buyerId,$shopId,$page,$pageSize)
    {
        $limit = $pageSize=5;
        $offset = ($page - 1) * $limit;
        $pageInfo['page'] = $page;
        $pageInfo['pageSize'] = $pageSize;
        $selects = 'm.msg_id,m.Sender,m.RecipientUserID,m.SendToName,
                    m.Subject,m.MessageID,m.ReceiveDate,
                    m.is_star,m.handled,m.is_delete,m.send_status,m.shop_id,
                    s.account,s.nick_name';
        $conditions1 = "(m.Sender = '{$buyerId}') and aggregate_hide =1 and FolderID <> 1  and s.shop_id in ({$shopId})";
        $conditions2 = "(m.SendToName ='{$buyerId}') and aggregate_hide =1 and FolderID <> 1 and s.shop_id in ({$shopId})";
        $order = array(
            'ReceiveDate DESC',
            'msg_id DESC'
        );
        $result = $this->dbCommand->reset()
            ->select($selects, 'SQL_CALC_FOUND_ROWS')
            ->from("{$this->tableName} m")
            ->join("{$this->shop} s", "s.shop_id = m.shop_id")
            ->where($conditions1)
            ->group("msg_id")
            ->order($order)
            ->limit($limit, $offset)
            ->union("select {$selects} from {$this->tableName} m join shop s on s.shop_id = m.shop_id where {$conditions2} group by msg_id")
            ->queryAll();
        $total = $this->dbCommand->reset()
            ->setText('select found_rows()')
            ->queryScalar();
        
        return array(
            'list' => $result,
            'count' => $total,
            'page' => $pageInfo
        );
    }
}