<?php

/**
 * @desc 用户处理类
 * @author heguangquan
 * @date 2015-02-06
 */
class UserModel extends BaseModel
{

    /**
     * @desc 覆盖父方法返回MsgQueueModel对象
     * @param string $className 需要实例化的类名
     * @author heguangquan
     * @date 2015-02-06
     * @return UserModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @desc 获取用户列表
     * @param int $sellerId 登录用户ID
     * @param array $pageArr 分页
     * @param string $searchKeyWord 搜索的关键字
     * @author heguangquan
     * @date 2015-02-06
     * @return array $userArr 用户列表信息
     */
    public function getUserList($userId, $pageInfo, $searchKeyWord)
    {
        if (empty($userId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'User authentication failed');
        }
        $userArr = UserDAO::getInstance()->getUserList($userId, $pageInfo, $searchKeyWord);
        if (empty($userArr['list']) && $pageInfo['page'] > 1) {
            $pageInfo['page'] = $pageInfo['page'] - 1;
            $userArr = UserDAO::getInstance()->getUserList($userId, $pageInfo, $searchKeyWord);
        }
        $userArr['sellerinfo'] = Yii::app()->session['userInfo'];
        $resultArr = $userArr ? $this->handleApiFormat(EnumOther::ACK_SUCCESS, $userArr) : $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 
            'User authentication failed');
        return $resultArr;
    }

    /**
     * @desc 添加新的用户
     * @param array $paramArr 添加的用户数据
     * @param bool $isPid 判断是否为注册用户，false:注册用户；true:系统内添加用户
     * @author heguangquan
     * @date 2015-02-06
     * @return array $resultArr 添加状态;
     */
    public function addUser($paramArr, $isPid = true)
    {
        if (empty($paramArr['pid']) && $isPid || empty($paramArr['username']) || empty($paramArr['realname'])) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'userInfo_missing');
        }
        
        if ($paramArr['password'] === null || strlen($paramArr['password']) < 6) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'password_missing');
        }
        
        // 不允许子用户添加用户 @YangLong
        if (Yii::app()->session['userInfo']['seller_id'] !== Yii::app()->session['userInfo']['user_id']) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'You do not have permission to add users');
        }
        
        if (! $isPid) {
            if (strtolower($paramArr['verifycode']) !== strtolower(Yii::app()->session['VerifyCode'])) {
                return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'verifyCode_err');
            }
            unset($paramArr['verifycode']);
        }
        $objUserDAO = UserDAO::getInstance();
        // 创建时间与更改时间一致
        $paramArr['update_time'] = $paramArr['create_time'] = time();
        $paramArr['status'] = 1;
        $paramArr['password'] = md5($paramArr['password']);
        $isParam['username'] = $paramArr['username'];
        $record = $objUserDAO->isExists($isParam);
        
        if ($record) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'user_exists');
        }
        $result = $objUserDAO->insert($paramArr);
        
        // 写权限默认为All
        $columns = array(
            'user_id' => $result,
            'users_group_id' => 1
        );
        UsersUsersRefGroupsDAO::getInstance()->iinsert($columns);
        
        if ($result) {
            $resultArr = $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
        } else {
            $resultArr = $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'fail to add user');
        }
        return $resultArr;
    }

    /**
     * @desc 获取用户明细信息
     * @param int $userId 客户ID
     * @author heguangquan
     * @date 2015-02-06
     * @return array | bool $userArr 用户明细
     */
    public function getUserDetail($userId)
    {
        if (empty($userId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户ID不存在');
        }
        $criteria = array(
            'user_id' => $userId
        );
        $selectArr = array(
            'user_id,realname,username,email,company_name,contact_person,telephone,address,create_time,pid,access_level,status'
        );
        $userArr['content'] = UserDAO::getInstance()->findByAttributes($criteria, $selectArr);
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $userArr);
    }

    /**
     * @desc 删除用户
     * @param string $userId 删除的用户ID
     * @param int $sellerId 当前登录的用户ID
     * @author heguangquan
     * @date 2015-02-12
     * @return array 操作状态;
     */
    public function deleteUser($userId, $sellerId)
    {
        if (empty($userId) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'User authentication failed');
        }
        if (strpos($userId, ',')) {
            $userId = explode(',', $userId);
            if (in_array($sellerId, $userId)) {
                return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', "Can't delete the current user");
            }
        } else {
            if ($userId === $sellerId) {
                return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', "Can't delete the current user");
            }
        }
        
        $paramArr['is_delete'] = boolConvert::toInt01(true);
        $paramArr['update_time'] = time();
        $result = UserDAO::getInstance()->updateByPk($userId, $paramArr);
        if (empty($result)) {
            $resultArr = $this->handleApiFormat(EnumOther::ACK_FAILURE, '', "Delete user failure");
        } else {
            $resultArr = $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
        }
        return $resultArr;
    }

    /**
     * @desc 修改用户数据
     * @param int $userId 用户ID
     * @param array $paramArr 修改的数据
     * @author heguangquan
     * @date 2015-02-06
     * @return array 操作状态
     */
    public function editUser($userId, $paramArr)
    {
        if (empty($userId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户ID不存在');
        }
        if (empty($paramArr['password'])) {
            unset($paramArr['password']);
        } else {
            $paramArr['password'] = md5($paramArr['password']);
        }
        $paramArr['update_time'] = time();
        $result = UserDAO::getInstance()->updateByPk($userId, $paramArr);
        if ($result) {
            $resultArr = $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
        } else {
            $resultArr = $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '编辑用户失败');
        }
        return $resultArr;
    }

    /**
     * @desc 检测用户是否存在
     * @param string $userName 用户名
     * @author heguangquan
     * @date 2015-03-02
     * @return array 用户存在的状态值
     */
    public function checkUser($userName)
    {
        if (empty($userName)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户名不能为空');
        }
        $isParam['username'] = $userName;
        $record = UserDAO::getInstance()->isExists($isParam);
        if ($record) {
            $resultArr = $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户名已存在');
        } else {
            $resultArr = $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
        }
        return $resultArr;
    }

    /**
     * @desc 切换站点,将相应的siteId,写入session
     * @param int $siteId 站点ID
     * @author YangLong
     * @date 2015-03-04
     * @return array ack状态
     */
    public function switchSite($siteId)
    {
        $switchInfo = array();
        if (isset(Yii::app()->session['switchInfo'])) {
            $switchInfo = Yii::app()->session['switchInfo'];
        }
        $switchInfo['siteId'] = $siteId;
        $switchInfo['accountId'] = 0;
        Yii::app()->session['switchInfo'] = $switchInfo;
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS);
    }

    /**
     * @desc 切换用户,将相应的accountId,写入session
     * @param int $accountId shop表ID
     * @author YangLong
     * @date 2015-03-04
     * @return array ack状态
     */
    public function switchAccount($accountId)
    {
        $switchInfo = array();
        if (isset(Yii::app()->session['switchInfo'])) {
            $switchInfo = Yii::app()->session['switchInfo'];
        }
        $switchInfo['accountId'] = $accountId;
        Yii::app()->session['switchInfo'] = $switchInfo;
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS);
    }

    /**
     * @desc 获取用户能管理的店铺信息
     * @author YangLong
     * @date 2015-09-16
     * @return mixed
     */
    public function getUserConfigs()
    {
        if (Yii::app()->session['userInfo']['user_id'] > 0) {
            $key = md5('user_shops_cache' . Yii::app()->session['userInfo']['user_id']);
            $user = iMemcache::getInstance()->get($key);
            if ($user !== false) {
                return $user;
            } else {
                $user = array();
            }
            
            // 查询出用户的组
            $columns = array(
                'users_group_id'
            );
            $conditions = 'user_id=:user_id';
            $params = array(
                ':user_id' => Yii::app()->session['userInfo']['user_id']
            );
            $user['groups'] = UsersUsersRefGroupsDAO::getInstance()->iselect($columns, $conditions, $params);
            
            // 查询出用户的所有权限
            $user['actions'] = array();
            if (is_array($user['groups'])) {
                foreach ($user['groups'] as $group) {
                    $columns = array(
                        'users_actions_id'
                    );
                    $conditions = 'users_group_id=:users_group_id';
                    $params = array(
                        ':users_group_id' => $group['users_group_id']
                    );
                    $_temp = UsersGroupRefActionsDAO::getInstance()->iselect($columns, $conditions, $params);
                    array_push($user['actions'], $_temp);
                }
            }
            
            // 查询出用户的关联店铺
            $columns = array(
                'shop_id'
            );
            $conditions = 'user_id=:user_id';
            $params = array(
                ':user_id' => Yii::app()->session['userInfo']['user_id']
            );
            $user['shops'] = ShopRefUsersDAO::getInstance()->iselect($columns, $conditions, $params, 'queryColumn');
            
            // 查询出seller的config
            $columns = array(
                'config_name',
                'config_value'
            );
            $conditions = 'seller_id=:seller_id';
            $params = array(
                ':seller_id' => Yii::app()->session['userInfo']['seller_id']
            );
            $_temp = SellerConfigDAO::getInstance()->iselect($columns, $conditions, $params);
            foreach ($_temp as $value) {
                $user['seller_config'][$value['config_name']] = $value['config_value'];
            }
            
            iMemcache::getInstance()->set($key, $user, 300);
            return $user;
        } else {
            return false;
        }
    }

    /**
     * @desc 获取当前登录的用户名
     * @author YangLong
     * @date 2015-10-09
     * @return mixed
     */
    public function getUserName()
    {
        $userName = Yii::app()->session['userInfo']['username'];
        $result = array();
        if (! empty($userName)) {
            $result['userName'] = $userName;
            if (Yii::app()->session['userInfo']['seller_id'] == Yii::app()->session['userInfo']['user_id'] &&
                 empty(Yii::app()->session['userInfo']['last_login_time'])) {
                $result['showHelp'] = 1;
            } else {
                $result['showHelp'] = 0;
            }
            $result = $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            $result = $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
        return $result;
    }
}
