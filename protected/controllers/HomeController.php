<?php

/**
 * 默认控制器
 * @date 2015-01-23
 * @author YangLong
 */
class HomeController extends Controller
{

    /**
     * @desc 默认方法
     * @author YangLong
     * @date 2015-01-23
     */
    public function actionIndex()
    {
        if (! $this->checklogin(ActionsEnum::PUBLIC_READ)) {
            Yii::app()->request->redirect('?r=Home/Login');
        }
        
        $this->assignLangInfo('index');
        
        $dirs = scandir('public/lang/');
        $langlist = array();
        foreach ($dirs as $key => $value) {
            if ($value == '.' || $value == '..') {} else {
                $_langname = @file_get_contents('public/lang/' . $value . '/_langname.txt');
                if ($_langname) {
                    $langlist[$value] = $_langname;
                }
            }
        }
        
        $currentLangName = '';
        if (! empty(Yii::app()->session['cLanguage'])) {
            if (isset($langlist[Yii::app()->session['cLanguage']])) {
                $currentLangName = $langlist[Yii::app()->session['cLanguage']];
            }
        }
        if (empty($currentLangName)) {
            $currentLangName = '语言选择';
        }
        
        $this->assign('currentLangName', $currentLangName);
        $this->assign('langlist', $langlist);
        $this->display('index.html');
    }
    
    /**
     * @desc 信息列表页
     * @author YangLong
     * @date 2015-02-06
     */
    public function actionMsgList()
    {
        if (! $this->checklogin(ActionsEnum::MSG_READ)) {
            Yii::app()->request->redirect('?r=Home/Login');
        }
        
        $this->assignLangInfo('msglist');
        $this->display('msglist.html');
    }
    
    /**
     * @desc 回复模版列表
     * @author YangLong
     * @date 2015-02-09
     */
    public function actionTplist()
    {
        if (! $this->checklogin(ActionsEnum::PUBLIC_READ)) {
            Yii::app()->request->redirect('?r=Home/Login');
        }

        $this->assignLangInfo('replytp');
        $this->display('replytp.html');
    }
	
    /**
     * @desc 店铺列表
     * @author YangLong
     * @date 2015-02-09
     */
    public function actionShoplist()
    {
        if (! $this->checklogin(ActionsEnum::PUBLIC_READ)) {
            Yii::app()->request->redirect('?r=Home/Login');
        }
        
        $this->assignLangInfo('shopset');
        $this->display('shopset.html');
    }
	
    /**
     * @desc 客户管理
     * @author YangLong
     * @date 2015-09-06
     */
    public function actionCusMange()
    {
        if (! $this->checklogin(ActionsEnum::PUBLIC_READ)) {
            Yii::app()->request->redirect('?r=Home/Login');
        }

        $this->assignLangInfo('cusmanage');
        $this->display('cusmanage.html');
    }
	
    /**
     * @desc 客户管理详情
     * @author YangLong
     * @date 2015-09-07
     */
    public function actionCusDetails()
    {
        if (! $this->checklogin(ActionsEnum::PUBLIC_READ)) {
            Yii::app()->request->redirect('?r=Home/Login');
        }
        
        $this->assignLangInfo('cusdet');
        $this->display('cusdet.html');
    }
    
    /**
     * @desc 配置管理页面
     * @author YangLong
     * @date 2015-11-20
     */
    public function actionConfigSet()
    {
        if (! $this->checklogin(ActionsEnum::PUBLIC_READ)) {
            Yii::app()->request->redirect('?r=Home/Login');
        }
        
        $this->assignLangInfo('sellerconfig');
        $this->display('sellerconfig.html');
    }
    
    /**
     * @desc SKU导入和管理页面
     * @author YangLong
     * @date 2015-11-21
     */
    public function actionSkuMatch()
    {
        if (! $this->checklogin(ActionsEnum::PUBLIC_READ)) {
            Yii::app()->request->redirect('?r=Home/Login');
        }
        
        $this->assignLangInfo('skumatch');
        $this->display('skumatch.html');
    }
    
