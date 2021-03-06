<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta name=”viewport” content=”width=device-width, initial-scale=1, maximum-scale=1″>
  <link rel="stylesheet" href="public/template/css/login.css" />
  <title><?= $lang['loginbox']['title']; ?></title>
  <meta name="keywords" content="易服 eBay消息管理系统 IMS CRM ERP 出口易 速脉 速脉ERP M2C M2B 供销品台">
  <meta name="description" content="专注eBay客户服务">
  <link rel="icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
  <link rel="shortcut icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
  <!--<link rel="icon" href="animated_favicon1.gif" type="image/gif" >-->
</head>
<body class="login">
<!--[if lte IE 8]>
  <style>#ie8-warning{position:fixed;top:0;width:100%;z-index:99;color: #C39653;border-bottom:2px solid #C39653; background: #FDF8E4;height:25px;line-height:25px;text-align:center;}</style>
  <div id="ie8-warning"><p>您正在使用的浏览器版本较旧，为了更好的体验本系统，请您使用谷歌(chrome)、火狐(firebox)或者IE9及以上版本浏览器。</div>
<![endif]-->
  <!-- 头部 start -->
  <div class="top">
    <img src="public/template/img/logo2.png" alt="">
    <span><?= $lang['loginbox']['toptext']; ?></span>
  </div>
  <!-- 主要内容 -->
  <div class="main">
    <div class="firstBox boxC">
      <div class="firstBoxCon">
        <div class="leftTitle">
          <div class="leftTitleTop">
            <p><?= $lang['loginbox']['copys1']; ?></p>
            <p><?= $lang['loginbox']['copys2']; ?></p>
            <p><?= $lang['loginbox']['copys3']; ?></p>
            <p><?= $lang['loginbox']['copys4']; ?></p>
            <p><?= $lang['loginbox']['copys5']; ?></p>
          </div>
        <div class="leftTitleBottom">
          <h4><?= $lang['loginbox']['copys6']; ?></h4>
          <p><?= $lang['loginbox']['copys7']; ?></p>
        </div>
        </div>
        <div class="loginBox">
          <div class="hintPBox">
            <p class="hintP"></p>
          </div>
          <p>
            <input type="text" name="username" id="user" placeholder="<?= $lang['loginbox']['inpusertit']; ?>" />
          </p>
          <p>
            <input type="password" name="password" id="pwd" placeholder="<?= $lang['loginbox']['inppasstit']; ?>" />
          </p>
          <!-- 验证码 -->
          <div class="verifyBox">
            <input type="text" name="" id="verifycode" class="verifyInp" placeholder="<?= $lang['loginbox']['inpverifytit']; ?>" />
            <div class="verifyImg"><img id="code" src="" alt="<?= $lang['loginbox']['updateverifyimgtit']; ?>" height="42" width="90" style="cursor: pointer; vertical-align:middle;" /></div>
            <span id="next"><?= $lang['loginbox']['updateverifyimg']; ?></span>
            <div class="clear"></div>
          </div>
          <p>
            <label>
              <input type="checkbox" name="" value="" id="remindName" checked="checked"><?= $lang['loginbox']['rememberusername']; ?></label>
          </p>
          <p>
            <input type="submit" id="loginIn" value="<?= $lang['loginbox']['loginbtn']; ?>" class="sub" />
          </p>
          <p class="bottomP">
            <a href="demo/?r=Api/AutoLogin&sid=<?= $sid; ?>"><?= $lang['loginbox']['look']; ?></a>|<a href="?r=home/Register"><?= $lang['loginbox']['registerbtn']; ?></a></p>
        </div>
        <div class="clear"></div>
      </div>
    </div>
    <div class="box-2 boxC">
      <div class="box-2-1">
        <div class="left">
          <img src="public/template/img/bjb.png" alt="">
        </div>
        <div class="right">
          <img src="public/template/img/ebay.jpg" alt="">
          <h2><?= $lang['copysbox2']['logoname']; ?></h2>
          <h2><?= $lang['copysbox2']['copys1']; ?></h2>
        </div>
        <div class="clear"></div>
      </div>
      <div class="box-2-bg"></div>
      <div class="bottomH">
        <div class="bottomHCon">
          <h4><?= $lang['copysbox2']['copys2_1']; ?><span class="orangeFont">3</span><?= $lang['copysbox2']['copys2_2']; ?></h4>
          <ul>
            <li>
              <div class="bottomHIcon">
                <img src="public/template/img/security.png" alt="">
              </div>
              <div class="textRight">
                <p>安全</p>
                <p>SAFETY</p>
              </div>
            </li>
            <li>
              <div class="bottomHIcon">
                <img src="public/template/img/power.png" alt="">
              </div>
              <div class="textRight">
                <p>高效</p>
                <p>EFFICIENT</p>
              </div>
            </li>
            <li style="border:none;margin:0;">
              <div class="bottomHIcon">
                <img src="public/template/img/checkmark.png" alt="">
              </div>
              <div class="textRight">
                <p>简易</p>
                <p>EASY</p>
              </div>
            </li>
          </ul>
          <div class="clear"></div>
        </div>
      </div>
    </div>
    <div class="box-3">
      <div class="boxCon">
        <div class="boxConTop">
          <div class="left box-3-text">
            <h4><?= $lang['copysbox3']['copys1']; ?></h4>
            <ul>
              <li><?= $lang['copysbox3']['copys2']; ?></li>
              <li><?= $lang['copysbox3']['copys3']; ?></li>
            </ul>
          </div>
          <div class="right box-3-img">
            <img src="public/template/img/bg_04.jpg" alt="">
          </div>
          <div class="clear"></div>
        </div>
        <div class="boxConBottom" style="padding-top:260px;">
          <div class="left box-3-img">
            <img src="public/template/img/reng.jpg" alt="">
          </div>
          <div class="right box-3-text">
            <h4><?= $lang['copysbox3']['copys4']; ?></h4>
            <ul>
              <li><?= $lang['copysbox3']['copys5']; ?></li>
              <li><?= $lang['copysbox3']['copys6']; ?></li>
              <li><?= $lang['copysbox3']['copys7']; ?></li>
            </ul>
          </div>
          <div class="clear"></div>
        </div>
        </div>
    </div>
    <div class="lastBox">
      <ul>
        <li title="<?= $lang['bottombox']['ewmtit']; ?>" style="margin-left:0;cursor:auto;">
          <img src="public/template/img/ewm.jpg" alt="">
          <p><?= $lang['bottombox']['ewm']; ?></p>
        </li>
        <li title="<?= $lang['bottombox']['visitortit']; ?>">
          <a href="demo/?r=Api/AutoLogin&sid=<?= $sid; ?>">
            <img src="public/template/img/lasticon_13.jpg" alt="" id="backToTop">
          </a>
          <p><?= $lang['bottombox']['visitor']; ?></p>
        </li>
        <li title="<?= $lang['bottombox']['clickregisternow']; ?>">
          <img src="public/template/img/lasticon_10.jpg" salt="" id="register">
          <p><?= $lang['bottombox']['registernow']; ?></p>
        </li>
      </ul>
      <div class="clear"></div>
    </div>
  </div>
  <div id="back-to-top" title="<?= $lang['bottombox']['backtop']; ?>"><a href="#top"></a></div>
</body>
<script src="public/template/js/jquery-1.8.3.min.js?v<?= $git_hash; ?>"></script>
<script src="public/lang/<?= $lang_dir; ?>/js/lang.js?v<?= $git_hash; ?>"></script>
<script src="public/template/js/common.js?v<?= $git_hash; ?>"></script>
<script src="public/template/js/login_biz.js?v<?= $git_hash; ?>"></script>

</html>
