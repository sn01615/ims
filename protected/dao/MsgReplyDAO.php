<?php
/**
 * @desc 信息回复队列表处理
 * @author heguangquan
 * @date 2015-03-05
 */
class MsgReplyDAO extends BaseDAO
{
	/**
	 * @var 定义msg表
	 */
	private $tbMsg;
	
	/**
	 * @var 定义店铺表
	 */
	private $tbShop;
	
	/**
     * @desc 对象实例化
     * @param 当前类名称
     * @author heguangquan
     * @date 2015-03-05
     * @return MsgReplyDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }
    
    /**
     * @desc 初始化方法
     * @author heguangquan
     * @date 2015-03-05
     */
    public function __construct()
    {
    	$this->tableName = 'msg_reply_queue';
        $this->primaryKey = 'down_queue_id';
        $this->tbMsg = 'msg';
        $this->tbShop = 'shop';
        $this->fields = 'down_queue_id,msg_id,token,ExternalMessageID,Sender,content,process_sign';
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->dbCommand->reset();
    }
    
    /**
     * @desc 获取信息回复需要的Token、收件人等
     * @param int $msgId 信息ID
     * @author heguangquan
     * @date 2015-03-05
     * @return array | bool 信息回复数据或是否查询成功
     */
    public function getShopMsgInfo($msgId)
    {
        if (empty($msgId)) {
            return false;
        }
        
        return $this->dbCommand->reset()
            ->select('token,ExternalMessageID,MessageID,Sender,ResponseEnabled')
            ->from($this->tbMsg)
            ->join($this->tbShop, "{$this->tbShop}.shop_id={$this->tbMsg}.shop_id")
            ->where("{$this->tbMsg}.msg_id = {$msgId}")
            ->limit(1)
            ->queryRow();
    }
}