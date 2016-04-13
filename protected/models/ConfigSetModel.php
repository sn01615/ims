<?php

/**
 * @desc 系统设置类
 * @author liaojianwen
 * @date 2015-11-23
 */
class ConfigSetModel extends BaseModel
{

    /**
     * @desc 覆盖父方法,返回当前类的(单)实例
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-11-23
     * @return ConfigSetModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @desc 保存设置
     * @param string $imsconfig            
     * @param string $sellerId            
     * @author liaojianwen
     * @date 2015-11-23
     * @return mixed;
     */
    public function saveConfig($imsconfig, $sellerId)
    {
        if (empty($imsconfig) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'config value is unvalued');
        }
        $user_id = Yii::app()->session['userInfo']['user_id'];
        if ($sellerId === $user_id) {
            $columns = array(
                'seller_id' => $sellerId,
                'config_name' => EnumOther::TASKASSIGN,
                'config_value' => $imsconfig
            );
            $conditions = "seller_id = :sellerID and config_name = :key";
            $params = array(
                ':sellerID' => $sellerId,
                ':key' => EnumOther::TASKASSIGN
            );
            $res = SellerConfigDAO::getInstance()->ireplaceinto($columns, $conditions, $params, true);
            if (empty($res)) {
                return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'save failed');
            }
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'User authentication fails');
        }
    }

    /**
     * @desc 获取系统设置
     * @param string $sellerId
     * @author liaojianwen
     * @date 2015-11-24
     * @return mixed;
     */
    public function getConfig($sellerId)
    {
        if (empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '', 'sellerId is unvalued');
        }
        $columns = array(
            'config_name',
            'config_value'
        );
        $conditions = "seller_id =:seller_id";
        $params = array(
            ':seller_id' => $sellerId
        );
        $result = SellerConfigDAO::getInstance()->iselect($columns, $conditions, $params);
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'data no found');
        }
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
    }
}
