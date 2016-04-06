<?php

/**
 * @desc 数据读取接口API
 * @date 2015-01-23
 * @author YangLong
 */
class ApiController extends Controller
{

    /**
     * @desc 构造方法
     * @author YangLong
     * @date 2015-07-29
     */
    public function __construct()
    {}

    /**
     * @desc 默认控制器
     * @author YangLong
     * @date 2015-02-12
     */
    public function actionIndex()
    {
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $this->renderJson(array(
            'GMT_DATE_ISO8601' => gmdate(DATE_ISO8601),
            'LOCAL_DATE_ISO8601' => date(DATE_ISO8601)
        ));
    }

    /**
     * @desc 获取消息列表, string $listType 分类筛选参数, int $classID 分类筛选参数值, int $page 页码, int $pagesize 分页大小
     * @author YangLong,liaojianwen
     * @date 2015-02-12
     */
    public function actionGetMsgList()
    {
        $this->apiChecklogin(ActionsEnum::MSG_READ);
        
        $listType = CInputFilter::getString('listType', '');
        $classID = CInputFilter::getInt('classID', 0);
        $page = CInputFilter::getInt('page', EnumOther::PAGE);
        $pageSize = CInputFilter::getInt('pageSize', EnumOther::PAGESIZE);
        $searchCon = CInputFilter::getString('searchCon', '');
        $questionType = CInputFilter::getString('QuestionType', '');
        $messageType = CInputFilter::getString('MessageType', '');
        $labelid = CInputFilter::getInt('labelid', 0);
        $tagid = CInputFilter::getInt('tagid', 0);
        $aggregate = CInputFilter::getInt('aggregate');
        $listType = $this->filterclass($listType);
        $this->renderJson(MsgDealModel::model()->getMessageList($listType, $classID, $page, $pageSize, $searchCon, $labelid, $tagid, $aggregate, $questionType, $messageType));
    }

    /**
     * @desc 查询待处理数
     * @author liaojianwen
     * @date 2015-06-11
     */
    public function actionGetMsgHandCount()
    {
        $this->apiChecklogin(ActionsEnum::MSG_READ);
        
        $this->renderJson(MsgDealModel::model()->getDisposeCount());
    }

    /**
     * @desc 删除消息, string $mids 需要删除消息的ID 逗号(,)分隔的ID值,例如:1,2,3,4
     * @author liaojianwen
     * @date  2015-01-28
     */
    public function actionSetMsgDel()
    {
        $this->apiChecklogin(__METHOD__);
        
        $mids = CInputFilter::getnorepeatInts('mids');
        $_arr['ids'] = $mids;
        $_arr['type'] = 'del';
        
        $this->renderJson(MsgDealModel::model()->dealMsg($_arr));
    }

    /**
     * @desc 隐藏消息, string $mids 需要隐藏消息的ID  逗号(,)分隔的ID值,例如:1,2,3,4
     * @author liaojianwen
     * @date  2015-01-28
     */
    public function actionSetMsgHide()
    {
        $this->apiChecklogin(__METHOD__);
        
        $mids = CInputFilter::getnorepeatInts('mids');
        $_arr['ids'] = $mids;
        $_arr['type'] = 'hidden';
        $this->renderJson(MsgDealModel::model()->dealMsg($_arr));
    }

    /**
     * @desc 还原消息, string $mids 需要还原消息的ID  逗号(,)分隔的ID值,例如:1,2,3,4
     * @author liaojianwen
     * @date  2015-01-28
     */
    public function actionRevertMessages()
    {
        $this->apiChecklogin(__METHOD__);
        
        $mids = CInputFilter::getnorepeatInts('mids');
        $_arr['ids'] = $mids;
        $_arr['type'] = 'revert';
        $this->renderJson(MsgDealModel::model()->dealMsg($_arr));
    }

    /**
     * @desc 更新消息为自定义标签, string $mids 需要标记标签消息的ID  逗号(,)分隔的ID值,例如:1,2,3,4
     * @author liaojianwen
     * @date 2015-01-28
     */
    public function actionSetMessagesCustomLabel($label = 0)
    {
        $this->apiChecklogin(__METHOD__);
        
        $mids = CInputFilter::getnorepeatInts('mids');
        $label = (int) $label;
        $_arr['ids'] = $mids;
        $_arr['param'] = $label;
        $_arr['type'] = 'label_succe';
        $this->renderJson(MsgDealModel::model()->dealMsg($_arr));
    }

    /**
     * @desc 取消消息的自定义标签, string $mids 需要取消标签消息的ID  逗号(,)分隔的ID值,例如:1,2,3,4
     * @author liaojianwen
     * @date 2015-01-28
     */
    public function actionCancelMessagesCustomLabel($label = 0)
    {
        $this->apiChecklogin(__METHOD__);
        
        $mids = CInputFilter::getnorepeatInts('mids');
        $label = (int) $label;
        $_arr['ids'] = $mids;
        $_arr['param'] = $label;
        $_arr['type'] = 'label_not';
        $this->renderJson(MsgDealModel::model()->dealMsg($_arr));
    }

    /**
     * @desc 更新消息标星, string $mids 需要星标的消息的ID  逗号(,)分隔的ID值,例如:1,2,3,4
     * @author liaojianwen
     * @date 2015-01-28
     */
    public function actionSetMsgStar()
    {
        $this->apiChecklogin(__METHOD__);
        
        $mids = CInputFilter::getnorepeatInts('mids');
        $_arr['ids'] = $mids;
        $_arr['type'] = 'star_succe';
        $this->renderJson(MsgDealModel::model()->dealMsg($_arr));
    }

    /**
     * @desc 取消消息标星, string $mids 需要取消消息星标的ID  逗号(,)分隔的ID值,例如:1,2,3,4
     * @author liaojianwen
     * @date 2015-01-28
     */
    public function actionCancelMegStar()
    {
        $this->apiChecklogin(__METHOD__);
        
        $mids = CInputFilter::getnorepeatInts('mids');
        $_arr['ids'] = $mids;
        $_arr['type'] = 'star_not';
        $this->renderJson(MsgDealModel::model()->dealMsg($_arr));
    }

    /**
     * @desc 设置是否已处理, string $mids 需要标记待办消息的ID  逗号(,)分隔的ID值,例如:1,2,3,4
     * @author liaojianwen,YangLong
     * @date 2015-09-19
     */
    public function actionSetMsgHandleStatus()
    {
        $this->apiChecklogin(__METHOD__);
        
        ignore_user_abort(true);
        $mids = CInputFilter::getnorepeatInts('mids');
        $action = CInputFilter::getString('action', 'handle_yes');
        $_arr['ids'] = $mids;
        $_arr['type'] = $action;
        $this->renderJson(MsgDealModel::model()->dealMsg($_arr));
    }

    /**
     * @desc 更新消息为已读, string $mids 需要标记已读消息的ID  逗号(,)分隔的ID值,例如:1,2,3,4
     * @author liaojianwen
     * @date 2015-01-28
     */
    public function actionSetMsgRead()
    {
        $this->apiChecklogin(__METHOD__);
        
        $mids = CInputFilter::getnorepeatInts('mids');
        $_arr['verfiy'] = CInputFilter::getString('g'); // 判断命令是从列表还是明细发出
        $_arr['ids'] = $mids;
        $_arr['type'] = 'read_succe';
        $this->renderJson(MsgDealModel::model()->dealMsg($_arr));
    }

    /**
     * @desc 更新消息为未读, string $mids 需要标记未读消息的ID  逗号(,)分隔的ID值,例如:1,2,3,4
     * @author liaojianwen
     * @date 2015-01-28
     */
    public function actionSetMsgNoRead()
    {
        $this->apiChecklogin(__METHOD__);
        
        $mids = CInputFilter::getnorepeatInts('mids');
        $_arr['ids'] = $mids;
        $_arr['type'] = 'read_not';
        $this->renderJson(MsgDealModel::model()->dealMsg($_arr));
    }

    /**
     * @desc 回复信息 int $mids 信息ID
     * @author YangLong
     * @date 2015-01-30
     */
    public function actionReplyMessage()
    {
        $this->apiChecklogin(__METHOD__);
        
        $mids = CInputFilter::getnorepeatInts('mids');
        $body = CInputFilter::getString('body', "");
        $result = MsgDealModel::model()->replyMessages($body, $mids);
        $this->renderJson($result);
    }

    /**
     * @desc 获取信息明细
     * @author liaojianwen
     * @date 2015-01-28
     */
    public function actionShowMessage()
    {
        $this->apiChecklogin(ActionsEnum::MSG_READ);
        
        $mids = CInputFilter::getnorepeatInts('mids');
        $msgType = CInputFilter::getString('c');
        $classID = CInputFilter::getInt('classID', 0);
        $result = MsgDealModel::model()->showMessages($mids, $msgType, $classID);
        $this->renderJson($result);
    }

    /**
     * @desc 添加模板分类, int $pid 父ID, string $classname 模板分类名称
     * @author YangLong
     * @date 2015-02-02
     */
    public function actionAddTpClass()
    {
        $this->apiChecklogin(__METHOD__);
        
        $classname = CInputFilter::getString('classname', '');
        $pid = CInputFilter::getInt('pid', 0);
        $result = MsgDealModel::model()->addTpClass($classname, $pid);
        $this->renderJson($result);
    }

    /**
     * @desc 获取模板分类列表, int or string '#'(%23) $pid 父ID, boolean $alternative jstree格式
     * @author YangLong
     * @date 2015-02-03
     */
    public function actionGetTpClassList()
    {
        $this->apiChecklogin(ActionsEnum::TEMPLATE_READ);
        
        $pid = CInputFilter::getString('pid', '#');
        if ($pid === '#' or $pid === '') {
            $pid = - 1;
        } else {
            $pid = (int) $pid;
        }
        $alternative = CInputFilter::getBool('alternative');
        $result = MsgDealModel::model()->getTpClassList($pid, $alternative);
        $this->renderJson($result);
    }

    /**
     * @desc 模板分类的编辑和添加
     * @author YangLong
     * @date 2015-02-25
     */
    public function actionTpClassEdit()
    {
        $this->apiChecklogin(__METHOD__);
        
        $cid = CInputFilter::getInt('cid');
        $pid = CInputFilter::getInt('pid', - 1);
        $classname = CInputFilter::getText('classname');
        $result = MsgDealModel::model()->tpClassEdit($cid, $pid, $classname);
        $this->renderJson($result);
    }

