<?php

/**
 * @desc ebay_listing_down主表
 * @author liaojianwen
 * @date 2015-07-28
 */
class EbayListingDownDAO extends BaseDAO
{
    
    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-07-28
     * @return EbayListingDownDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-07-28
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'ebay_listing_down';
        $this->primaryKey = 'down_id';
        $this->created = 'create_time';
        
        $this->shop = 'shop';
    }
    
    /**
     * @desc 获取已经下载的Listing数据
     * @param int $taskNumber
     * @author liaojianwen
     * @date 2015-07-28
     * @return Ambigous <multitype:, mixed>
     */
    public function getListingDownData($taskNumber)
    {
        $this->dbCommand->reset();
        $conditions = 'status=:status or (lastruntime<:lastruntime and runcount<=:runcount)';
        $params = array(
            ':status' => boolConvert::toInt01(false),
            ':lastruntime' => time() - EnumOther::RETURN_DATA_REPICK_TIME,
            ':runcount' => EnumOther::RETURN_DATA_MAX_PICK_COUNT
        );
        $result = $this->dbCommand->select('down_id,seller_id,shop_id,text_json')
            ->from($this->tableName)
            ->where($conditions, $params)
            ->limit($taskNumber)
            ->order('down_id asc')
            ->queryAll();
        return $result;
    }
}