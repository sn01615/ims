<?php
/**
 * @desc EBAY Listing 的描述表
 * @author liaojianwen
 * @date 2015-07-29
 */
class EbayListingDescDAO extends BaseDAO{
	/**
	 * @desc 对象实例重用
	 * @param string $className
	 * @return EbayListingDescDAO
	 * @author liaojianwen
	 * @date 2015-07-29
	 */
	public static function getInstance($className = __CLASS__)
	{
		return parent::createInstance($className);
	}
	
	/**
	 * @desc 初始化方法
	 * @author liaojianwen
	 * @date 2015-07-29
	 */
	public function __construct()
	{
	    
	    $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
		$this->tableName = 'ebay_listing_desc';
		$this->primaryKey = 'desc_id';
		$this->fields = array('desc_id','listing_id','description');

	}
}