    /**
     * @desc 模板分类的移动
     * @author YangLong
     * @date 2015-03-02
     */
    public function actionTpClassMove()
    {
        $this->apiChecklogin(__METHOD__);
        
        $cid = CInputFilter::getInt('cid');
        $pid = CInputFilter::getString('pid');
        if ($pid == '#') {
            $pid = 0;
        }
        $pid = (int) $pid;
        $result = MsgDealModel::model()->tpClassMove($cid, $pid);
        $this->renderJson($result);
    }

    /**
     * @desc 获取模板列表
     * @author heguangquan
     * @date 2015-02-03
     */
    public function actionGetTpList()
    {
        $this->apiChecklogin(ActionsEnum::TEMPLATE_READ);
        
        $pid = CInputFilter::getnorepeatInts('pid', '0');
        // 是否分页
        $type = CInputFilter::getInt('type');
        // 分页信息
        $pageInfo = array(
            'pageSize' => CInputFilter::getInt('pageSize', EnumOther::PAGESIZE),
            'page' => CInputFilter::getInt('page', EnumOther::PAGE)
        );
        
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = MsgDealModel::model()->getTpList($pid, $sellerId, $pageInfo, $type);
        $this->renderJson($result);
    }

    /**
     * @desc 获取模板明细
     * @author heguangquan
     * @date 2015-02-03
     */
    public function actionGetTpDetail()
    {
        $this->apiChecklogin(__METHOD__);
        
        $tid = CInputFilter::getString('tid');
        $result = MsgDealModel::model()->getTpDetail($tid);
        $this->renderJson($result);
    }

    /**
     * @desc 删除模板
     * @author heguangquan
     * @date 2015-02-03
     */
    public function actionDeteleTpList()
    {
        $this->apiChecklogin(__METHOD__);
        
        $tpId = CInputFilter::getnorepeatInts('tid');
        $result = MsgDealModel::model()->deteleTpList($tpId);
        $this->renderJson($result);
    }

    /**
     * @desc 模板信息的编辑
     * @author YangLong
     * @date 2015-02-12
     */
    public function actionTpEdit()
    {
        $this->apiChecklogin(__METHOD__);
        
        $tpId = CInputFilter::getInt('tid');
        $classId = CInputFilter::getInt('cid');
        $className = CInputFilter::getString('cn');
        $title = CInputFilter::getNSvalue('title');
        $content = CInputFilter::getNSvalue('content');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = MsgDealModel::model()->tpEdit($tpId, $classId, $className, $title, $content, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 根据 模板的自增ID获取模板详情，包括分类名称  tid 模板自增ID
     * @author YangLong
     * @date 2015-02-12
     */
    public function actionGetTp()
    {
        $this->apiChecklogin(__METHOD__);
        
        $tpId = CInputFilter::getInt('tid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = MsgDealModel::model()->getTp($tpId, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 将模板分类标记为已删除, string $cids 模板ID
     * @author YangLong
     * @date 2015-02-03
     */
    public function actionDeleteTpClass()
    {
        $this->apiChecklogin(__METHOD__);
        
        $cids = CInputFilter::getnorepeatInts('cids');
        $result = MsgDealModel::model()->deleteTpClass($cids);
        $this->renderJson($result);
    }

    /**
     * @desc 获取SessionID返回给浏览器构造出跳转URL
     * @return string
     * @author YangLong
     * @date 2015-02-12
     */
    public function actionGetEbaySessionID()
    {
        $this->apiChecklogin(__METHOD__);
        
        $result = MsgDealModel::model()->getEbaySessionID();
        $this->renderJson($result);
    }

    /**
     * @desc 浏览器发送回SessionID根据此SessionID获取token并保存到数据库, string $sessionid, string $account, string $nickname
     * @author liaojianwen,Yanglong
     * @date 2015-2-5
     */
    public function actionSaveTokenBySessionID()
    {
        $this->apiChecklogin(__METHOD__);
        
        $sessionid = CInputFilter::getString('sessionid');
        $account = CInputFilter::getString('account');
        $nickname = CInputFilter::getString('nickname');
        $siteid = CInputFilter::getInt('siteid', - 1);
        $result = MsgDealModel::model()->saveTokenBySessionID($sessionid, $account, $nickname, $siteid);
        $this->renderJson($result);
    }

    /**
     * @desc 获取用户列表信息
     * @author heguangquan
     * @date 2015-02-06
     */
    public function actionGetUserList()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $userId = Yii::app()->session['userInfo']['user_id'];
        $pageInfo = array(
            'pageSize' => CInputFilter::getInt('pageSize', EnumOther::PAGESIZE),
            'page' => CInputFilter::getInt('page', EnumOther::PAGE)
        );
        $searchKeyWord = CInputFilter::getString('searchCon');
        $resultArr = UserModel::model()->getUserList($userId, $pageInfo, $searchKeyWord);
        $this->renderJson($resultArr);
    }

    /**
     * @desc 删除用户
     * @author heguangquan
     * @date 2015-02-12
     */
    public function actionDeleteUser()
    {
        $this->apiChecklogin(__METHOD__);
        
        $userId = CInputFilter::getnorepeatInts('uid');
        $sellerId = Yii::app()->session['userInfo']['user_id'];
        $resultArr = UserModel::model()->deleteUser($userId, $sellerId);
        $this->renderJson($resultArr);
    }

    /**
     * @desc 添加用户
     * @author heguangquan
     * @date 2015-02-06
     * @modify 2015-11-28
     */
    public function actionAddUser()
    {
        $this->apiChecklogin(__METHOD__);
        
        $paramArr = array();
        $paramArr['pid'] = Yii::app()->session['userInfo']['seller_id'];
        $paramArr['username'] = CInputFilter::getString('username');
        $paramArr['realname'] = CInputFilter::getString('realname');
        $paramArr['password'] = CInputFilter::getNSvalue('password');
        $paramArr['email'] = CInputFilter::getString('email');
        $paramArr['telephone'] = CInputFilter::getString('telephone');
        $resultArr = UserModel::model()->addUser($paramArr);
        $this->renderJson($resultArr);
    }

    /**
     * @desc 获取用户明细信息
     * @author heguangquan
     * @date 2015-02-12
     */
    public function actionGetUserDetail()
    {
        $this->apiChecklogin(__METHOD__);
        
        $userId = CInputFilter::getInt('userid');
        $resultArr = UserModel::model()->getUserDetail($userId);
        $this->renderJson($resultArr);
    }

    /**
     * @desc 修改用户
     * @author heguangquan
     * @date 2015-02-06
     */
    public function actionEditUser()
    {
        $this->apiChecklogin(__METHOD__);
        
        $userId = CInputFilter::getInt('userid');
        $paramArr['username'] = CInputFilter::getString('username');
        $paramArr['realname'] = CInputFilter::getString('realname');
        $paramArr['password'] = CInputFilter::getString('password');
        $paramArr['email'] = CInputFilter::getString('email');
        $paramArr['telephone'] = CInputFilter::getString('telephone');
        $resultArr = UserModel::model()->editUser($userId, $paramArr);
        $this->renderJson($resultArr);
    }

    /**
     * @desc 获取店铺列表, int $page 页数, int $pageSize 页大小
     * @author YangLong
     * @date 2015-02-09
     */
    public function actionGetShopList()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $page = CInputFilter::getInt('page', EnumOther::PAGE);
        $pageSize = CInputFilter::getInt('pageSize', EnumOther::PAGESIZE);
        $resultArr = ShopModel::model()->shopList($page, $pageSize);
        $this->renderJson($resultArr);
    }

    /**
     * @desc 店铺删除, string $sids 店铺ID(以,连接，如:1,2)
     * @author YangLong
     * @date 2015-02-09
     */
    public function actionSetShopDel()
    {
        $this->apiChecklogin(__METHOD__);
        
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $sids = CInputFilter::getnorepeatInts('sids');
        $resultArr = ShopModel::model()->setShopDel($sids, $sellerId);
        $this->renderJson($resultArr);
    }

    /**
     * @desc 禁用或启用店铺, string $sids 店铺ID(以,连接，如:1,2)
     * @author YangLong
     * @date 2015-09-18
     */
    public function actionSetShopStatus()
    {
        $this->apiChecklogin(__METHOD__);
        
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $action = CInputFilter::getString('action');
        $sids = CInputFilter::getnorepeatInts('sids');
        $resultArr = ShopModel::model()->setShopStatus($sids, $action, $sellerId);
        $this->renderJson($resultArr);
    }

    /**
     * @desc 站点显示
     * @author liaojianwen
     * @date 2015-02-26
     * @modify 2015-03-06 YangLong 增加只返回有账户的站点的功能
     */
    public function actionGetSiteList()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $filter = CInputFilter::getBool('filter', false);
        $result = ShopModel::model()->siteList($filter);
        $this->renderJson($result);
    }

    /**
     * @desc 登录API
     * @author liaojianwen
     * @date 2015-03-02
     */
    public function actionLogin()
    {
        $username = CInputFilter::getString('username', '');
        $password = CInputFilter::getString('password', '');
        $code = CInputFilter::getString('code');
        
        // TODO XXX 此处有严重的绕过验证码的漏洞
        $isVerify = CInputFilter::getString('isverify');
        $result = $this->login($username, $password, $code, $isVerify);
        $this->renderJson($result);
    }

    /**
     * @desc 用户注销
     * @author liaojianwen
     * @date 2015-03-04
     */
    public function actionLogout()
    {
        $result = $this->logout();
        $this->renderJson($result);
    }

    /**
     * @desc 检测用户名是否存在
     * @author heguangquan
     * @date 2015-03-02
     */
    public function actionCheckUser()
    {
        $userName = CInputFilter::getString('username');
        $resultArr = UserModel::model()->checkUser($userName);
        $this->renderJson($resultArr);
    }

    /**
     * @desc 注册用户
     * @author heguangquan
     * @date 2015-03-02
     */
    public function actionRegisterUser()
    {
        $paramArr['username'] = CInputFilter::getString('username');
        $paramArr['realname'] = CInputFilter::getString('realname');
        $paramArr['password'] = CInputFilter::getString('password');
        $paramArr['email'] = CInputFilter::getString('email');
        $paramArr['telephone'] = CInputFilter::getString('telephone');
        $paramArr['verifycode'] = CInputFilter::getString('verifycode');
        $resultArr = UserModel::model()->addUser($paramArr, false);
        $this->renderJson($resultArr);
    }

    /**
     * @desc 获取用户名
     * @author liaojianwen,YangLong
     * @date 2015-03-03
     */
    public function actionGetUserName()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $result = UserModel::model()->getUserName();
        $this->renderJson($result);
    }

