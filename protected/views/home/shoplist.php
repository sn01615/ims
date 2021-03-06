<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv = "X-UA-Compatible" content = "IE=edge,chrome=1" />
    <link rel="stylesheet" type="text/css" href="public/template/css/index.css" />
  </head>
  <body>
    <div class="content shopSet">
      <!-- 店铺增加弹窗 -->
      <div class="TCGB TC500" id="add_window">
        <div class="TCBox">
            <h2><span title="TCtitle"><?= $lang['addShop']['title']; ?></span><span class="TCClose"><i class="icon-remove iconBtnS" id="addnewshop_closebtn" title="<?= $lang['addShop']['close_tip']; ?>"></i></span></h2>
          <div class="TCBody">
            <div id="info_box">
              <p style="font-size:14px;margin-bottom:15px;"><?= $lang['addShop']['info_box']['tip']; ?></p>
              <img src="public/template/img/addshop.jpg" width="469" height="159">
            </div>
            <div id="nickname_box">
              <p style="padding:0 30px;font-size:14px;margin-bottom:15px;"><?= $lang['addShop']['nickname_box']['tip']; ?></p>
              <span class="spanMode span100"><?= $lang['addShop']['nickname_box']['nick_name']; ?></span>
              <input name="" type="text" id="nickname">
            </div>
            <!--<p>
              <span class="spanMode span100">站点：</span>
              <select name="" id="siteId">
              </select>
            </p>
            <p>
              <span class="spanMode span100">账号：</span>
              <input type="text" id="shopname">
            </p>-->
          </div>
          <div class="TCFoot">
            <span class="subBtn ggg2" id="add_next_btn"><?= $lang['addShop']['action_tip']['next_btn']; ?></span>
            <span class="subBtn ggg2" id="add_login_btn" style="display:none;"><a href="#" target="_blank" style="color:inherit;"><?= $lang['addShop']['action_tip']['login_btn']; ?></a></span>
            <span class="subBtn ggg2" id="add_ack_btn" style="display:none;"><?= $lang['addShop']['action_tip']['ack_btn']; ?></span>
            <span class="ggg2" id="add_info" style="display:none;"></span>
          </div>
        </div>
      </div>
      <!-- 用户选择 -->
      <div class="TCGB TC500" id="permission_set_window">
        <div class="TCBox">
          <h2><span title="TCtitle"><?= $lang['user_option']['title']; ?></span><span class="TCClose"><i class="icon-remove iconBtnS" id="permission_set_window_closebtn" title="<?= $lang['user_option']['close_tip']; ?>"></i></span></h2>
          <div class="TCBody" id="permission_set_window_ckbs"></div>
          <div class="TCFoot">
            <span class="subBtn" id="permission_set_window_btn"><?= $lang['user_option']['save']; ?></span>
          </div>
        </div>
      </div>
      <!-- 店铺列表 -->
      <table class="table" cellpadding="0" cellspacing="0" id="list_table">
        <col width="50"/>
        <col width="50"/>
        <col width="100"/>
        <col width=""/>
        <col width=""/>
        <col width=""/>
        <col width="20%"/>
        <col width=""/>
        <col width="150"/>
        <col width="30"/>
        <col width="30"/>
        <thead>
          <tr class="inBoxConTop">
            <th colspan="11">
              <span class="noBgBtn" id="pldelete"><?= $lang['shop_list']['del_user']; ?></span>
              <span class="noBgBtn addUserBtn" title="<?= $lang['shop_list']['addShop_tip']; ?>" id="addshop"><i class="icon-plus iconBtnS"></i></span>
            </th>
          </tr>
          <tr>
            <th><input type="checkbox" name="" id="checkall" /></th>
            <th><?= $lang['shop_list']['serial_num']; ?></th>
            <th><?= $lang['shop_list']['platform']; ?></th>
            <th><?= $lang['shop_list']['site_id']; ?></th>
            <th><?= $lang['shop_list']['shop_name']; ?></th>
            <th><?= $lang['shop_list']['create_time']; ?></th>
            <th><?= $lang['shop_list']['expiration']; ?></th>
            <th><?= $lang['shop_list']['right_manage']; ?></th>
            <th><?= $lang['shop_list']['status']; ?></th>
            <th></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <!-- 数据输出 start -->
          <!-- 数据输出 end -->
        </tbody>
        <tfoot>
        <!--下面tr为表格底行（页面跳转行）-->
          <tr>
            <td colspan="11" id="paginationNavBar" style="text-align:right;padding-right:40px;"></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </body>
  <script src="public/template/js/jquery-1.8.3.min.js?v<?= $git_hash; ?>"></script>
  <script src="public/lang/<?= $lang_dir; ?>/js/lang.js?v<?= $git_hash; ?>"></script>
  <script src="public/template/js/common.js?v<?= $git_hash; ?>"></script>
  <script src="public/template/js/shopset_biz.js?v<?= $git_hash; ?>"></script>
</html>