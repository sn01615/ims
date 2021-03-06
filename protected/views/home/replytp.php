<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv = "X-UA-Compatible" content = "IE=edge,chrome=1" />
        <link rel="stylesheet" type="text/css" href="public/template/css/index.css" />
    </head>
    <body class="replyTemplate">
        <!--新增回复弹窗 start-->
        <div class="TCGB replyTC TC600" id="add_window">
            <div class="TCBox">
                <h2><span class="TCtitle"><?= $lang['add_tp_window']['title']; ?></span><span class="TCClose"><i class="icon-remove iconBtnS" title="<?= $lang['com']['click_close']; ?>"></i></span></h2>
                <div class="TCBody">
                    <p id="xxLine2">
                    <span class="spanMode span60"><?= $lang['add_tp_window']['class']; ?>：</span>
                    <input name="class_id" type="hidden" value="">
                    <input name="tp_list_id" type="hidden" value="0">
                    </p>
                    <p>
                    <span class="spanMode span60"><?= $lang['add_tp_window']['subject']; ?>：</span>
                    <input name="title" type="text">
                    </p>
                    <p>
                    <span class="spanMode span60"><?= $lang['add_tp_window']['content']; ?>：</span>
                    <textarea name="content" ></textarea>
                    </p>
                </div>
                <div class="TCFoot">
                    <span class="subBtn" id="save_btn"><?= $lang['com']['OK']; ?></span>
                </div>
            </div>
        </div>
        <!--新增回复弹窗 end-->
        <!--分类管理弹窗 start-->
        <div class="TCGB classifyTC TC400" id="class_edit_box">
            <div class="TCBox">
                <h2><span id="info_t001" class="TCtitle"><?= $lang['com']['OK']; ?></span><span class="TCClose"><i class="icon-remove iconBtnS" id="rename_close_btn"></i></span></h2>
                <div class="clear"></div>
                <div class="TCBody">
                    <p>
                    <span class="spanMode span100" id="info_l001"><?= $lang['class_manger_window']['rename_to']; ?>：</span>
                    <input name="tp_class_id" type="hidden" value="">
                    <input name="pid" type="hidden" value="">
                    <input name="classname" type="text" maxlength="10">
                    </p>
                </div>
                <div class="TCFoot">
                    <span class="subBtn" id="save_btn_2"><?= $lang['com']['OK']; ?></span>
                </div>
            </div>
        </div>
        <!--分类管理弹窗 end-->
        <div class="content">
            <!-- <div class="conTopSel" id="class_box">三级联动容器</div> -->
            <div class="classify">
                <h2>
                <span><?= $lang['class_manger']['class']; ?></span>
                <a href="javascript:;" title="<?= $lang['class_manger']['manger_btn_tip']; ?>" class="classManage" btnon="off" id="classManage_btn"><?= $lang['class_manger']['manger_btn']; ?></a>
                <div id="other_btn_box1" style="float:right; margin-right:0.8em;">
                    <a href="javascript:;" d="open_all" style="margin-right:0.8em;"><?= $lang['class_manger']['open_all']; ?></a>
                    <a href="javascript:;" d="close_all" style="margin-right:0.8em;display:none;"><?= $lang['class_manger']['close_all']; ?></a>
                    <!--<a href="javascript:;" d="unsel_all" style="margin-right:0.8em;">取消选择</a>-->
                </div>
                <div id="other_btn_box2" style="float:right; margin-right:0.8em; display:none;"><a href="javascript:;" d="add_root"><?= $lang['class_manger']['add_root_class']; ?></a></div>
                <div class="clear"></div>
                </h2>
                <div id="new_class_box"></div>
            </div>
            <table class="table" cellpadding="0" cellspacing="0" id="list_table">
                <col width="70"/>
                <col width=""/>
                <col width=""/>
                <col width=""/>
                <col width="70"/>
                <col width="70"/>
                <thead>
                    <tr class="inBoxConTop">
                        <th colspan="6">
                            <span class="noBgBtn" id="pldelete"><?= $lang['list_tb']['delete']; ?></span>
                            <span class="noBgBtn addUserBtn" title="<?= $lang['list_tb']['add_reply']; ?>" id="addTpBtn"><i class="icon-plus iconBtnS"></i></span>
                        </th>
                    </tr>
                    <tr>
                        <th><input type="checkbox" name="" id="checkall" /></th>
                        <th><?= $lang['list_tb']['th']['serial']; ?></th>
                        <th><?= $lang['list_tb']['th']['class']; ?></th>
                        <th><?= $lang['list_tb']['th']['subject']; ?></th>
                        <th><?= $lang['list_tb']['th']['edit']; ?></th>
                        <th><?= $lang['list_tb']['th']['delete']; ?></th>
                    </tr>
                </thead>
                <!-- 纠纷模板列表 -->
                <tbody>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="6" id="paginationNavBar" style="text-align:right;padding-right:40px;"></td>
                </tr>
                </tfoot>
            </table>
        </div>
    </body>
    <link rel="stylesheet" type="text/css" href="public/template/js/jstree.themes/default/style.css">
    <script src="public/template/js/jquery-1.8.3.min.js?v<?= $git_hash; ?>"></script>
	<script src="public/lang/<?= $lang_dir; ?>/js/lang.js?v<?= $git_hash; ?>"></script>
    <script src="public/template/js/jstree.min.js?v<?= $git_hash; ?>"></script>
    <script src="public/template/js/common.js?v<?= $git_hash; ?>"></script>
    <script src="public/template/js/replytp_biz.js?v<?= $git_hash; ?>"></script>
    <!-- {literal} -->
    <script>
        function autoWidth(){
            var zWidth=parseInt($('.content').width())-320;
            $('.table').css('width',zWidth);
        }
        function defaultHeight() {
            var winHeight = $(window).height();
            var deHeight = parseInt(winHeight) - 81;
            $('#new_class_box').css({
                height: deHeight
            });
        }
        autoWidth();
        defaultHeight();
        $(window).resize(function(){
            autoWidth();
            defaultHeight();
        })
    </script>
    <!-- {/literal} -->
</html>