    /**
     * @desc 调用ebay API删除消息
     * @author liaojianwen
     * @date 2015-03-04
     */
    public function actionCallApiDelMsg()
    {
        $this->apiChecklogin(__METHOD__);
        
        $mids = CInputFilter::getnorepeatInts('mids');
        $this->renderJson(MsgDealModel::model()->deleteMyMessages($mids));
    }

    /**
     * @desc 调用ebay API还原消息
     * @author liaojianwen
     * @date 2015-03-04
     */
    public function actionRevertMyMessages()
    {
        $this->apiChecklogin(__METHOD__);
        
        $mids = CInputFilter::getnorepeatInts('mids');
        // mids 是id, 0 是收件箱的forderId
        $this->renderJson(MsgDealModel::model()->setMessagesStatus($mids, '0', 2));
    }

    /**
     * @desc 调用ebay API 标记消息已读
     * @author liaojianwen
     * @date 2015-03-04
     */
    public function actionSetReadMyMessages()
    {
        $this->apiChecklogin(__METHOD__);
        
        $mids = CInputFilter::getnorepeatInts('mids');
        $this->renderJson(MsgDealModel::model()->setMessagesStatus($mids, 'true', 1));
    }

    /**
     * @desc 调用ebay API 标记消息未读
     * @author liaojianwen
     * @date 2015-03-04
     */
    public function actionSetMsgNoReadByApi()
    {
        $this->apiChecklogin(__METHOD__);
        
        $mids = CInputFilter::getnorepeatInts('mids');
        $this->renderJson(MsgDealModel::model()->setMessagesStatus($mids, 'false', 1));
    }

