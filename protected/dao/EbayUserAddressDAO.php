<?php

/**
 * @desc ebay_user_address表操作类
 * @author YangLong
 * @date 2015-07-30
 */
class EbayUserAddressDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-07-30
     * @return EbayUserAddressDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-07-30
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'ebay_user_address';
        $this->primaryKey = 'ebay_user_address_id';
        $this->created = 'create_time';
    }

    /**
     * @desc 获取客户地址
     * @param string $buyer_id
     * @author liaojianwen
     * @date 2015-10-31
     * @return mixed
     */
    public function getOrderAddr($buyer_id)
    {
        $selections = 'AddressID,AddressOwner,CityName,Country,CountryName,ExternalAddressID,Name,Phone,PostalCode,StateOrProvince,Street1,Street2,AddressAttributeXML';
        $conditions = "user_id = :buyer_id";
        $params = array(
            ':buyer_id' => $buyer_id
        );
        $result = $this->dbCommand->reset()
            ->select($selections)
            ->from($this->tableName)
            ->where($conditions, $params)
            ->queryRow();
        return $result;
    }
}
