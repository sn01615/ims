<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<title><?= $lang['title']; ?></title>
<link rel="stylesheet" type="text/css" href="public/template/css/index.css" />
<link rel="icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
<!--<link rel="icon" href="animated_favicon1.gif" type="image/gif" >-->
</head>
<body class="indexPage">
<div class="wrap"> 
    <!--新增标签弹窗 start-->
    <div class="TCGB TC400 addTagTC" id="add_label_window">
        <div class="TCBox">
            <h2><span class="TCtitle"><?= $lang['label']['add']['addlabel']; ?></span><span class="TCClose"><i class="icon-remove iconBtnS" title="<?= $lang['com']['clickclose']; ?>" id="add_label_window_close"></i></span></h2>
            <div class="TCBody">
                <p><?= $lang['label']['add']['inputlabelname']; ?>：
                    <input type="text" name="" id="input_label_name" maxlength="12">
                </p>
                <div>
                    <p style="float:left;"><?= $lang['label']['add']['selectlabelcolor']; ?>：</p>
                    <div class="selColorBox" style="float:left;">
                        <div class="defaultBox"> <span class="iconTab nui-tag1" id="label_color" cc="1"></span> <i class="icon-angle-down"></i> </div>
                        <ul class="tagColorList" id="label_color_select">
                            <li><span class="iconTab nui-tag1" cc="1"></span></li>
                            <li><span class="iconTab nui-tag2" cc="2"></span></li>
                            <li><span class="iconTab nui-tag3" cc="3"></span></li>
                            <li><span class="iconTab nui-tag4" cc="4"></span></li>
                            <li><span class="iconTab nui-tag5" cc="5"></span></li>
                            <li><span class="iconTab nui-tag6" cc="6"></span></li>
                            <li><span class="iconTab nui-tag7" cc="7"></span></li>
                            <li><span class="iconTab nui-tag8" cc="8"></span></li>
                            <li><span class="iconTab nui-tag9" cc="9"></span></li>
                            <li><span class="iconTab nui-tag10" cc="10"></span></li>
                            <li><span class="iconTab nui-tag11" cc="11"></span></li>
                            <li><span class="iconTab nui-tag12" cc="12"></span></li>
                            <li><span class="iconTab nui-tag13" cc="13"></span></li>
                            <li><span class="iconTab nui-tag14" cc="14"></span></li>
                            <li><span class="iconTab nui-tag15" cc="15"></span></li>
                            <li><span class="iconTab nui-tag16" cc="16"></span></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="TCFoot"> <span class="subBtn" id="add_label_window_submit"><?= $lang['com']['OK']; ?></span> <span class="subBtn" id="add_label_window_cancel"><?= $lang['com']['CANCEL']; ?></span> </div>
        </div>
    </div>
    <!--新增标签弹窗 end--> 
    
    <!--管理标签弹窗 start-->
    <div class="TCGB TC600 mTagTC" id="man_label_window">
        <div class="TCBox">
            <h2><span class="TCtitle"><?= $lang['label']['man']['labelmanage']; ?></span><span class="TCClose"><i class="icon-remove iconBtnS" title="<?= $lang['com']['clickclose']; ?>" id="man_label_window_close"></i></span></h2>
            <div class="TCBody">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= $lang['label']['man']['labelname']; ?></th>
                            <th><?= $lang['label']['man']['msgcount']; ?></th>
                            <th><?= $lang['label']['man']['labelname']; ?></th>
                        </tr>
                    </thead>
                    <tbody id="man_label_window_list">
                    </tbody>
                </table>
            </div>
            <div class="TCFoot"> <span class="subBtn" id=""><?= $lang['com']['OK']; ?></span> 
                <!--<span class="subBtn" id=""><?= $lang['com']['CANCEL']; ?></span>--> 
            </div>
        </div>
    </div>
    <!--管理标签弹窗 end--> 
    
    <!-- 头部 start -->
    <div class="header">
        <h1 class="logo"><img src="public/template/img/logo.png"/></h1>
        <ul class="siteNav">
            <li>
                <div class="comboBox">
                    <div class="defaultBox"> <span class="defaultOp"><em>eBay</em><b></b></span> <span><i class="icon-angle-down"></i></span> </div>
                    <ul class="selList">
                        <!--<li><span>速卖通</span></li>-->
                        <li><span>eBay</span> </li>
                    </ul>
                </div>
            </li>
            <li>
                <div class="comboBox" id="SitesList">
                    <div class="defaultBox"> <span class="defaultOp"><em><?= $lang['head']['site']; ?></em><b></b></span> <span><i class="icon-angle-down"></i></span> </div>
                    <ul class="selList">
                    </ul>
                </div>
            </li>
            <li>
                <div class="comboBox" id="AccountList">
                    <div class="defaultBox"> <span class="defaultOp"><em><?= $lang['head']['account']; ?></em><b></b></span> <span><i class="icon-angle-down"></i></span> </div>
                    <ul class="selList">
                    </ul>
                </div>
            </li>
        </ul>
        <div class="sign">
            <span id="username"></span>
            <div class="comboBox" style="display:inline-block;min-width:80px;">
                <div class="defaultBox"><span class="defaultOp"><em id="lang_display"><?= $currentLangName; ?></em></span><span><i class="icon-angle-down"></i></span></div>
                <ul class="selList" id="lang_select">
                    <?php foreach ($langlist as $key => $item) : ?>
                    <li data-lang="<?= $key; ?>"><span><?= $item; ?></span></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="signRight">
                <i class="reFull" title="<?= $lang['head']['fullscreen']; ?>"></i>
                <i class="icon-signout" id="logout" title="<?= $lang['head']['loginout']; ?>"></i>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <!-- 头部 end --> 
    <!--左边侧栏 start-->
    <div class="sidebar">
        <div id="imsversion"><?= $lang['sidebar']['version']; ?>：0.10160524</div>
        <ul style="position:relative;z-index: 1;">
            <li> 
                <!-- ==================================客服系统=================================== -->
                <h2><i class="icon-right"></i><a href="javascript:;"><?= $lang['sidebar']['msg']; ?></a></h2>
                <ul class="sideF2">
                    <li>
                        <h3 id='disposeCount'><a href="?r=Home/MsgList&class=pending" target="main"><?= $lang['sidebar']['msgitem']['pending']; ?><b></b></a></h3>
                        <h3><i class="icon-right"></i><a href="?r=Home/MsgList&class=member" target="main"><?= $lang['sidebar']['msgitem']['member']; ?></a></h3>
                        <ul class="sideF3">
                            <li>
                                <h3><a href="?r=Home/MsgList&class=salebefore" target="main" class="sideF3a"><?= $lang['sidebar']['msgitem']['salebefore']; ?></a></h3>
                                <h3><a href="?r=Home/MsgList&class=saleafter" target="main" class="sideF3a"><?= $lang['sidebar']['msgitem']['saleafter']; ?></a></h3>
                            </li>
                        </ul>
                        <h3><a href="?r=Home/MsgList&class=sys" target="main"><?= $lang['sidebar']['msgitem']['sys']; ?></a></h3>
                        <h3><a href="?r=Home/MsgList&class=star" target="main"><?= $lang['sidebar']['msgitem']['star']; ?></a></h3>
                        <h3><a href="?r=Home/MsgList&class=sent" target="main"><?= $lang['sidebar']['msgitem']['sent']; ?></a></h3>
                        <h3><a href="?r=Home/MsgList&class=delete" target="main"><?= $lang['sidebar']['msgitem']['delete']; ?></a></h3>
                        <h3><i class="icon-right"></i><a href="javascript:;" target="main"><?= $lang['sidebar']['msgitem']['label']; ?></a>
                            <div class="tabRight"><i class="iconAddTab" title="<?= $lang['sidebar']['msgitem']['addlabel']; ?>" id="add_label"></i><i class="iconSetTab" title="<?= $lang['sidebar']['msgitem']['managelabel']; ?>" id="man_label"></i></div>
                        </h3>
                        <ul class="sideF3">
                            <li id="nav_label_list"></li>
                        </ul>
                        <!-- <h3><i class="icon-right"></i><a href="javascript:;" target="main">自动标签</a></h3>
                        <ul class="sideF3">
                            <li id="nav_auto_label_list"></li>
                        </ul> -->
                    </li>
                </ul>
            </li>
            <!-- ==================================ebay纠纷=================================== -->
            <li>
                <h2><i class="icon-right"></i><a href="javascript:;"><?= $lang['sidebar']['dispute']; ?></a></h2>
                <ul class="sideF2">
                    <li>
                        <h3><a href="?r=Home/ReturnList" target="main" title="<?= $lang['sidebar']['disputeitem']['return']; ?>"><?= $lang['sidebar']['disputeitem']['return']; ?></a></h3>
                        <h3><a href="?r=Home/DisputeList&type=ebp_inr" target="main" title="<?= $lang['sidebar']['disputeitem']['ebp_inr']; ?>"><?= $lang['sidebar']['disputeitem']['ebp_inr']; ?></a></h3>
                        <h3><a href="?r=Home/DisputeList&type=ebp_snad" target="main" title="<?= $lang['sidebar']['disputeitem']['ebp_snad']; ?>"><?= $lang['sidebar']['disputeitem']['ebp_snad']; ?></a></h3>
                        <h3><a href="?r=Home/DisputeList&type=cancel" target="main" title="<?= $lang['sidebar']['disputeitem']['cancel']; ?>"><?= $lang['sidebar']['disputeitem']['cancel']; ?></a></h3>
                        <h3><a href="?r=Home/DisputeList&type=upi" target="main" title="<?= $lang['sidebar']['disputeitem']['upi']; ?>"><?= $lang['sidebar']['disputeitem']['upi']; ?></a></h3>
                    </li>
                </ul>
            </li>
            <li style="display:none;">
                <h2><i class="icon-right"></i><a href="javascript:;">PayPal纠纷</a></h2>
                <ul class="sideF2">
                    <li>
                        <h3><a href="javascript:;" target="main" title="未收到货">未收到货</a></h3>
                        <h3><a href="javascript:;" target="main" title="描述不符">描述不符</a></h3>
                        <h3><a href="javascript:;" target="main" title="PayPal调查">PayPal调查</a></h3>
                        <h3><a href="javascript:;" target="main" title="退单">退单</a></h3>
                    </li>
                </ul>
            </li>
            <!-- ==================================客户评价=================================== -->
            <li class="appraiseLi">
                <h2><i class="icon-right"></i><a href="javascript:;"><?= $lang['sidebar']['feedback']; ?></a></h2>
                <ul class="sideF2">
                    <li>
                        <h3 id="fedComprehensive"><a href="?r=Home/FeedbackList&type=comprehensive" target="main"><?= $lang['sidebar']['feedbackitem']['comprehensive']; ?><b></b></a></h3>
                        <h3 id="fedPositive"><i class="iconPos"></i><a href="?r=Home/FeedbackList&type=positive" target="main"><?= $lang['sidebar']['feedbackitem']['positive']; ?><b></b></a></h3>
                        <h3 id="fedNeutral"><i class="iconNeu"></i><a href="?r=Home/FeedbackList&type=neutral" target="main"><?= $lang['sidebar']['feedbackitem']['neutral']; ?><b></b></a></h3>
                        <h3 id="fedNegative"><i class="iconNeg"></i><a href="?r=Home/FeedbackList&type=negative" target="main"><?= $lang['sidebar']['feedbackitem']['negative']; ?><b></b></a></h3>
                    </li>
                </ul>
            </li>
            <!-- ==================================客户管理=================================== -->
            <li class="">
                <h2><i class="icon-right"></i><a href="javascript:;"><?= $lang['sidebar']['buyermanage']; ?></a></h2>
                <ul class="sideF2">
                    <li>
                        <h3><a href="?r=Home/Cusmanage" target="main"><?= $lang['sidebar']['buyermanageitem']['allbuyer']; ?></a></h3>
                    </li>
                </ul>
            </li>
            <!-- ==================================订单催付=================================== -->
            <li class="">
                <h2><i class="icon-right"></i><a href="javascript:;"><?= $lang['sidebar']['reminder']['title']; ?></a></h2>
                <ul class="sideF2">
                    <li>
                        <h3><a href="?r=Home/UrgePay" target="main"><?= $lang['sidebar']['reminder']['sub_title']; ?></a></h3>
                    </li>
                </ul>
            </li>
            <!-- ==================================系统设置=================================== -->
            <li>
                <h2><i class="icon-right"></i><a href="javascript:;"><?= $lang['sidebar']['syssetup']; ?></a></h2>
                <ul class="sideF2">
                    <li>
                        <h3><a href="?r=Home/UserList" target="main"><?= $lang['sidebar']['syssetupitem']['usermanage']; ?></a></h3>
                        <h3><a href="?r=Home/Shoplist" target="main"><?= $lang['sidebar']['syssetupitem']['shopsetup']; ?></a></h3>
                        <h3><a href="?r=Home/Replytp" target="main"><?= $lang['sidebar']['syssetupitem']['fastreply']; ?></a></h3>
                        <!-- <h3><a href="?r=Home/ConfigSet" target="main"><?= $lang['sidebar']['syssetupitem']['configmgr']; ?></a></h3>
                        <h3><a href="?r=Home/SkuMatch" target="main"><?= $lang['sidebar']['syssetupitem']['skuimport']; ?></a></h3> -->
                    </li>
                </ul>
            </li>
        </ul>
        <div class="clear"></div>
    </div>
    <!--左边侧栏 end--> 
    <!-- 右边主要内容 start -->
    <div class="main">
        <iframe name="main" src="" frameborder="0" width="100%" style="float:left;"></iframe>
        <div class="clear"></div>
    </div>
    <!-- 右边主要内容 end --> 
</div>
<script src="public/template/js/jquery-1.8.3.min.js?v<?= $git_hash; ?>"></script>
<script src="public/lang/<?= $lang_dir; ?>/js/lang.js?v<?= $git_hash; ?>"></script>
<script src="public/template/js/common.js?v<?= $git_hash; ?>"></script>
<script src="public/template/js/index_eft.js?v<?= $git_hash; ?>"></script>
<script src="public/template/js/index_biz.js?v<?= $git_hash; ?>"></script>
</body>
</html>