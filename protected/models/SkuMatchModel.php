<?php

/**
 * @desc SKU匹配相关
 * @author YangLong
 * @date 2015-11-21
 */
class SkuMatchModel extends BaseModel
{
    
    /**
     * @desc 覆盖父方法,返回当前类的(单)实例
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-11-21
     * @return SkuMatchModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * @desc 获取已经上传的SKU列表
     * @param int $page
     * @param int $pageSize
     * @param int $sellerId
     * @param string $keywords
     * @author YangLong
     * @date 2015-11-21
     * @return mixed
     */
    public function getSkuList($page, $pageSize, $sellerId, $keywords)
    {
        if (empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
        
        $columns = array(
            'sku.user_id',
            'sku.SKU',
            'u.username',
            'u.realname'
        );
        $conditions = 'seller_id=:seller_id';
        $params = array(
            ':seller_id' => $sellerId
        );
        $joinArray = array(
            array(
                UserDAO::getInstance()->getTableName() . ' u',
                'u.user_id=sku.user_id'
            )
        );
        $order = 'sku.create_time desc';
        $limit = $pageSize;
        $offset = ($page - 1) * $pageSize;
        
        // search
        if (! empty($keywords)) {
            $conditions .= ' and SKU like :SKU ';
            $params[':SKU'] = "%{$keywords}%";
        }
        
        $result['list'] = UserSkuDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, 'sku', $order, $limit, $offset);
        $result['count'] = UserSkuDAO::getInstance()->getCount($conditions, $params);
        if ($result['list'] === false) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        }
    }
    
    /**
     * @desc 获取seller下的所有用户
     * @param int $sellerId
     * @author YangLong
     * @date 2015-11-21
     * @return mixed
     */
    public function getSellerUsers($sellerId)
    {
        if (empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
        
        $columns = array(
            'user_id',
            'username',
            'realname'
        );
        $conditions = 'pid=:pid and is_delete=:false or user_id=:pid';
        $params = array(
            ':pid' => $sellerId,
            ':false' => boolConvert::toInt01(false)
        );
        $result = UserDAO::getInstance()->iselect($columns, $conditions, $params);
        if ($result === false) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        }
    }
    
    /**
     * @desc 添加SKU匹配规则
     * @param string $sku
     * @param int $userid
     * @param int $sellerId
     * @author YangLong
     * @date 2015-11-21
     * @return mixed
     */
    public function addSkuMatchInfo($sku, $userid, $sellerId)
    {
        if (empty($sku) || empty($userid) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
        
        $columns = array(
            'seller_id' => $sellerId,
            'user_id' => $userid,
            'SKU' => $sku
        );
        $conditions = 'seller_id=:seller_id and user_id=:user_id and SKU=:SKU';
        $params = array(
            ':seller_id' => $sellerId,
            ':user_id' => $userid,
            ':SKU' => $sku
        );
        $result = UserSkuDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
        
        if ($result === false) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        }
    }
    
    /**
     * @desc 编辑SKU匹配规则
     * @param string $sku
     * @param int $userid
     * @param int $sellerId
     * @param string $sku0
     * @param int $userid0
     * @author YangLong
     * @date 2015-11-23
     * @return mixed
     */
    public function editSkuMatchInfo($sku, $userid, $sellerId, $sku0, $userid0)
    {
        if (empty($sku) || empty($userid) || empty($sellerId) || empty($sku0) || empty($userid0)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
        
        $columns = array(
            'seller_id' => $sellerId,
            'user_id' => $userid,
            'SKU' => $sku
        );
        $conditions = 'seller_id=:c_seller_id and user_id=:c_user_id and SKU=:c_SKU';
        $params = array(
            ':c_seller_id' => $sellerId,
            ':c_user_id' => $userid0,
            ':c_SKU' => $sku0
        );
        try {
            $result = UserSkuDAO::getInstance()->iupdate($columns, $conditions, $params);
        } catch (Exception $e) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', $e->getCode());
        }
        
        if ($result === false) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        }
    }
    
    /**
     * @desc 删除一条SKU匹配信息
     * @param string $sku
     * @param int $userid
     * @param int $sellerId
     * @author YangLong
     * @date 2015-11-21
     * @return mixed
     */
    public function delSkuMatchInfo($sku, $userid, $sellerId)
    {
        if (empty($sku) || empty($userid) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
        
        $conditions = 'seller_id=:seller_id and user_id=:user_id and SKU=:SKU';
        $params = array(
            ':seller_id' => $sellerId,
            ':user_id' => $userid,
            ':SKU' => $sku
        );
        $result = UserSkuDAO::getInstance()->idelete($conditions, $params);
        
        if ($result === false) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        }
    }
    
    /**
     * @desc 批量删除SKU匹配规则
     * @param mixed $data
     * @param int $sellerId
     * @author YangLong
     * @date 2015-11-24
     * @return mixed
     */
    public function plDelSkuMatchInfo($data, $sellerId)
    {
        if (empty($data) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
        
        foreach ($data as $key => $value) {
            $userid = $value->user_id;
            $sku = $value->SKU;
            $conditions = 'seller_id=:seller_id and user_id=:user_id and SKU=:SKU';
            $params = array(
                ':seller_id' => $sellerId,
                ':user_id' => $userid,
                ':SKU' => $sku
            );
            $result[] = UserSkuDAO::getInstance()->idelete($conditions, $params);
        }
        
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        }
    }
    
    /**
     * @desc 解析SKU匹配信息Excel
     * @param string $filepath
     * @param int $sellerId
     * @author YangLong
     * @date 2015-11-25
     * @return mixed
     */
    public function parseSkuExcel($filepath, $sellerId)
    {
        if (empty($filepath)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'file no found');
        }
        
        new PHPExcel();
        $fileinfo = iMemcache::getInstance()->get($filepath);
        
        if (! is_file($fileinfo['filepath'])) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'file no found');
        }
        
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objReader->setReadDataOnly(TRUE);
        $objPHPExcel = $objReader->load($fileinfo['filepath']);
        
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        
        $result = array();
        $result['success'] = 0;
        $result['success_empty'] = 0;
        $result['error_sku'] = 0;
        $result['error_name_nofound'] = 0;
        $result['error_name_empty'] = 0;
        
        for ($row = 2; $row <= $highestRow; ++ $row) {
            $_sku = $objWorksheet->getCellByColumnAndRow(0, $row)->getValue();
            $_name = $objWorksheet->getCellByColumnAndRow(1, $row)->getValue();
            if (! empty($_name)) {
                $_uid = $this->getUserIdByName($_name, $sellerId);
                if (! empty($_uid)) {
                    if (! empty($_sku)) {
                        $columns = array(
                            'seller_id' => $sellerId,
                            'user_id' => $_uid,
                            'SKU' => $_sku
                        );
                        $conditions = 'seller_id=:seller_id and user_id=:user_id and SKU=:SKU';
                        $params = array(
                            ':seller_id' => $sellerId,
                            ':user_id' => $_uid,
                            ':SKU' => $_sku
                        );
                        $count = UserSkuDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                        if ($count) {
                            $result['success'] ++;
                        } else {
                            $result['success_empty'] ++;
                        }
                    } else {
                        $result['error_sku'] ++;
                    }
                } else {
                    $result['error_name_nofound'] ++;
                }
            } else {
                $result['error_name_empty'] ++;
            }
        }
        
        if (! empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE);
        }
    }
    
    /**
     * @desc 设置SKU分配相关的默认用户
     * @param integer $userid
     * @param integer $sellerId
     * @author YangLong
     * @date 2015-11-26
     * @return mixed
     */
    public function setSkuDefaultUser($userid, $sellerId)
    {
        if (empty($userid) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'userid cannot empty.');
        }
        
        $columns = array(
            'seller_id' => $sellerId,
            'config_name' => 'sku_default_user',
            'config_value' => $userid
        );
        $conditions = 'seller_id=:seller_id and config_name=:config_name';
        $params = array(
            ':seller_id' => $sellerId,
            ':config_name' => 'sku_default_user'
        );
        $result = SellerConfigDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
        if ($result !== false) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', $result);
        }
    }
    
    /**
     * @desc 获取seller的默认SKU分配相关默认用户
     * @param integer $sellerId
     * @author YangLong
     * @date 2015-11-26
     * @return mixed
     */
    public function getSkuDefaultUser($sellerId)
    {
        if (empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'sellerId cannot empty.');
        }
        
        $columns = array(
            'config_value userid'
        );
        $conditions = 'seller_id=:seller_id and config_name=:config_name';
        $params = array(
            ':seller_id' => $sellerId,
            ':config_name' => 'sku_default_user'
        );
        $result = SellerConfigDAO::getInstance()->iselect($columns, $conditions, $params, 'queryScalar');
        if ($result !== false) {
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', $result);
        }
    }
    
    /**
     * @desc 获取用户ID
     * @param string $name
     * @param string $sellerId
     * @author YangLong
     * @date 2015-11-25
     * @return int|false
     */
    private function getUserIdByName($name, $sellerId)
    {
        $columns = array(
            'user_id'
        );
        $conditions = 'pid=:pid and realname=:realname';
        $params = array(
            ':pid' => $sellerId,
            ':realname' => $name
        );
        return UserDAO::getInstance()->iselect($columns, $conditions, $params, 'queryScalar');
    }
    
}