    /**
     * @desc 登录界面
     * @author liaojianwen
     * @date 2015-03-02
     */
    public function actionLogin()
    {
        session_start();
        $sessionId = session_id();
        $this->assign('sid', md5($sessionId));
        $this->assignLangInfo('login');
        $this->display('login.html');
    }
    
    /**
     * @desc 用户列表
     * @author heguangquan
     * @date 2015-02-09
     */
    public function actionUserList()
    {
        if (! $this->checklogin(ActionsEnum::PUBLIC_READ)) {
            Yii::app()->request->redirect('?r=Home/Login');
        }
        
        $this->assignLangInfo('usermanage');
        $this->display('usermanage.html');
    }
    
    /**
     * @desc 信息详情页
     * @author heguangquan
     * @date 2015-02-09
     */
    public function actionMsgDetail()
    {
        if (! $this->checklogin(ActionsEnum::MSG_READ)) {
            Yii::app()->request->redirect('?r=Home/Login');
        }
        
        $mids = CInputFilter::getInt('mids');
        $msgType = CInputFilter::getString('class');
        $this->assign('mids', $mids);
        $this->assign('msgtype', $msgType);
        $this->assignLangInfo('msgdetail');
        $this->display('msgdetail.html');
    }
    
    /**
     * @desc case列表页面
     * @author lvjianfei
     * @date 2015-03-26
     */
    public function actionDisputeList()
    {
        if (! $this->checklogin(ActionsEnum::CASE_READ)) {
            Yii::app()->request->redirect('?r=Home/Login');
        }
        
        $this->assignLangInfo('disputeList');
        $this->display('disputeList.html');
    }
    
    /**
     * @desc case详细页面
     * @author lvjianfei
     * @date 2015-4-2
     */
    public function actionDisputeDetail()
    {
        if (! $this->checklogin(ActionsEnum::CASE_READ)) {
            Yii::app()->request->redirect('?r=Home/Login');
        }
        
        $this->assignLangInfo('disputeDet');
        $this->display('disputeDet.html');
    }
    
    /**
     * @desc 用户注册页面
     * @author heguangquan
     * @date 2015-03-02
     */
    public function actionRegister()
    {
        $this->assignLangInfo('register');
        $this->display('register.html');
    }
    
    /**
     * @desc return 列表页面
     * @author liaojianwen
     * @date 2015-06-18
     */
    public function actionReturnList()
    {
        if (! $this->checklogin(ActionsEnum::RETURN_READ)) {
            Yii::app()->request->redirect('?r=Home/Login');
        }
        $this->assignLangInfo('returnlist');
        $this->display('returnlist.html');
    }
    
    /**
     * @desc return 详情页面
     * @author liaojianwen
     * @date 2015-06-19
     */
    public function actionReturnDetail()
    {
        if (! $this->checklogin(ActionsEnum::RETURN_READ)) {
            Yii::app()->request->redirect('?r=Home/Login');
        }
        $this->assignLangInfo('returndet');
        $this->display('returndet.html');
    }
    
    /**
     * @desc FeedbackList
     * @author liaojianwen
     * @date 2015-06-19
     */
    public function actionFeedbackList()
    {
        if (! $this->checklogin(ActionsEnum::FEEDBACK_READ)) {
            Yii::app()->request->redirect('?r=Home/Login');
        }
        
        $this->assignLangInfo('feedbacklist');
        $this->display('feedbacklist.html');
    }
    
    /**
     * @desc urgepay
     * @author liaojianwen
     * @date 2015-10-30
     */
    public function actionUrgePay()
    {
        if(! $this->checklogin(ActionsEnum::URGEPAY_READ)){
            Yii::app()->request->redirect('?r=Home/Login');
        }
        $this->assignLangInfo('urgepay');
        $this->display('urgepay.html');
    }
}
