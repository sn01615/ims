<?php

/**
 * @desc 信息下载表
 * @author heguangquan
 * @date 2015-01-28
 */
class MsgDownDAO extends BaseDAO
{

    private $tb_shop;

    private $tb_downqueue;

    private $tb_shop_folder;

    /**
     * @desc 对象实例化
     * @author heguangquan
     * @date 2015-01-28
     * @return MsgDownDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 初始化对象
     * @author heguangquan
     * @date 2015-01-28
     */
    public function __construct()
    {
        $this->tableName = 'eb_msg_down';
        $this->primaryKey = 'down_id';
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tb_shop = 'shop';
        $this->tb_downqueue = 'eb_msg_down_queue';
        $this->tb_shop_folder = 'shop_folder';
    }

    /**
     * @desc 获取需要生成队列的店铺信息
     * @param string $type
     * @author YangLong
     * @date 2015-01-28
     * @return array $data
     */
    public function getEbShop($type = 'msg')
    {
        $lessTime = time() - EnumOther::HEARTBEAT_TIME;
        
        if ($type == 'msg') {
            // 定时获取最新消息队列
            $type = 'msg_down_time';
        } elseif ($type == 'update') {
            // 定时更新Open Case状态队列
            $type = 'open_case_down_time';
            $lessTime = time() - 900;
        } elseif ($type === 'feedback') {
            // 获取feedback
            $type = 'feedback_down_time';
            $lessTime = time() - EnumOther::FEEDBACK_TIME;
        } elseif ($type === 'update_status') {
            // 定时更新All Case状态队列
            $type = 'case_update_status_time';
            $lessTime = time() - EnumOther::CASE_UPLOAD_STATUS_TIME;
        } elseif ($type === 'return') {
            // 获取所有Return 对列
            $lessTime = time() - 60;
            $type = 'return_down_time';
        } elseif ($type === 'return_update') {
            $type = 'return_update_time';
            $lessTime = time() - EnumOther::RETURN_UPDATE_TIME;
        } elseif ($type === 'orders') {
            // 订单获取队列
            $type = 'orders_down_time';
            $lessTime = time() - 288;
        } elseif ($type === 'listing') {
            // listing 获取队列
            $type = 'listing_down_time';
            $lessTime = time() - 3600 * 12;
        } elseif ($type === 'disputes') {
            // disputes 获取队列
            $type = 'disputes_down_time';
            $lessTime = time() - EnumOther::HEARTBEAT_TIME;
        } else {
            // 获取所有Case队列
            $type = 'case_down_time';
        }
        
        $table = $this->tb_shop;
        $conditions = "status=:status and {$type}<:{$type} and is_delete=:false";
        $params = array(
            ':status' => EnumOther::EBAY_ACCOUNT_NORMAL,
            ":{$type}" => $lessTime,
            ':false' => boolConvert::toInt01(false)
        );
        $data = $this->dbCommand->reset()
            ->select(
            "shop_id,AccountID,seller_id,account,site_id,nick_name,token,status,msg_down_time,
                case_update_status_time,case_down_time,open_case_down_time,
                feedback_down_time,orders_down_time,return_down_time,disputes_down_time,HardExpirationTime")
            ->from($table)
            ->where($conditions, $params)
            ->queryAll();
        return $data;
    }

    /**
     * @desc 插入队列
     * @param array $shop 账号信息
     * @param array $folders 文件夹信息
     * @param int $priority 优先级
     * @param int $start 开始时间
     * @param int $end 结束时间
     * @param bool $hisQ 是否为历史队列
     * @author YangLong
     * @date 2015-01-28
     * @return boolean
     */
    public function makeQueue($shop, $folders, $priority, $start, $end, $hisQ = FALSE)
    {
        $postdata = array();
        foreach ($folders as $folder) {
            $fixPriority = $priority;
            if ($folder['FolderID'] == 6) {
                // 默认30天内是下载不到 归档文件夹的 因为邮件还未归档
                if ($start > time() - 3600 * 24 * 30) {
                    if (rand(1, 10) > 1) {
                        continue;
                    }
                }
                $fixPriority -= 100;
            }
            if ($folder['FolderID'] == 1) {
                // 发件箱
                $fixPriority -= 50;
            }
            $Qcolumns = array(
                'seller_id' => $shop['seller_id'],
                'shop_id' => $shop['shop_id'],
                'AccountID' => $shop['AccountID'],
                'site_id' => $shop['site_id'],
                'token' => $shop['token'],
                'folder_id' => $folder['FolderID'],
                'start_time' => $start,
                'end_time' => $end,
                'process_sign' => boolConvert::toInt01(false),
                'priority' => $fixPriority,
                'create_time' => time()
            );
            if ($Qcolumns['start_time'] <= 0) {
                $Qcolumns['start_time'] = 0;
            }
            
            if (! $hisQ) {
                // 合并7天内的队列
                $columns = array(
                    'down_queue_id',
                    'start_time'
                );
                $conditions = 'shop_id=:shop_id and folder_id=:folder_id and start_time>:start_time and process_sign=:process_sign';
                $params = array(
                    ':shop_id' => $Qcolumns['shop_id'],
                    ':folder_id' => $Qcolumns['folder_id'],
                    ':start_time' => time() - 3600 * 24 * 7,
                    ':process_sign' => boolConvert::toInt01(false)
                );
                
                $hQueue = EbayMsgDownQueueDAO::getInstance()->iselect($columns, $conditions, $params);
                
                if (! empty($hQueue)) {
                    $_down_queue_ids = array();
                    foreach ($hQueue as $_key => $_value) {
                        if ($Qcolumns['start_time'] > $_value['start_time']) {
                            $Qcolumns['start_time'] = $_value['start_time'];
                        }
                        $_down_queue_ids[] = $_value['down_queue_id'];
                    }
                    $conditions = EbayMsgDownQueueDAO::getInstance()->getPk() . ' in (' . implode(',', $_down_queue_ids) . ')';
                    $params = array();
                    EbayMsgDownQueueDAO::getInstance()->idelete($conditions, $params);
                }
            }
            
            // count
            iMongo::getInstance()->setCollection('makeMsgQ')->insert(
                array(
                    'shop_id' => $Qcolumns['shop_id'],
                    'folder_id' => $Qcolumns['folder_id'],
                    'start_time' => $Qcolumns['start_time'],
                    'end_time' => $Qcolumns['end_time'],
                    'time' => time()
                ));
            
            array_push($postdata, $Qcolumns);
        }
        $result = EbayMsgDownQueueDAO::getInstance()->iMultiInsert($postdata);
        
        if ($result === false) {
            throw new Exception('"$result = EbayMsgDownQueueDAO::getInstance()->iMultiInsert($postdata);" return a "false" value!');
        }
        
        $columns = array(
            'msg_down_time' => $end
        );
        $conditions = "shop_id=:shop_id and msg_down_time<:end";
        $params = array(
            ':shop_id' => $shop['shop_id'],
            ':end' => $end
        );
        ShopDAO::getInstance()->iupdate($columns, $conditions, $params);
        
        return true;
    }

    /**
     * @desc 根据店铺ID获取店铺里的所有文件夹ID
     * @param int $shop_id
     * @author YangLong
     * @date 2015-02-17
     * @return Ambigous <multitype:, mixed>
     */
    public function getShopFolders($shop_id)
    {
        $params = array(
            ':shop_id' => $shop_id
        );
        return $folders = $this->dbCommand->reset()
            ->select('FolderID')
            ->from($this->tb_shop_folder)
            ->where('shop_id=:shop_id', $params)
            ->queryAll();
    }

    /**
     * @desc 将获取回来的文件夹信息存入数据库
     * @param array $foldersinfo
     * @param int $shop_id
     * @author YangLong
     * @date 2015-02-17
     * @return boolean
     */
    public function setShopFolders($foldersinfo, $shop_id)
    {
        if (is_array($foldersinfo)) {
            $this->dbCommand->delete($this->tb_shop_folder, 'shop_id=:shop_id', array(
                ':shop_id' => $shop_id
            ));
            $result = array();
            foreach ($foldersinfo as $key => $folder) {
                if (is_null($folder['FolderName'])) {
                    $folder['FolderName'] = '';
                }
                if ($folder['FolderID'] > 200) {
                    $result[] = $this->dbCommand->insert($this->tb_shop_folder, 
                        array(
                            'shop_id' => $shop_id,
                            'FolderID' => $folder['FolderID'],
                            'FolderName' => $folder['FolderName'],
                            'create_time' => time()
                        ));
                }
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     * @desc 获取下载队列数据
     * @param number $limit
     * @author YangLong
     * @date 2015-01-28
     * @return mixed|boolean
     */
    public function getDownQueueData($limit = 1)
    {
        $table = $this->tb_downqueue;
        $conditions = "process_sign=:process_sign or (lastruntime<:lastruntime and runcount<:runcount)";
        $params = array(
            ':process_sign' => boolConvert::toInt01(false),
            ':lastruntime' => time() - EnumOther::HEARTBEAT_TIME * 6,
            ':runcount' => EnumOther::MAX_RUN_COUNT
        );
        $data = $this->dbCommand->reset()
            ->select('down_queue_id,AccountID,seller_id,shop_id,site_id,token,folder_id,start_time,end_time')
            ->from($table)
            ->where($conditions, $params)
            ->limit($limit)
            ->order("priority desc,down_queue_id asc")
            ->queryAll();
        
        if (! empty($data)) {
            $down_queue_id = array();
            foreach ($data as $key => $value) {
                $down_queue_id[] = $value['down_queue_id'];
            }
            $down_queue_id = implode(',', $down_queue_id);
            $columns = array(
                'process_sign' => 1,
                'lastruntime' => time()
            );
            $conditions = "down_queue_id in ({$down_queue_id})";
            $this->dbCommand->reset()->update($table, $columns, $conditions);
            $this->dbCommand->reset()
                ->setText("UPDATE {$table} SET runcount = runcount + 1 WHERE down_queue_id IN ({$down_queue_id})")
                ->execute();
            return $data;
        } else {
            return false;
        }
    }

    /**
     * @desc 删除完成的队列数据
     * @param number $id    
     * @author YangLong
     * @date 2015-01-28
     * @return number
     */
    public function deleteDownQueue($id)
    {
        $table = $this->tb_downqueue;
        $conditions = "down_queue_id=:down_queue_id";
        $params = array(
            ':down_queue_id' => $id
        );
        $data = $this->dbCommand->reset()->delete($table, $conditions, $params);
        return $data;
    }
}