    /**
     * @desc 切换站点
     * @author YangLong
     * @date 2015-03-04
     */
    public function actionSwitchSite()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $siteId = CInputFilter::getInt('siteId', - 1);
        $result = UserModel::model()->switchSite($siteId);
        $this->renderJson($result);
    }

    /**
     * @desc 切换账号
     * @author YangLong
     * @date 2015-03-04
     */
    public function actionSwitchAccount()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $accountId = CInputFilter::getInt('accountId');
        $result = UserModel::model()->switchAccount($accountId);
        $this->renderJson($result);
    }

    /**
     * @desc 返回验证码
     * @author liaojianwen
     * @date 2015-03-05
     * @modify 2015-03-12 YangLong 方法静态化
     */
    public function actionGetCode()
    {
        VerifyCode::getCode(4, 20); // 4个数字，显示大小为20
    }

    /**
     * @desc 返回买家发起case列表
     * @author lvjianfei liaojiannwen
     * @date 2015-04-02
     */
    public function actionGetCaseList()
    {
        $this->apiChecklogin(ActionsEnum::CASE_READ);
        
        $page = CInputFilter::getInt('page', EnumOther::PAGE);
        $pageSize = CInputFilter::getInt('pageSize', EnumOther::PAGESIZE);
        $Type = CInputFilter::getString('type'); // 获取case的类型
        $status = CInputFilter::getString('status'); // 查询条件中的状态
        $itemId = CInputFilter::getString('itemid'); // 查询条件中的item NO
        $cust = CInputFilter::getString('buyer'); // 查询条件中的客户
        $query = CInputFilter::getString('query'); // 判断是查询还是列表展示/query 为查询
        $result = CaseModel::model()->getCaseList($Type, $page, $pageSize, $status, $itemId, $cust, $query);
        $this->renderJson($result);
    }

    /**
     * @desc 获取买家发起case详细页的内容
     * @author lvjianfei
     * @date 2015-4-2
     */
    public function actionGetCaseDetail()
    {
        $this->apiChecklogin(ActionsEnum::CASE_READ);
        
        $caseid = CInputFilter::getnorepeatInts('caseid');
        $type = CInputFilter::getString('type');
        $result = CaseDetailModel::model()->getCaseDetail($caseid, $type);
        $this->renderJson($result);
    }

    /**
     * @desc 获取买家发起case对话历史
     * @author lvjianfei
     * @date 2015-4-2
     */
    public function actionGetCaseHistory()
    {
        $this->apiChecklogin(ActionsEnum::CASE_READ);
        
        $caseid = CInputFilter::getnorepeatInts('caseid');
        $result = CaseDetailModel::model()->getCaseResponseHistory($caseid);
        $this->renderJson($result);
    }

    /**
     * @desc 获取case备注列表
     * @author lvjianfei,liaojianwen
     * @date 2015-04-03
     * @modify 2015-04-21
     */
    public function actionGetItemNoteList()
    {
        $this->apiChecklogin(ActionsEnum::NOTE_READ);
        
        $itemId = CInputFilter::getString('itemId');
        $type = CInputFilter::getString('type');
        if ($type === 'case') {
            $dealId = CInputFilter::getInt('caseId');
        } elseif ($type === 'return') {
            $dealId = CInputFilter::getInt('returnid');
        } else {
            $dealId = CInputFilter::getInt('msgId');
        }
        $result = CaseDetailModel::model()->getItemNoteList($itemId, $type, $dealId);
        $this->renderJson($result);
    }

    /**
     * @desc 添加case备注
     * @author lvjianfei,liaojianwen
     * @date 2015-04-03
     * @modify 2015-04-21
     */
    public function actionAddItemNote()
    {
        $this->apiChecklogin(__METHOD__);
        
        $text = CInputFilter::getString('text');
        $msgId = CInputFilter::getInt('msgid', 0);
        $caseId = CInputFilter::getInt('caseid', 0);
        $returnId = CInputFilter::getInt('returnid', 0);
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        if ($msgId > 0) {
            $result = MsgDealModel::model()->addItemNote($text, $msgId, $sellerId);
        } elseif ($returnId > 0) {
            $result = ReturnDetailModel::model()->addItemNote($text, $returnId, $sellerId);
        } else {
            $result = CaseDetailModel::model()->addItemNote($text, $caseId, $sellerId);
        }
        $this->renderJson($result);
    }

    /**
     * @desc 获取卖家发起case的详细信息
     * @author lvjianfei
     * @date 2015-04-08
     */
    public function actionGetCaseDisputeDetail()
    {
        $this->apiChecklogin(ActionsEnum::CASE_READ);
        
        $caseid = CInputFilter::getInt('caseid');
        $type = CInputFilter::getString('type');
        $result = CaseDisputeModel::model()->getCaseDisputeDetail($caseid, $type);
        $this->renderJson($result);
    }

    /**
     * @desc 获取卖家发起case的历史对话
     * @author lvjianfei
     * @date 2015-04-08
     */
    public function actionGetCaseDisputeMessage()
    {
        $this->apiChecklogin(ActionsEnum::CASE_READ);
        
        $caseid = CInputFilter::getInt('caseid');
        $result = CaseDisputeModel::model()->getCaseDisputeMessage($caseid);
        $this->renderJson($result);
    }

    /**
     * @desc 返回卖家发起case列表
     * @author lvjianfei 
     * @date 2015-04-08
     */
    public function actionGetCaseDisputeList()
    {
        $this->apiChecklogin(ActionsEnum::CASE_READ);
        
        $page = CInputFilter::getInt('page', EnumOther::PAGE);
        $pageSize = CInputFilter::getInt('pageSize', EnumOther::PAGESIZE);
        $Type = CInputFilter::getString('type');
        $status = CInputFilter::getString('status'); // 查询条件中的状态
        $itemId = CInputFilter::getString('itemid'); // 查询条件中的item NO
        $cust = CInputFilter::getString('buyer'); // 查询条件中的客户
        $query = CInputFilter::getString('query'); // 判断是查询还是列表展示/query 为查询
        $result = CaseDisputeModel::model()->getCaseDisputeList($Type, $page, $pageSize, $status, $itemId, $cust, $query);
        $this->renderJson($result);
    }

    /**
     * @desc 回复case
     * @author liaojianwen
     * @date 2015-04-15
     */
    public function actionAddResponse()
    {
        $this->apiChecklogin(__METHOD__);
        
        $text = CInputFilter::getNSvalue('text');
        $type = CInputFilter::getString('type');
        $caseId = CInputFilter::getInt('case_id'); // case表主键
        $caseId_id = CInputFilter::getInt('caseId_id'); // caseID
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = CaseDetailModel::model()->addResponse($caseId, $type, 'SELLER', $text, $sellerId, $caseId_id);
        $this->renderJson($result);
    }

    /**
     * @desc 添加追踪号
     * @author liaojianwen
     * @date 2015-04-15
     */
    public function actionAddTrackingInfo()
    {
        $this->apiChecklogin(__METHOD__);
        
        $text = CInputFilter::getNSvalue('text');
        $carrier = CInputFilter::getString('carrier');
        $trackingNum = CInputFilter::getString('num');
        $castType = CInputFilter::getString('type');
        $caseId = CInputFilter::getInt('case_id'); // case表主键
        $caseId_id = CInputFilter::getInt('caseId_id'); // caseID
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = CaseDetailModel::model()->addTrackingInfo($caseId, $castType, 'SELLER', $carrier, $trackingNum, $text, $sellerId, $caseId_id);
        $this->renderJson($result);
    }

    /**
     * @desc 添加货运信息
     * @author liaojianwen
     * @date 2015-04-15
     */
    public function actionAddShippingInfo()
    {
        $this->apiChecklogin(__METHOD__);
        
        $text = CInputFilter::getNSvalue('text');
        $carrier = CInputFilter::getString('carrier');
        $shipDate = CInputFilter::getString('shipdate');
        $castType = CInputFilter::getString('type');
        $caseId = CInputFilter::getInt('case_id'); // case表主键
        $caseId_id = CInputFilter::getInt('caseId_id'); // caseID
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = CaseDetailModel::model()->addShippingInfo($caseId, $castType, 'SELLER', $carrier, $shipDate, $text, $sellerId, $caseId_id);
        $this->renderJson($result);
    }

    /**
     * @desc 全额退款
     * @author liaojianwen
     * @date 2015-04-15
     */
    public function actionFullRefund()
    {
        $this->apiChecklogin(__METHOD__);
        
        $text = CInputFilter::getNSvalue('text');
        $type = CInputFilter::getString('type');
        $caseId = CInputFilter::getInt('case_id'); // case表主键
        $caseId_id = CInputFilter::getInt('caseId_id'); // caseID
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = CaseDetailModel::model()->fullRefund($caseId, $type, 'SELLER', $text, $sellerId, $caseId_id);
        $this->renderJson($result);
    }

    /**
     * @desc 部分退款
     * @author liaojianwen
     * @date 2015-04-15
     */
    public function actionPartialRefund()
    {
        $this->apiChecklogin(__METHOD__);
        
        $text = CInputFilter::getNSvalue('text');
        $type = CInputFilter::getString('type');
        $caseId = CInputFilter::getInt('case_id'); // case表主键
        $caseId_id = CInputFilter::getInt('caseId_id'); // caseID
        $amount = CInputFilter::getInt('amount');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = CaseDetailModel::model()->partialRefund($caseId, $type, 'SELLER', $text, $amount, $sellerId, $caseId_id);
        $this->renderJson($result);
    }

    /**
     * @desc 退货兼退款
     * @author liaojianwen
     * @date 2015-04-15
     */
    public function actionReturnItemRefund()
    {
        $this->apiChecklogin(__METHOD__);
        
        $text = CInputFilter::getNSvalue('text');
        $type = CInputFilter::getString('type');
        $caseId = CInputFilter::getInt('case_id'); // case表主键
        $caseId_id = CInputFilter::getInt('caseId_id'); // caseID
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $address['country'] = CInputFilter::getString('country');
        $address['state'] = CInputFilter::getString('state');
        $address['city'] = CInputFilter::getString('city');
        $address['street'] = CInputFilter::getString('street');
        $address['street2'] = CInputFilter::getString('street2');
        $address['contractName'] = CInputFilter::getString('name');
        $address['postcode'] = CInputFilter::getString('postcode');
        $address['merchantAuth'] = CInputFilter::getString('code');
        $result = CaseDetailModel::model()->returnItemRefund($caseId, $type, 'SELLER', $text, $sellerId, $caseId_id, $address);
        $this->renderJson($result);
    }

    /**
     * @desc ebay介入case处理
     * @author liaojianwen
     * @date 2015-04-15
     */
    public function actionEbayHelp()
    {
        $this->apiChecklogin(__METHOD__);
        
        $text = CInputFilter::getNSvalue('text');
        $type = CInputFilter::getString('type');
        $caseId = CInputFilter::getInt('case_id'); // case表主键
        $caseId_id = CInputFilter::getInt('caseId_id'); // caseID
        $reason = CInputFilter::getString('reason');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = CaseDetailModel::model()->ebayHelp($caseId, $type, 'SELLER', $text, $reason, $sellerId, $caseId_id);
        $this->renderJson($result);
    }

    /**
     * @desc 获取case处理记录
     * @author lvjianfei
     * @date 2015-04-20
     */
    public function actionGetCaseHandleLog()
    {
        $this->apiChecklogin(ActionsEnum::CASE_READ);
        
        $caseid = CInputFilter::getInt('case_id');
        $result = CaseDetailModel::model()->getCaseHandleLog($caseid);
        $this->renderJson($result);
    }

    /**
     * @desc 获取国家列表
     * @author liaojianwen
     * @date 2015-04-22
     */
    public function actionGetCountryList()
    {
        $this->apiChecklogin(__METHOD__);
        
        $result = EbayOtherInfoModel::model()->getCountryList();
        $this->renderJson($result);
    }

    /**
     * @desc 获取当前用户店铺列表(用于添加case)
     * @author lvjianfei
     * @date 2014-04-22
     */
    public function actionGetShopName()
    {
        $this->apiChecklogin(__METHOD__);
        
        $result = ShopModel::model()->getShopName();
        $this->renderJson($result);
    }

    /**
     * @desc 添加case
     * @author lvjianfei
     * @date 2015-04-22
     * @modifyby liaojianwen
     * @modifydate 2015-07-30  
     */
    public function actionAddCase()
    {
        $this->apiChecklogin(__METHOD__);
        $orderInfo = CInputFilter::getArray('orderInfo', '');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = CaseDetailModel::model()->addDispute($orderInfo, $sellerId);
        return $this->renderJson($result);
    }

    /**
     * @desc 调用cdc接口查询订单数据
     * @author lvjianfei
     * @data 2015-04-23
     */
    public function actionSearchItem()
    {
        $this->apiChecklogin(__METHOD__);
        
        $ItemID = CInputFilter::getString('ItemID');
        $BuyerUserID = CInputFilter::getString('BuyerUserID');
        $shop_id = CInputFilter::getString('shopId');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $page = CInputFilter::getInt('page', 1);
        $pageSize = CInputFilter::getInt('pageSize', 20);
        $result = CaseDisputeModel::model()->searchOrder($ItemID, $BuyerUserID, $shop_id, $sellerId, $page, $pageSize);
        $this->renderJson($result);
    }

    /**
     * @desc (eBay)根据msg表自增ID获取Item详细信息保存入数据库并返回有用的部分
     * @author YangLong
     * @date 2015-05-04
     */
    public function actionGetItemInfoByItemId()
    {
        $this->apiChecklogin(ActionsEnum::MSG_READ);
        
        $msgid = CInputFilter::getInt('msgid', 0);
        $caseid = CInputFilter::getInt('caseid', 0);
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $data = EbayOtherInfoModel::model()->eBayGetItemInfo($msgid, $sellerId, $caseid);
        $this->renderJson($data);
    }

    /**
     * @desc上传文件后台处理
     * @author lvjianfei,YangLong
     * @date 2015-05-04
     */
    public function actionUpload()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $path = Yii::app()->params['upload_url'];
        $refid = CInputFilter::getInt('refid');
        $src = CInputFilter::getString('src');
        $result = UploadModel::model()->uploadImage($path, $refid, $src);
        $this->renderJson($result);
    }

    /**
     * @desc上传文件后台处理
     * @author lvjianfei,YangLong
     * @date 2015-05-04
     */
    public function actionUploadFile()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $path = Yii::app()->params['upload_url'];
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = UploadModel::model()->uploadFile($path, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc (eBay)根据caseId获取一些客户评价相关信息
     * @author liaojianwen
     * @date 2015-05-15
     */
    public function actionGetFeedbackInfo()
    {
        $this->apiChecklogin(ActionsEnum::CASE_READ);
        
        $caseId = CInputFilter::getInt('caseid', 0);
        $returnId = CInputFilter::getInt('returnid', 0);
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $data = EbayOtherInfoModel::model()->getFeedbackInfo($caseId, $sellerId, $returnId);
        $this->renderJson($data);
    }

    /**
     * @desc (eBay)根据caseId获取一些客户留言相关信息
     * @author liaojianwen
     * @date 2015-05-21
     */
    public function actionGetEbayOrderNote()
    {
        $this->apiChecklogin(ActionsEnum::CASE_READ);
        
        $caseId = CInputFilter::getInt('caseid', 0);
        $return_id = CInputFilter::getInt('returnid', 0);
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $data = EbayOtherInfoModel::model()->getEbayOrderNote($caseId, $sellerId, $return_id);
        $this->renderJson($data);
    }

    /**
     * @desc 生成回复信息队列
     * @author heguangquan
     * @date 2015-03-04
     * @modify liaojianwen
     * @modify 2015-05-22 YangLong
     */
    public function actionReplyMsg()
    {
        $this->apiChecklogin(__METHOD__);
        
        $msgId = CInputFilter::getInt('msgid');
        $imgUrl = CInputFilter::getArray('imgurl', '');
        $copy = CInputFilter::getString('copy');
        $content = CInputFilter::getNSvalue('content');
        $result = MsgQueueModel::model()->replyMsg($msgId, $imgUrl, $copy, $content);
        if ($result['Ack'] === 'Success') {
            $Replied = EnumMsgStatus::REPLIED_SUCCE;
            $handled = EnumMsgStatus::HANDLED_YES;
            $send_status = EnumMsgStatus::SEND_STATUS_NORMAL;
            MsgDealModel::model()->UpdateMsgStatus($msgId, $Replied, $handled, $send_status);
        }
        $this->renderJson($result);
    }

    /**
     * @desc 获取最新一条回复的消息
     * @author YangLong
     * @date 2015-05-22
     */
    public function actionGetLastSendMsg()
    {
        $this->apiChecklogin(__METHOD__);
        
        $msgid = CInputFilter::getInt('msgid');
        $result = EbayOtherInfoModel::model()->getLastSendMsg($msgid);
        $this->renderJson($result);
    }

    /**
     * @desc 根据ebay userid获取ebay用户信息
     * @author YangLong
     * @date 2015-06-15
     */
    public function actionGetEbayUserInfo()
    {
        $this->apiChecklogin(ActionsEnum::MSG_READ);
        
        $userid = CInputFilter::getString('userid');
        $result = EbayOtherInfoModel::model()->getEbayUserInfo($userid);
        $this->renderJson($result);
    }

    /**
     * @desc 根据OrderID获取相关信息
     * @author YangLong
     * @date 2015-06-15
     */
    public function actionGetEbayTransactionInfo()
    {
        $this->apiChecklogin(ActionsEnum::MSG_READ);
        
        $ItemID = CInputFilter::getString('ItemID');
        $OrderLineItemID = CInputFilter::getString('OrderLineItemID');
        $result = EbayOtherInfoModel::model()->getEbayTransactionInfo($ItemID, $OrderLineItemID);
        $this->renderJson($result);
    }

    /**
     * @desc 根据OrderID获取相关信息
     * @author YangLong
     * @date 2015-06-15
     */
    public function actionGetEbayOrderInfo()
    {
        $this->apiChecklogin(ActionsEnum::MSG_READ);
        
        $OrderLineItemID = CInputFilter::getString('OrderLineItemID');
        $result = EbayOtherInfoModel::model()->getEbayOrderInfo($OrderLineItemID);
        $this->renderJson($result);
    }

    /**
     *@desc 通过orderLIneItemID 获取评价
     *@author liaojianwen
     *@date 2015-06-17
     */
    public function actionGetEbayFeedbackInfoByOLID()
    {
        $this->apiChecklogin(ActionsEnum::MSG_READ);
        
        $OrderLineItemID = CInputFilter::getString('OrderLineItemID');
        $data = MsgDealModel::model()->getFeedbackInfo($OrderLineItemID);
        $this->renderJson($data);
    }

    /**
     * @desc 根据UserId获取orders
     * @author YangLong
     * @date 2015-06-17
     */
    public function actionGetEbayOrdersByUserId()
    {
        $this->apiChecklogin(ActionsEnum::MSG_READ);
        
        $BuyerUserID = CInputFilter::getString('BuyerUserID');
        $result = EbayOtherInfoModel::model()->getEbayOrdersByUserId($BuyerUserID);
        $this->renderJson($result);
    }

    /**
     * @desc 根据UserId获取transations
     * @author YangLong
     * @date 2015-06-17
     */
    public function actionGetEbayTransactionsByUserId()
    {
        $this->apiChecklogin(ActionsEnum::MSG_READ);
        
        $BuyerUserID = CInputFilter::getString('BuyerUserID');
        $EIASToken = CInputFilter::getString('EIASToken');
        $result = EbayOtherInfoModel::model()->getEbayTransactionsByUserId($BuyerUserID, $EIASToken);
        $this->renderJson($result);
    }

    /**
     * @desc 获取return列表
     * @author liaojianwen
     * @date 2015-06-18
     */
    public function actionGetReturnList()
    {
        $this->apiChecklogin(ActionsEnum::RETURN_READ);
        
        $page = CInputFilter::getInt('page', EnumOther::PAGE);
        $pageSize = CInputFilter::getInt('pageSize', EnumOther::PAGESIZE);
        $status = CInputFilter::getString('status'); // 查询条件中的状态
        $itemId = CInputFilter::getString('itemid'); // 查询条件中的item NO
        $cust = CInputFilter::getString('buyer'); // 查询条件中的客户
        $result = ReturnModel::model()->getReturnList($status, $itemId, $cust, $page, $pageSize);
        $this->renderJson($result);
    }

    /**
     * @desc 获取return明细
     * @author liaojianwen
     * @date 2015-06-19
     */
    public function actionGetReturnDetail()
    {
        $this->apiChecklogin(ActionsEnum::RETURN_READ);
        
        $return_id = CInputFilter::getnorepeatInts('returnid');
        $result = ReturnDetailModel::model()->getReturnDetail($return_id);
        $this->renderJson($result);
    }

    /**
     * @desc 获取return的图片数
     * @author liaojianwen
     * @date 2015-09-24
     */
    public function actionGetDocCount()
    {
        $this->apiChecklogin(ActionsEnum::RETURN_READ);
        
        $return_id = CInputFilter::getString('returnid');
        $result = ReturnModel::model()->getDocCount($return_id);
        $this->renderJson($result);
    }

    /**
     * @desc 根据UserId获取Msgs
     * @author YangLong
     * @date 2015-06-18
     */
    public function actionGetMsgsByUserID()
    {
        $this->apiChecklogin(ActionsEnum::MSG_READ);
        
        $UserID = CInputFilter::getString('UserID');
        $page = CInputFilter::getInt('page', 1);
        $limit = CInputFilter::getInt('limit', 10);
        $result = MsgDealModel::model()->getMsgsByUserID($UserID, $page, $limit);
        $this->renderJson($result);
    }

    /**
     * @desc 编辑店铺别名
     * @author YangLong
     * @date 2015-06-19
     */
    public function actionSetShopNickname()
    {
        $this->apiChecklogin(__METHOD__);
        
        $shopId = CInputFilter::getString('shopId');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $nickname = CInputFilter::getString('nickname');
        $result = ShopModel::model()->setShopNickname($sellerId, $shopId, $nickname);
        $this->renderJson($result);
    }

    /**
     * @desc 获取消息的相关消息的id
     * @author YangLong
     * @date 2015-11-10
     */
    public function actionGetMsgRelationIds()
    {
        $msgId = CInputFilter::getInt('msgid');
        $result = MsgDealModel::model()->getMsgRelationIds($msgId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取历史消息正文
     * @author YangLong
     * @date 2015-06-26
     */
    public function actionGetMsgTexts()
    {
        $this->apiChecklogin(ActionsEnum::MSG_READ);
        
        $msgId = CInputFilter::getInt('msgid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = MsgDealModel::model()->getMsgTexts($sellerId, $msgId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取消息的客服名
     * @author YangLong
     * @date 2015-06-29
     */
    public function actionGetMsgTextsCS()
    {
        $this->apiChecklogin(ActionsEnum::MSG_READ);
        
        $md5s = CInputFilter::getString('md5s');
        $md5s = preg_replace('/[^a-fA-F0-9,]+/', '', $md5s);
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $userId = CInputFilter::getString('userId');
        $result = MsgDealModel::model()->getMsgTextsCS($sellerId, $md5s, $userId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取用户的注册站点
     * @author YangLong
     * @date 2015-06-29
     */
    public function actionGetUserRegSite()
    {
        $this->apiChecklogin(__METHOD__);
        
        $userId = CInputFilter::getString('userId');
        $result = MsgDealModel::model()->getUserRegSite($userId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取消息的详情
     * @author YangLong
     * @date 2015-06-30
     */
    public function actionGetMsg()
    {
        $this->apiChecklogin(ActionsEnum::MSG_READ);
        
        $msgid = CInputFilter::getString('msgid');
        $result = MsgDealModel::model()->getMsg($msgid);
        $this->renderJson($result);
    }

    /**
     * @desc 获取上下页
     * @author liaojianwen
     * @date 2015-06-24
     */
    public function actionGetReturnPreNextID()
    {
        $this->apiChecklogin(ActionsEnum::RETURN_READ);
        
        $returnid = CInputFilter::getInt('returnid', 0);
        $creationDate = CInputFilter::getInt('date', 0);
        $status = CInputFilter::getString('status'); // 查询条件中的状态
        $itemId = CInputFilter::getString('itemid'); // 查询条件中的item NO
        $cust = CInputFilter::getString('buyer'); // 查询条件中的客户
        $result = ReturnDetailModel::model()->getReturnPreNextID($returnid, $creationDate, $status, $itemId, $cust);
        $this->renderJson($result);
    }

    /**
     * @desc 获取历史
     * @author lioajianwen
     * @date 2015-06-25
     */
    public function actionGetReturnHistory()
    {
        $this->apiChecklogin(ActionsEnum::RETURN_READ);
        
        $returnid = CInputFilter::getInt('returnid', 0);
        $result = ReturnDetailModel::model()->getReturnHistory($returnid);
        $this->renderJson($result);
    }

    /**
     * @desc 通过caseid 获取requestID
     * @author liaojianwen
     * @date 2015-06-30
     */
    public function actionGetReturnRequest()
    {
        $this->apiChecklogin(ActionsEnum::RETURN_READ);
        
        $caseid = CInputFilter::getInt('caseid', 0);
        $result = ReturnDetailModel::model()->getReturnRequest($caseid);
        $this->renderJson($result);
    }

    /**
     * @desc 获取Return退货地址
     * @author liaojianwen
     * @date 2015-06-30
     */
    public function actionGetReturnAddr()
    {
        $this->apiChecklogin(ActionsEnum::RETURN_READ);
        
        $returnid = CInputFilter::getInt('returnid', 0);
        $result = ReturnDetailModel::model()->getReturnAddr($returnid);
        $this->renderJson($result);
    }

    /**
     * @desc approve request 
     * @author liaojianwen
     * @date 2015-06-30
     */
    public function actionApproveReturn()
    {
        $this->apiChecklogin(__METHOD__);
        
        $returnid = CInputFilter::getInt('returnid', 0);
        $RMA = CInputFilter::getString('RMA');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = ReturnDetailModel::model()->approveReturn($returnid, $RMA, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc issue refund return 退全款
     * @author liaojianwen
     * @date 2015-07-01
     */
    public function actionIssueReturnRefund()
    {
        $this->apiChecklogin(__METHOD__);
        
        $returnid = CInputFilter::getInt('returnid', 0);
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = ReturnDetailModel::model()->issueReturnRefund($returnid, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc offer partail refund 退部分款
     * @author liaojianwen
     * @date 2015-07-01
     */
    public function actionIssueReturnPartRefund()
    {
        $this->apiChecklogin(__METHOD__);
        
        $returnid = CInputFilter::getInt('returnid', 0);
        $amount = CInputFilter::getString('partamount');
        $currencyId = CInputFilter::getString('currencyId');
        $text = CInputFilter::getString('text');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = ReturnDetailModel::model()->issueReturnPartRefund($returnid, $amount, $currencyId, $text, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc return 发送消息
     * @author liaojianwen
     * @date 2015-07-01
     */
    public function actionSendReturnMsg()
    {
        $this->apiChecklogin(__METHOD__);
        
        $returnid = CInputFilter::getInt('returnid', 0);
        $text = CInputFilter::getString('text');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = ReturnDetailModel::model()->sendReturnMsg($returnid, $text, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc return ebay 介入
     * @author liaojianwen
     * @date 2015-07-01
     */
    public function actionReturnAskHelp()
    {
        $this->apiChecklogin(__METHOD__);
        
        $returnid = CInputFilter::getInt('returnid', 0);
        $text = CInputFilter::getString('text');
        $reason = CInputFilter::getString('reason');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = ReturnDetailModel::model()->returnAskHelp($returnid, $text, $reason, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 拒绝return 
     * @author liaojiannwen
     * @date 2015-08-10
     */
    public function actionDeclineReturn()
    {
        $this->apiChecklogin(__METHOD__);
        
        $returnid = CInputFilter::getInt('returnid', 0);
        $text = CInputFilter::getString('text');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = ReturnDetailModel::model()->declineReturns($returnid, $text, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取可操作步骤
     * @author liaojianwen
     * @date 2015-07-01
     */
    public function actionGetSellerOptions()
    {
        $this->apiChecklogin(ActionsEnum::RETURN_READ);
        
        $returnid = CInputFilter::getInt('returnid', 0);
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = ReturnDetailModel::model()->getSellerOptions($returnid, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取return 状态
     * @author liaojianwen
     * @date 2015-07-02
     */
    public function actionGetReturnState()
    {
        $this->apiChecklogin(ActionsEnum::RETURN_READ);
        
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = ReturnModel::model()->getReturnState($sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc case明细中获取相关request的历史
     * @author liaojianwen
     * @date 2015-07-07
     */
    public function actionGetReturn2Case()
    {
        $this->apiChecklogin(ActionsEnum::CASE_READ);
        
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $returnId_id = CInputFilter::getString('returnId_id');
        $result = ReturnDetailModel::model()->getReturn2Case($returnId_id, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @case 提供临时退货地址
     * @author liaojianwen
     * @date 2015-07-07
     */
    public function actionProvideReturnInfo()
    {
        $this->apiChecklogin(__METHOD__);
        
        $caseid = CInputFilter::getString('case_id');
        $type = CInputFilter::getString('type');
        $addr['name'] = CInputFilter::getString('name');
        $addr['street1'] = CInputFilter::getString('street1');
        $addr['street2'] = CInputFilter::getString('street2');
        $addr['city'] = CInputFilter::getString('city');
        $addr['state'] = CInputFilter::getString('state');
        $addr['country'] = CInputFilter::getString('country');
        $addr['postalCode'] = CInputFilter::getString('ZIP');
        $rma = CInputFilter::getString('RMA');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $caseId_id = CInputFilter::getInt('caseId_id'); // caseID
        $result = CaseDetailModel::model()->provideReturnInfo($caseid, $type, $addr, $rma, $caseId_id, $sellerId);
        $this->renderJson($result);
    }

    /*
     * @desc 获取product name
     * @author liaojianwen
     * @date 2015-07-11
     */
    public function actionGetProductName()
    {
        $this->apiChecklogin(ActionsEnum::RETURN_READ);
        
        $orderLineItemId = CInputFilter::getString('OLI');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = ReturnDetailModel::model()->getProductName($orderLineItemId, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取某个买家对卖家的评价统计信息
     * @author YangLong
     * @date 2015-07-10
     */
    public function actionGetBuyerToSellerFeedbackInfo()
    {
        $this->apiChecklogin(ActionsEnum::MSG_READ);
        
        $userId = CInputFilter::getString('userId');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = MsgDealModel::model()->getBuyerToSellerFeedbackInfo($userId, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc case 查找处理人、处理方式
     * @author liaojianwen
     * @date 2015-07-15
     */
    public function actionGetCaseOperator()
    {
        $this->apiChecklogin(ActionsEnum::CASE_READ);
        
        $caseid = CInputFilter::getString('caseid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = CaseDetailModel::model()->getCaseOperator($caseid, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc return 查找处理人、处理方式
     * @author liaojianwen
     * @date 2015-07-24
     */
    public function actionGetReturnOperator()
    {
        $this->apiChecklogin(ActionsEnum::RETURN_READ);
        
        $returnid = CInputFilter::getString('returnid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = ReturnDetailModel::model()->getReturnOperator($returnid, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取订单退款信息
     * @author liaojianwen
     * @date 2015-07-17
     */
    public function actionGetOredersRefund()
    {
        $this->apiChecklogin(ActionsEnum::RETURN_READ);
        
        $return_id = CInputFilter::getString('returnid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = ReturnDetailModel::model()->getOrdersRefund($return_id, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc case 向ebay 申诉
     * @author liaojianwen
     * @date 2015-07-22
     */
    public function actionAppealEbay()
    {
        $this->apiChecklogin(__METHOD__);
        
        $caseid = CInputFilter::getString('case_id');
        $type = CInputFilter::getString('type');
        $appealReason = CInputFilter::getString('appeal');
        $text = CInputFilter::getString('text');
        $caseId_id = CInputFilter::getString('caseId_id');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        
        $result = CaseDetailModel::model()->appealEbay($caseid, $type, $appealReason, $text, $caseId_id, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取消息回复队列里的消息是否已回复成功(阻塞式)
     * @author YangLong
     * @date 2015-07-27
     */
    public function actionGetMsgReplyStatus()
    {
        $this->apiChecklogin(__METHOD__);
        
        $qpk = CInputFilter::getInt('qpk');
        $result = MsgQueueModel::model()->getMsgReplyStatus($qpk);
        $this->renderJson($result);
    }

    /**
     * @desc 根据ItemID获取备注信息
     * @author YangLong
     * @date 2015-07-28
     */
    public function actionGetItemNotes()
    {
        $this->apiChecklogin(ActionsEnum::NOTE_READ);
        
        $itemId = CInputFilter::getString('itemId');
        $result = EbayOtherInfoModel::model()->getItemNotes($itemId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取用户地址信息
     * @author YangLong
     * @date 2015-07-30
     */
    public function actionGetUserAddress()
    {
        $this->apiChecklogin(__METHOD__);
        
        $userId = CInputFilter::getString('userId');
        $EIASToken = CInputFilter::getString('EIASToken');
        $result = EbayOtherInfoModel::model()->getUserAddress($userId, $EIASToken);
        $this->renderJson($result);
    }

    /**
     * @desc 根据ExtTransID获取PayPal地址
     * @author YangLong
     * @date 2015-08-07
     */
    public function actionGetPaypalAddressByExtTransID()
    {
        $this->apiChecklogin(ActionsEnum::MSG_READ);
        
        $TransactionID = CInputFilter::getString('TransactionID');
        $BuyerID = CInputFilter::getString('BuyerID');
        $result = EbayOtherInfoModel::model()->getPaypalAddressByExtTransID($TransactionID, $BuyerID);
        $this->renderJson($result);
    }

    /**
     * @desc 获取退全款是否同步到ebay
     * @author liaojianwen
     * @date 2015-08-10
     */
    public function actionGetReturnTrueAct()
    {
        $this->apiChecklogin(__METHOD__);
        
        $uploadId = CInputFilter::getString('id');
        $actionType = CInputFilter::getString('type');
        $result = ReturnUploadModel::model()->getReturnReply($uploadId, $actionType);
        $this->renderJson($result);
    }

    /**
     * @desc 获取eBay用户的注册地址
     * @author YangLong
     * @date 2015-08-11
     */
    public function actionGetUserRegAddress()
    {
        $this->apiChecklogin(__METHOD__);
        
        $userId = CInputFilter::getString('userId');
        $result = EbayOtherInfoModel::model()->getUserRegAddress($userId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取eBay Item的listting状态
     * @author YangLong
     * @date 2015-08-14
     */
    public function actionGetItemStatus()
    {
        $this->apiChecklogin(__METHOD__);
        
        $itemId = CInputFilter::getString('itemId');
        $result = EbayOtherInfoModel::model()->getItemStatus($itemId);
        $this->renderJson($result);
    }

    /**
     * @desc 直接回复消息，无队列
     * @author YangLong
     * @date 2015-08-17
     */
    public function actionReplyMsg2()
    {
        $this->apiChecklogin(__METHOD__);
        
        $msgId = CInputFilter::getString('msgId');
        $content = CInputFilter::getString('content');
        $imgurl = CInputFilter::getArray('imgurl', 'string');
        $emailCopyToSender = boolConvert::toInt01(CInputFilter::getString('copy'));
        $result = MsgDealModel::model()->replyMsg($msgId, $content, $imgurl, $emailCopyToSender);
        $this->renderJson($result);
    }

    /**
     * @desc 获取cancel disputes 列表
     * @author liaojianwen
     * @date 2015-08-19
     */
    public function actionGetCancelDisputeList()
    {
        $this->apiChecklogin(ActionsEnum::CASE_READ);
        
        $page = CInputFilter::getInt('page', EnumOther::PAGE);
        $pageSize = CInputFilter::getInt('pageSize', EnumOther::PAGESIZE);
        $Type = CInputFilter::getString('type');
        $status = CInputFilter::getString('status'); // 查询条件中的状态
        $itemId = CInputFilter::getString('itemid'); // 查询条件中的item NO
        $cust = CInputFilter::getString('buyer'); // 查询条件中的客户
        $query = CInputFilter::getString('query'); // 判断是查询还是列表展示/query 为查询
        $result = DisputesModel::model()->getCancleDisputeList($Type, $page, $pageSize, $status, $itemId, $cust, $query);
        $this->renderJson($result);
    }

    /**
     * @desc 获取cancel dispute列表
     * @author liaojianwen
     * @date 2015-08-20
     */
    public function actionGetCancelDisputeDetail()
    {
        $this->apiChecklogin(ActionsEnum::CASE_READ);
        
        $disputeid = CInputFilter::getInt('disputeid');
        $type = CInputFilter::getString('type');
        $result = DisputesModel::model()->getCancelDisputeDetail($disputeid, $type);
        $this->renderJson($result);
    }

    /**
     * @desc 获取卖家发起cancel dispute的历史对话
     * @author liaojianwen
     * @date 2015-08-20
     */
    public function actionGetCancelDisputeMessage()
    {
        $this->apiChecklogin(ActionsEnum::CASE_READ);
        
        $disputeid = CInputFilter::getInt('disputeid');
        $result = DisputesModel::model()->getCancelDisputeMessage($disputeid);
        $this->renderJson($result);
    }

    /**
     * @desc 获取feedback 列表
     * @author liaojianwen
     * @date 2015-08-25
     */
    public function actionGetFeedbackList()
    {
        $this->apiChecklogin(ActionsEnum::FEEDBACK_READ);
        
        $page = CInputFilter::getInt('page', EnumOther::PAGE);
        $pageSize = CInputFilter::getInt('pageSize', EnumOther::PAGESIZE);
        $Type = CInputFilter::getString('type');
        $cust = CInputFilter::getString('cust');
        $status = CInputFilter::getString('status');
        $result = EbayFeedbackTransactionModel::model()->getFeedbackList($Type, $page, $pageSize, $cust, $status);
        $this->renderJson($result);
    }

    /**
     * @desc 获取item 图片
     * @author liaojianwen
     * @date 2015-08-26
     */
    public function actionGetItemURL()
    {
        $this->apichecklogin(ActionsEnum::FEEDBACK_READ);
        
        $itemid = CInputFilter::getString('itemid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = EbayListingModel::model()->getItemURL($itemid, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc feedback 备注
     * @author liaojianwen
     * @date 2015-08-26
     */
    public function actionGetFeedbackNote()
    {
        $this->apichecklogin(ActionsEnum::FEEDBACK_READ);
        
        $itemid = CInputFilter::getString('itemid');
        $clientid = CInputFilter::getString('clientid');
        $result = EbayFeedbackTransactionModel::model()->getFeedbackNote($itemid, $clientid);
        $this->renderJson($result);
    }

    /**
     * @desc 增加feedback 备注
     * @author liaojianwen
     * @date 2015-08-26
     */
    public function actionAddFeedbackNote()
    {
        $this->apichecklogin(__METHOD__);
        
        $itemid = CInputFilter::getString('itemid', '');
        $clientid = CInputFilter::getString('clientid');
        $text = CInputFilter::getString('text');
        $result = EbayFeedbackTransactionModel::model()->addFeedbackNote($itemid, $clientid, $text);
        $this->renderJson($result);
    }

    /**
     * @desc 添加新标签
     * @author YangLong
     * @date 2015-08-26
     */
    public function actionAddLabel()
    {
        $this->apiChecklogin(ActionsEnum::LABEL_WRITE);
        
        $labeltitle = CInputFilter::getString('labeltitle');
        $labelcolor = CInputFilter::getString('labelcolor');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = MsgDealModel::model()->addLabel($labeltitle, $labelcolor, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取标签列表
     * @author YangLong
     * @date 2015-08-26
     */
    public function actionGetLabelList()
    {
        $this->apiChecklogin(ActionsEnum::LABEL_READ);
        
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = MsgDealModel::model()->getLabelList($sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取自动标签列表
     * @author YangLong
     * @date 2015-11-09
     */
    public function actionGetAutoLabelList()
    {
        $this->apiChecklogin(ActionsEnum::LABEL_READ);
        
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = MsgDealModel::model()->getAutoLabelList($sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 编辑标签
     * @author YangLong
     * @date 2015-08-26
     */
    public function actionEditLabel()
    {
        $this->apiChecklogin(ActionsEnum::LABEL_WRITE);
        
        $labeltitle = CInputFilter::getString('labeltitle');
        $labelcolor = CInputFilter::getString('labelcolor');
        $labelid = CInputFilter::getInt('labelid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = MsgDealModel::model()->editLabel($labeltitle, $labelcolor, $labelid, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 删除标签
     * @author YangLong
     * @date 2015-08-26
     */
    public function actionDelLabel()
    {
        $this->apiChecklogin(ActionsEnum::LABEL_WRITE);
        
        $labelid = CInputFilter::getInt('labelid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = MsgDealModel::model()->delLabel($labelid, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 设置消息标签
     * @author YangLong
     * @date 2015-08-26
     */
    public function actionSetMsgLabel()
    {
        $this->apiChecklogin(ActionsEnum::MSG_WRITE);
        
        $labelid = CInputFilter::getInt('labelid');
        $msgid = CInputFilter::getInt('msgid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = MsgDealModel::model()->setMsgLabel($labelid, $msgid, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 取消消息标签
     * @author YangLong
     * @date 2015-08-27
     */
    public function actionRemoveMsgLabel()
    {
        $this->apiChecklogin(ActionsEnum::MSG_WRITE);
        
        $labelid = CInputFilter::getInt('labelid');
        $msgid = CInputFilter::getInt('msgid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = MsgDealModel::model()->removeMsgLabel($labelid, $msgid, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 回复feedback
     * @author liaojianwen
     * @date 2015-08-28
     */
    public function actionResponseFeedback()
    {
        $this->apichecklogin(__METHOD__);
        
        $feedbackId = CInputFilter::getString('fedID');
        $text = CInputFilter::getString('text');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = EbayFeedbackTransactionModel::model()->responseFeedback($feedbackId, $text, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 批量回复
     * @author liaojianwen
     * @date 2015-08-31
     */
    public function actionBatchReply()
    {
        $this->apichecklogin(__METHOD__);
        
        $ReplyInfo = CInputFilter::getArray('replyInfo', '');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = EbayFeedbackTransactionModel::model()->batchReply($ReplyInfo, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 生成联系客户msg 队列
     * @author liaojianwen
     * @date 2015-08-31
     */
    public function actionGenerateContactMsgQueue()
    {
        $this->apichecklogin(__METHOD__);
        
        $feedbackId = CInputFilter::getString('feedbackId');
        $content = CInputFilter::getNSvalue('content');
        $imgUrl = CInputFilter::getArray('imgurl', '');
        $emailCopyToSender = CInputFilter::getString('emailCopyToSender');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = EbayFeedbackTransactionModel::model()->generateContactMsgQueue($feedbackId, $content, $imgUrl, $emailCopyToSender, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取消息的相关Case信息
     * @author YangLong
     * @date 2015-08-31
     */
    public function actionGetMsgCaseInfo()
    {
        $this->apiChecklogin(ActionsEnum::MSG_READ);
        
        $msgid = CInputFilter::getInt('msgid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = MsgDealModel::model()->getMsgCaseInfo($msgid, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取ebay用户列表
     * @author YangLong
     * @date 2015-09-06
     */
    public function actionGetEbayUserList()
    {
        $this->apiChecklogin(ActionsEnum::EBAY_USER_READ);
        
        $page = CInputFilter::getInt('page', EnumOther::PAGE);
        $pageSize = CInputFilter::getInt('pageSize', EnumOther::PAGESIZE);
        $keyword = CInputFilter::getNSvalue('keyword');
        $searchType = CInputFilter::getNSvalue('searchType');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = EbayUserModel::model()->getEbayUserList($page, $pageSize, $sellerId, $keyword, $searchType);
        $this->renderJson($result);
    }

    /**
     * @desc 用户管理详情页获取用户详细信息
     * @author YangLong
     * @date 2015-09-07
     */
    public function actionGetEbayUserInfoByUid()
    {
        $this->apiChecklogin(ActionsEnum::EBAY_USER_READ);
        
        $uid = CInputFilter::getInt('uid');
        $result = EbayUserModel::model()->getEbayUserInfoByUid($uid);
        $this->renderJson($result);
    }

    /**
     * @desc 保存feedback status
     * @author liaojianwen
     * @date 2015-09-06
     */
    public function actionSaveFeedbackStatus()
    {
        $this->apiChecklogin(__METHOD__);
        
        $feedbackId = CInputFilter::getString('feedbackid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $msgStatus = CInputFilter::getString('msgstatus');
        $requestStatus = CInputFilter::getString('reqstatus');
        $requestOutDate = CInputFilter::getString('reqtime');
        $feedbackOutDate = CInputFilter::getString('fedtime');
        $declineChange = CInputFilter::getString('dechange');
        $result = EbayFeedbackTransactionModel::model()->saveFeedbackStatus($feedbackId, $msgStatus, $requestStatus, $requestOutDate, $feedbackOutDate, $declineChange, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取feedback条数
     * @author liaojianwen
     * @date 2015-09-06
     */
    public function actionGetFeedbackCount()
    {
        $this->apiChecklogin(ActionsEnum::FEEDBACK_READ);
        
        $result = EbayFeedbackTransactionModel::model()->getFeedbackCount();
        $this->renderJson($result);
    }

    /**
     * @desc 获取用户对应店铺
     * @author YangLong
     * @date 2015-09-14
     */
    public function actionGetUsersByShopid()
    {
        $this->apiChecklogin(ActionsEnum::SHOP_ADMIN_READ);
        
        $shopId = CInputFilter::getInt('shopid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = ShopModel::model()->getUsersByShopid($shopId, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 设置店铺对应的用户
     * @author YangLong
     * @date 2015-09-15
     */
    public function actionSetShopUsers()
    {
        $this->apiChecklogin(ActionsEnum::SHOP_ADMIN_WRITE);
        
        $set = isset($_REQUEST['set']) ? $_REQUEST['set'] : array();
        $shopId = CInputFilter::getInt('shopid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = ShopModel::model()->setShopUsers($set, $shopId, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取店铺所属的用户信息
     * @author YangLong
     * @date 2015-09-15
     */
    public function actionGetShopByUserid()
    {
        $this->apiChecklogin(ActionsEnum::SHOP_ADMIN_READ);
        
        $userId = CInputFilter::getInt('userid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = ShopModel::model()->getShopByUserid($userId, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 设置用户对应的店铺
     * @author YangLong
     * @date 2015-09-17
     */
    public function actionSetUserShops()
    {
        $this->apiChecklogin(ActionsEnum::SHOP_ADMIN_WRITE);
        
        $set = isset($_REQUEST['set']) ? $_REQUEST['set'] : array();
        $userId = CInputFilter::getInt('userid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = ShopModel::model()->setUserShops($set, $userId, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc test
     * @author YangLong
     * @date 2015-09-15
     */
    public function actionSaveNotification()
    {
        MonitorModel::model()->saveNotification();
    }

    /**
     * @desc 根据feedback 中的OrderLineItemId 获取 获取订单信息
     * @author liaojianwen
     * @date 2015-09-15
     */
    public function actionGetFeedbackOrder()
    {
        $this->apiChecklogin(ActionsEnum::FEEDBACK_READ);
        
        $orderLineItemID = CInputFilter::getString('lineid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = EbayFeedbackTransactionModel::model()->getFeedbackOrder($orderLineItemID, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 通过用户获取所有case
     * @author liaojianwen
     * @date 2015-09-22
     */
    public function actionGetCaseListByUserId()
    {
        $this->apiChecklogin(__METHOD__);
        
        $BuyerUserID = CInputFilter::getString('BuyerUserID');
        $result = CaseModel::model()->getCaseListByUserId($BuyerUserID);
        $this->renderJson($result);
    }

    /**
     * @desc  获取用户关联的所有return
     * @author liaojianwen
     * @date 2015-09-23
     */
    public function actionGetReturnListByUserId()
    {
        $this->apiChecklogin(__METHOD__);
        
        $BuyerUserID = CInputFilter::getString('BuyerUserID');
        $result = ReturnModel::model()->getReturnListByUserId($BuyerUserID);
        $this->renderJson($result);
    }

    /**
     * @desc 客户管理中给客户发消息生成队列
     * @author liaojianwen
     * @date 2015-09-23
     */
    public function actionGenerateMsgQueue()
    {
        $this->apiChecklogin(__METHOD__);
        
        $shopId = CInputFilter::getString('shopid');
        $content = CInputFilter::getNSvalue('content');
        $imgUrl = CInputFilter::getArray('imgurl', '');
        $emailCopyToSender = CInputFilter::getString('emailCopyToSender');
        $buyerId = CInputFilter::getString('buyerid');
        $itemId = CInputFilter::getString('itemid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = EbayUserModel::model()->generateMsgQueue($shopId, $content, $imgUrl, $emailCopyToSender, $buyerId, $itemId, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取客户关联的备注
     * @author liaojianwen
     * @date 2015-09-23
     */
    public function actionGetCustNote()
    {
        $this->apiChecklogin(__METHOD__);
        
        $clientId = CInputFilter::getString('clientid');
        $result = EbayUserModel::model()->getCustNote($clientId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取客户关联的msg列表
     * @author liaojianwen
     * @date 2015-09-25
     */
    public function actionGetMsgListByClientId()
    {
        $this->apiChecklogin(__METHOD__);
        
        $page = CInputFilter::getInt('page', EnumOther::PAGE);
        $pageSize = CInputFilter::getInt('pageSize', EnumOther::PAGESIZE);
        $buyerId = CInputFilter::getString('clientid');
        $result = MsgDealModel::model()->getMsgListByClientId($buyerId, $page, $pageSize);
        $this->renderJson($result);
    }

    /**
     * @desc 通过return_id 获取return关联的图片
     * @author liaojianwen
     * @date 2015-10-09
     */
    public function actionGetPictureById()
    {
        $this->apiChecklogin(ActionsEnum::RETURN_READ);
        
        $return_id = CInputFilter::getString('id');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = ReturnModel::model()->getPictureById($return_id, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 上传return 关联的图片
     * @author liaojianwen
     * @date 2015-10-12
     */
    public function actionSubmitDocs()
    {
        $this->apiChecklogin(__METHOD__);
        
        $return_id = CInputFilter::getString('returnid');
        $imgUrl = CInputFilter::getArray('imgurl', '');
        $result = ReturnModel::model()->submitDocs($return_id, $imgUrl);
        $this->renderJson($result);
    }

    /**
     * @desc 设置某条Msg为已处理
     * @author YangLong
     * @date 2015-10-16
     */
    public function actionSetMsgHandled()
    {
        $this->apiChecklogin(ActionsEnum::MSG_WRITE);
        
        $msgid = CInputFilter::getInt('msgid');
        $result = MsgDealModel::model()->setMsgHandled($msgid, true);
        $this->renderJson($result);
    }

    /**
     * @desc 设置某条Msg为已处理
     * @author YangLong
     * @date 2015-10-20
     */
    public function actionSetMsgOnHand()
    {
        $this->apiChecklogin(ActionsEnum::MSG_WRITE);
        
        $msgid = CInputFilter::getInt('msgid');
        $result = MsgDealModel::model()->setMsgHandled($msgid, false);
        $this->renderJson($result);
    }

    /**
     * @desc 设置语言
     * @author YangLong
     * @date 2015-10-19
     */
    public function actionSetLanguage()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $lang = CInputFilter::getString('lang', 'zh-cn');
        $result = EbayUserModel::model()->setLanguage($lang);
        $this->renderJson($result);
    }

    /**
     * @desc 语义分析消息内容读取接口
     * @author YangLong
     * @date 2015-10-29
     */
    public function actionGetMsgEffectText()
    {
        $ModTimeFrom = CInputFilter::getString('ModTimeFrom');
        $ModTimeTo = CInputFilter::getString('ModTimeTo');
        $page = CInputFilter::getInt('page', 1);
        $pageSize = CInputFilter::getInt('pageSize', 1000);
        $result = EbayOtherInfoModel::model()->getMsgEffectText($ModTimeFrom, $ModTimeTo, $page, $pageSize);
        $this->renderJson($result);
    }

    /**
     * @desc 读取tracking信息
     * @author YangLong
     * @date 2015-11-03
     */
    public function actionGetTrackingInfo()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $trackingNumber = CInputFilter::getString('trackno');
        $result = LogisticsModel::model()->getTrackingInfo($trackingNumber);
        $this->renderJson($result);
    }

    /**
     * @desc 读取tracking信息(实时)
     * @author YangLong
     * @date 2015-11-03
     */
    public function actionGetTrackingInfo2()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $trackingNumber = CInputFilter::getString('trackno');
        $result = LogisticsModel::model()->getTrackingInfo2($trackingNumber);
        $this->renderJson($result);
    }

    /**
     * @desc 获取已经上传的SKU列表
     * @author YangLong
     * @date 2015-11-21
     */
    public function actionGetSkuList()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $keywords = CInputFilter::getNSvalue('keywords');
        $page = CInputFilter::getInt('page', 1);
        $pageSize = CInputFilter::getInt('pageSize', 100);
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        // $search = array(
        // '%',
        // '\\'
        // );
        // $replace = array(
        // '\%',
        // '\\\\'
        // );
        // $keywords = str_replace($search, $replace, $keywords);
        $result = SkuMatchModel::model()->getSkuList($page, $pageSize, $sellerId, $keywords);
        $this->renderJson($result);
    }

    /**
     * @desc 获取seller下的所有用户
     * @author YangLong
     * @date 2015-11-21
     */
    public function actionGetSellerUsers()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = SkuMatchModel::model()->getSellerUsers($sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 添加SKU匹配规则
     * @author YangLong
     * @date 2015-11-21
     */
    public function actionAddSkuMatchInfo()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $sku = CInputFilter::getNSvalue('sku');
        $userid = CInputFilter::getInt('userid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $sku = trim($sku);
        $result = SkuMatchModel::model()->addSkuMatchInfo($sku, $userid, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 编辑SKU匹配规则
     * @author YangLong
     * @date 2015-11-23
     */
    public function actionEditSkuMatchInfo()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $sku = CInputFilter::getNSvalue('sku');
        $userid = CInputFilter::getInt('userid');
        $sku0 = CInputFilter::getNSvalue('sku0');
        $userid0 = CInputFilter::getInt('userid0');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $sku = trim($sku);
        $result = SkuMatchModel::model()->editSkuMatchInfo($sku, $userid, $sellerId, $sku0, $userid0);
        $this->renderJson($result);
    }

    /**
     * @desc 删除SKU匹配规则
     * @author YangLong
     * @date 2015-11-21
     */
    public function actionDelSkuMatchInfo()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $sku = CInputFilter::getNSvalue('sku');
        $userid = CInputFilter::getInt('userid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = SkuMatchModel::model()->delSkuMatchInfo($sku, $userid, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 批量删除SKU匹配规则
     * @author YangLong
     * @date 2015-11-24
     */
    public function actionPlDelSkuMatchInfo()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $data = CInputFilter::getNSvalue('data');
        $data = json_decode($data);
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = SkuMatchModel::model()->plDelSkuMatchInfo($data, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 解析SKU匹配信息Excel
     * @author YangLong
     * @date 2015-11-25
     */
    public function actionParseSkuExcel()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $filepath = CInputFilter::getString('filepath');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = SkuMatchModel::model()->parseSkuExcel($filepath, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 设置seller的默认SKU分配相关默认用户
     * @author YangLong
     * @date 2015-11-26
     */
    public function actionSetSkuDefaultUser()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $userid = CInputFilter::getInt('userid');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = SkuMatchModel::model()->setSkuDefaultUser($userid, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取seller的默认SKU分配相关默认用户
     * @author YangLong
     * @date 2015-11-26
     */
    public function actionGetSkuDefaultUser()
    {
        $this->apiChecklogin(ActionsEnum::PUBLIC_READ);
        
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = SkuMatchModel::model()->getSkuDefaultUser($sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取订单信息
     * @author liaojianwen
     * @date 2015-10-31
     */
    public function actionGetUpaidOrderList()
    {
        $this->apiChecklogin(ActionsEnum::URGEPAY_READ);
        
        $page = CInputFilter::getInt('page', 1);
        $pageSize = CInputFilter::getInt('pageSize', 20);
        $cust = CInputFilter::getString('cust');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = InvoicesModel::model()->getUpaidOrderList($page, $pageSize, $cust, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取客户地址
     * @author liaojianwen
     * @date 2015-10-31
     */
    public function actionGetOrderAddr()
    {
        $this->apiChecklogin(ActionsEnum::URGEPAY_READ);
        
        $buyer_id = CInputFilter::getString('buyerid');
        $result = InvoicesModel::model()->getOrderAddr($buyer_id);
        $this->renderJson($result);
    }

    /**
     * @desc 获取订单明细
     * @author liaojianwen
     * @date 2015-10-31
     */
    public function actionGetOrderTransaction()
    {
        $this->apiChecklogin(ActionsEnum::URGEPAY_READ);
        
        $order_Id = CInputFilter::getString('orderid');
        $result = InvoicesModel::model()->getOrderTransaction($order_Id);
        $this->renderJson($result);
    }

    /**
     * @desc 获取订单物流服务
     * @author liaojianwen
     * @date 2015-11-03
     */
    public function actionGetEbayShipService()
    {
        $this->apiChecklogin(ActionsEnum::URGEPAY_READ);
        
        $siteid = CInputFilter::getString('id');
        $flag = CInputFilter::getInt('flag');
        $result = InvoicesModel::model()->getEbayShipService($siteid, $flag);
        $this->renderJson($result);
    }

    /**
     * @desc 对客户进行催款
     * @author liaojianwen
     * @date 2015-11-03
     */
    public function actionSendInvoice()
    {
        $this->apiChecklogin(__METHOD__);
        
        $orderID = CInputFilter::getString('orderid');
        $serviceOptions = CInputFilter::getArray('serviceoptions', '');
        $text = CInputFilter::getString('text');
        $adjustAmount = CInputFilter::getFloat('discount', 0);
        $currencyID = CInputFilter::getString('currencyid');
        $isSendMe = CInputFilter::getBool('issendme');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $ebay_orders_id = CInputFilter::getString('ebay_orders_id');
        $result = InvoicesModel::model()->ebaySendInvoices($orderID, $serviceOptions, $text, $adjustAmount, $currencyID, $isSendMe, $sellerId, $ebay_orders_id);
        $this->renderJson($result);
    }

    /**
     * @desc 批量对客户进行发消息
     * @author liaojianwen
     * @date 2015-11-05
     */
    public function actionBatchSendMsg()
    {
        $this->apiChecklogin(__METHOD__);
        
        $msgInfo = CInputFilter::getArray('msgInfo', '');
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = EbayFeedbackTransactionModel::model()->batchSendMsg($msgInfo, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 保存设置
     * @author liaojianwen
     * @date 2015-11-23
     */
    public function actionSaveConfig()
    {
        $this->apiChecklogin(__METHOD__);
        
        $imsconfig = CInputFilter::getString(EnumOther::TASKASSIGN);
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = ConfigSetModel::model()->saveConfig($imsconfig, $sellerId);
        $this->renderJson($result);
    }

    /**
     * @desc 获取设置
     * @author liaojianwen
     * @date 2015-11-24
     */
    public function actionGetConfig()
    {
        $this->apiChecklogin(__METHOD__);
        
        $sellerId = Yii::app()->session['userInfo']['seller_id'];
        $result = ConfigSetModel::model()->getConfig($sellerId);
        $this->renderJson($result);
    }
}
