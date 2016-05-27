<?php

/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{

    /**
     *
     * @var string the default layout for the controller view. Defaults to '//layouts/column1',
     *      meaning using a single column layout. See 'protected/views/layouts/column1.php'.
     */
    public $layout = '//layouts/column1';

    /**
     *
     * @var array context menu items. This property will be assigned to {@link CMenu::items}.
     */
    public $menu = array();

    /**
     *
     * @var array the breadcrumbs of the current page. The value of this property will
     *      be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
     *      for more details on how to specify this property.
     */
    public $breadcrumbs = array();

    /**
     * smarty assign function
     *
     * @param string $key            
     * @param mixed $value            
     */
    public function assign($key, $value)
    {
        // Yii::app()->smarty->assign($key, $value);
        $app = Yii::app(); // CWebApplication对象
        $smarty = $app->smarty; // CModule::__get创建对象
        $smarty->assign($key, $value);
    }

    /**
     * smarty display function
     *
     * @param string $view            
     */
    public function display($view)
    {
        Yii::app()->smarty->display($view);
    }

    /**
     * @desc 数据转换成json格式
     * @param mixed $data 数据
     * @param bool $encode 是否json_encode
     * @param string $decodezh 是否解码中文
     * @author Zijie Yuan
     * @date 2014-11-13
     * @modify YangLong 2015-04-23 增加不encode的功能
     * @modify YangLong 2015-02-21 增加中文解码功能
     * @return void|boolean
     */
    protected function renderJson($data, $encode = true, $decodezh = false)
    {
        if (empty($data)) {
            return false;
        }
        
        if (! headers_sent()) {
            header('Content-Type: application/json');
        }
        
        if ($encode) {
            $data = json_encode($data);
        }
        
        if ($decodezh) {
            $data = preg_replace_callback('/\\\u[0-9a-fA-F]{4}/i', 
                function ($matches) {
                    return json_decode('"' . $matches[0] . '"', true);
                }, $data);
        }
        echo $data;
    }

    /**
     * @desc 验证用户是否登录等
     * @param string $action
     * @author liaojianwen,YangLong
     * @date 2015-03-04
     * @return boolean
     */
    protected function checklogin($action = '')
    {
        if (Yii::app()->session['userInfo']['seller_id'] > 0) {
            $_flag = false;
            $actionsArray = UserModel::model()->getUserConfigs();
            $actionsArray = $actionsArray['actions'];
            foreach ($actionsArray as $actions) {
                foreach ($actions as $_action) {
                    if ($_action['users_actions_id'] == $action || $_action['users_actions_id'] == ActionsEnum::ALL) {
                        $_flag = true;
                    }
                }
            }
            return $_flag;
        } else {
            return false;
        }
    }

    /**
     * @desc 用户登录
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $isVerify 是否自动登录
     * @author liaojianwen,YangLong
     * @date 2015-03-04
     * @return boolean
     */
    protected function login($username = '', $password = '', $code = '', $isVerify)
    {
        $verifyCode = strtolower(Yii::app()->session['VerifyCode']);
        Yii::app()->session['VerifyCode'] = null;
        if (empty($verifyCode)) {
            $result = array(
                'Ack' => 'CodeFail'
            );
            return $result;
        }
        
        $identity = new UserIdentity($username, $password);
        $user = $identity->authenticate();
        if ($user === "NameFail") {
            $result = array(
                'Ack' => 'NameFail'
            );
            return $result;
        } elseif ($user === "PwdFail") {
            $result = array(
                'Ack' => 'PwdFail'
            );
            return $result;
        } else {
            if (strtolower($code) == $verifyCode || $isVerify) {
                Yii::app()->session['userInfo'] = array(
                    'seller_id' => ($user['pid'] > EnumOther::ROOT_USER_ID ? $user['pid'] : $user['user_id']),
                    'user_id' => $user['user_id'],
                    'username' => $user['username'],
                    'last_login_time' => $user['last_login_time']
                );
                $result = array(
                    'Ack' => 'Success'
                );
                
                $columns = array(
                    'last_login_time' => time()
                );
                $conditions = 'user_id=:user_id';
                $params = array(
                    ':user_id' => $user['user_id']
                );
                UserDAO::getInstance()->iupdate($columns, $conditions, $params);
                
                $lang = CInputFilter::getString('cLanguage', 'zh-cn');
                if (strlen($lang) > 0 && is_dir('public/lang/' . $lang)) {
                    Yii::app()->session['cLanguage'] = $lang;
                }
                
                return $result;
            } else {
                $result = array(
                    'Ack' => 'CodeFail'
                );
                return $result;
            }
        }
    }

    /**
     * @descs 用户注销
     * @author liaojianwen
     * @date 2015-03-04
     */
    protected function logout()
    {
        Yii::app()->getSession()->destroy();
        $result = array(
            'Ack' => 'Success'
        );
        return $result;
    }

    /**
     * @desc API检查登录
     * @param string $action
     * @author YangLong
     * @date 2015-02-12
     */
    protected function apiChecklogin($action = '')
    {
        if (! $this->checklogin($action)) {
            $this->renderJson(array(
                'Error' => 'User authentication fails'
            ));
            Yii::app()->end();
        }
    }

    /**
     * @desc API过滤分类筛选参数
     * @param string $class            
     * @author YangLong,liaojianwen
     * @date 2015-02-12
     * @return string
     */
    protected function filterclass($class = '')
    {
        $_alows = array(
            'pending' => '待处理',
            'star' => '星标邮件',
            'label' => '自定义标签',
            'autotag' => '自动标签',
            'member' => '会员邮件',
            'sys' => '系统邮件',
            'sent' => '已发送',
            'salebefore' => '售前',
            'saleafter' => '售后',
            'delete' => '已删除'
        );
        
        if (array_key_exists($class, $_alows)) {
            return $class;
        } else {
            return 'pending';
        }
    }

    /**
     * @desc assign 语言信息
     * @param 模板名称 $tpname
     * @author YangLong
     * @date 2015-10-10
     * @return null
     */
    protected function assignLangInfo($tpname)
    {
        if (empty(Yii::app()->session['cLanguage'])) {
            Yii::app()->session['cLanguage'] = 'zh-cn';
        }
        $lang = require 'public/lang/' . Yii::app()->session['cLanguage'] . '/' . $tpname . '.tp.php';
        $this->assign('lang', $lang);
        $this->assign('lang_dir', Yii::app()->session['cLanguage']);
    }

    protected function view(array $data = array(), $view = NULL, $return = FALSE)
    {
        ! $view and $view = $this->action->id;
        $data = array_merge($data, $this->_load_lang());
        return $this->render($view, $data, $return);
    }

    private function _load_lang($tpname = NULL)
    {
        ! $tpname && $tpname = $this->action->id;
        
        $_lang_dir = Yii::app()->session['cLanguage'];
        ! $_lang_dir and Yii::app()->session['cLanguage'] = $_lang_dir = 'zh-cn';
        
        $file = 'public/lang/' . $_lang_dir . '/' . $tpname . '.tp.php';
        file_exists($file) ? $lang = require $file : $lang = array();
        
        return array(
            'lang' => $lang,
            'lang_dir' => $_lang_dir,
            'git_hash' => $this->git_hash()
        );
    }

    protected function git_hash()
    {
        $file = '.git/index';
        $content = file_exists($file) ? file_get_contents($file) : '';
        return substr(md5($content), 13, 6);
    }
}
