<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{

    /**
     * Authenticates a user.
     * The example implementation makes sure if the username and password
     * are both 'demo'.
     * In practical applications, this should be changed to authenticate
     * against some persistent user identity storage (e.g. database).
     *
     * @author liaojianwen,YangLong
     * @modify YangLong 2015-04-24 实现ECS集成用户系统
     * @return boolean whether authentication succeeds.
     */
    public function authenticate()
    {
        $columns = array(
            'user_id',
            'password',
            'pid',
            'username',
            'last_login_time'
        );
        $conditions = 'username=:username';
        $params = array(
            ':username' => strtolower($this->username)
        );
        $user = UserDAO::getInstance()->iselect($columns, $conditions, $params, false);
        
        if ($user === false) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        } else {
            if ($user['password'] != md5($this->password)) {
                $this->errorCode = self::ERROR_PASSWORD_INVALID;
            } else {
                $this->errorCode = self::ERROR_NONE;
            }
        }
        
        if ($this->errorCode == self::ERROR_NONE) {
            return $user;
        } elseif ($this->errorCode == self::ERROR_USERNAME_INVALID) {
            return "NameFail";
        } elseif ($this->errorCode == self::ERROR_PASSWORD_INVALID) {
            return "PwdFail";
        }
    }
    
}
