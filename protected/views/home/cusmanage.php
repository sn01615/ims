<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<link rel="stylesheet" type="text/css" href="public/template/css/index.css" />
</head>

<body class="cusmanage">
<div class="content"> 
  <!--评价列表-->
  <table class="table disputListTable" cellpadding="0" cellspacing="0">
    <col />
    <col />
    <col />
    <col />
    <col />
    <col />
    <col />
    <thead>
      <tr>
        <th colspan="7"> <div class="searchDiv">
            <p>
              <select name="" id="search_type">
                <option value="userid"><?= $lang['buyeruserid']; ?></option>
                <option value="username"><?= $lang['buyername']; ?></option>
              </select>
            </p>
            <p>
              <input type="text" name="" id="keyword" />
            </p>
            <p>
              <input type="submit" id="search_btn" value="<?= $lang['search']; ?>" class="subBtn" />
            </p>
          </div>
        </th>
      </tr>
      <tr>
        <th><?= $lang['list_th']['buyer_user_id']; ?></th>
        <th><?= $lang['list_th']['buyer_name']; ?></th>
        <th><?= $lang['list_th']['regaddr']; ?></th>
        <th><?= $lang['list_th']['shop_history']; ?></th>
        <th><?= $lang['list_th']['case_count']; ?></th>
        <th><?= $lang['list_th']['feed_count']; ?></th>
        <th><?= $lang['list_th']['action']; ?></th>
      </tr>
    </thead>
    <tbody id="cus_list">
      <!-- 数据输出 start --> 
      <!-- 数据输出 end -->
    </tbody>
    <tfoot>
      <!--下面tr为表格底行（页面跳转行）-->
      <tr>
        <td id="paginationNavBar" colspan="7" style="text-align:right;padding-right:40px;"></td>
      </tr>
    </tfoot>
  </table>
</div>
<script src="public/template/js/jquery-1.8.3.min.js?v<?= $git_hash; ?>"></script> 
<script src="public/lang/<?= $lang_dir; ?>/js/lang.js?v<?= $git_hash; ?>"></script>
<script src="public/template/js/common.js?v<?= $git_hash; ?>"></script> 
<script src="public/template/js/cusmanage_biz.js?v<?= $git_hash; ?>"></script>
</body>
</html>