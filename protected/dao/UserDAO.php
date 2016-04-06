<?php

/**
 * @desc 用户处理DAO
 * @author heguangquan
 * @date 2015-02-06
 */
class UserDAO extends BaseDAO
{

    /**
     * @desc 对象实例化
     * @param string $className 需要实例化的类名
     * @author heguangquan
     * @date 2015-02-06
     * @return UserDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 初始化对象
     * @author heguangquan
     * @date 2015-02-06
     */
    public function __construct()
    {
        $this->tableName = 'users';
        $this->primaryKey = 'user_id';
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
    }

    /**
     * @desc 获取用户列表
     * @param int $sellerId 当前用户ID
     * @param array $pageInfo 分页
     * @param string $searchKeyWord 查询的姓名
     * @author heguangquan
     * @date 2015-02-06
     * @return array 用户列表信息
     */
    public function getUserList($userId, $pageInfo, $searchKeyWord)
    {
        if (empty($userId)) {
            return false;
        }
        
        $userArr = array();
        $limit = $pageInfo['pageSize'];
        $offset = ($pageInfo['page'] - 1) * $limit;
        $this->dbCommand->reset();
        $this->dbCommand->select('user_id,realname,username,email,telephone', 'SQL_CALC_FOUND_ROWS')->from($this->tableName);
        if (! empty($searchKeyWord)) {
            $this->dbCommand->where(array(
                'or',
                'pid=:pid',
                'user_id=:sellerId'
            ), array(
                ':pid' => $userId,
                ':sellerId' => $userId
            ));
            $this->dbCommand->andWhere('is_delete=:delete and realname like :realName', 
                array(
                    ':delete' => 0,
                    ':realName' => '%' . $searchKeyWord . '%'
                ));
        } else {
            $this->dbCommand->where(array(
                'and',
                'pid=:pid',
                'is_delete=:delete'
            ), array(
                ':pid' => $userId,
                ':delete' => boolConvert::toInt01(false)
            ));
            $this->dbCommand->orWhere('user_id=:sellerId', array(
                ':sellerId' => $userId
            ));
        }
        
        $userArr['list'] = $this->dbCommand->order($this->primaryKey . ' desc')
            ->limit($limit, $offset)
            ->queryAll();
        
        foreach ($userArr['list'] as $key => $value) {
            $userArr['list'][$key]['username'] = htmlspecialchars($userArr['list'][$key]['username']);
            $userArr['list'][$key]['realname'] = htmlspecialchars($userArr['list'][$key]['realname']);
        }
        
        // 获取总记录行数
        $this->dbCommand->reset();
        $userArr['count'] = $this->dbCommand->setText('select found_rows()')->queryScalar();
        $userArr['page'] = $pageInfo;
        return $userArr;
    }
}