<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <link rel="stylesheet" type="text/css" href="public/template/css/index.css" />
</head>

<body class="msglist labellist">
    <div class="content">
        <!-- 标签选择弹块 -->
        <div class="labelTc">
            <ul id="set_msg_label_sel"></ul>
        </div>
        <table class="table" cellpadding="0" cellspacing="0" id="list_table">
            <col width="50px" />
            <col width="90px" />
            <col id="td_f" />
            <col id="td_s" />
            <col />
            <col width="20px" />
            <col width="30px" />
            <col width="140px" />
            <!-- 表格头部 -->
            <thead>
                <tr class="inBoxConTop">
                    <th colspan="8"> <span class="noBgBtn" id="delete"><?= $lang['delete']; ?></span> <span class="noBgBtn" id="revert" style="display:none"><?= $lang['restore']; ?></span>
                        <div class="comboBox" id="markSel">
                            <div class="defaultBox"> <span class="defaultOp"><?= $lang['marker']; ?><b></b></span> <span><i class="icon-angle-down"></i></span> </div>
                            <ul class="selList">
                                <li id="read"><span><?= $lang['marker_read']; ?></span></li>
                                <li id="unread"><span><?= $lang['marker_unread']; ?></span></li>
                                <li id="stars"><span><?= $lang['marker_stars']; ?></span></li>
                                <li id="unstars"><span><?= $lang['marker_unstars']; ?></span></li>
                                <!--<li id = "undisposes"><span>不需处理</span></li>-->
                            </ul>
                        </div>
                        <label style="float:left; margin:7px 0 0 15px; font-weight:normal; font-size:12px;"><input name="" type="checkbox" value="" id="aggregate_btn" checked><?= $lang['sessionmode']; ?></label>
                        <label style="float:left; margin:7px 0 0 15px; font-weight:normal; font-size:12px;"><input name="" type="checkbox" id="openOptions"><?= $lang['openoptions']; ?></label>
                        <span class="noBgBtn SyncBtn" id="" style="display:none;"><?= $lang['synceBay']; ?></span>
                        <div class="right">
                            <div class="search">
                                <p class="dfVal"></p>
                                <input type="text" name="" id="searchName" />
                                <span class="searchBtn" id="search" title="<?= $lang['searchbtn']; ?>"><i class="icon-search"></i></span> </div>
                        </div>
                    </th>
                <tr style="background:none;display:none;border-bottom:1px dashed #ccc;" class="optionTr">
                    <th colspan="2"><?= $lang['question_type']; ?>:</th>
                    <th colspan="6">
                        <div id="question_type">
                            <label><input name="QuestionType" type="radio" value="" checked><?= $lang['no_select']; ?></label>
                            <label><input name="QuestionType" type="radio" value="General" title="General questions about the item."><?= $lang['General']; ?></label>
                            <label><input name="QuestionType" type="radio" value="MultipleItemShipping" title="Questions related to the shipping of this item bundled with other items also purchased on eBay."><?= $lang['MultipleItemShipping']; ?></label>
                            <label><input name="QuestionType" type="radio" value="CustomizedSubject" title="Customized subjects set by the seller using SetMessagePreferences or the eBay Web site."><?= $lang['CustomizedSubject']; ?></label>
                            <label><input name="QuestionType" type="radio" value="Payment" title="Questions related to the payment for the item."><?= $lang['Payment']; ?></label>
                            <label><input name="QuestionType" type="radio" value="Shipping" title="Questions related to the shipping of the item."><?= $lang['Shipping']; ?></label>
                            <label><input name="QuestionType" type="radio" value="None" title="No question type applies."><?= $lang['None']; ?></label>
                            <label><input name="QuestionType" type="radio" value="CustomCode" title="Reserved for future or internal use."><?= $lang['CustomCode']; ?></label>
                        </div>
                    </th>
                </tr>
                <tr style="background:none;display:none;border-bottom:1px solid #ccc;" class="optionTr">
                    <th colspan="2"><?= $lang['msg_type']; ?>:</th>
                    <th colspan="6">
                      <div id="message_type">
                          <label><input name="MessageType" type="radio" value="" checked><?= $lang['no_select']; ?></label>
                          <label><input name="MessageType" type="radio" value="AskSellerQuestion" title="Member to Member message initiated by bidder/potential bidder to a seller of a particular item."><?= $lang['AskSellerQuestion']; ?></label>
                          <label><input name="MessageType" type="radio" value="ContactEbayMember" title="Member to Member message initiated by any eBay member to another eBay member."><?= $lang['ContactEbayMember']; ?></label>
                          <label><input name="MessageType" type="radio" value="ContacteBayMemberViaAnonymousEmail" title="Member message initiated after eBay receives an email sent by an eBay member's email client to another eBay member."><?= $lang['ContacteBayMemberViaAnonymousEmail']; ?></label>
                          <label><input name="MessageType" type="radio" value="ContacteBayMemberViaCommunityLink" title="Member to Member message initiated by any eBay member to another eBay member who has posted on a community forum within the past 7 days."><?= $lang['ContacteBayMemberViaCommunityLink']; ?></label>
                          <label><input name="MessageType" type="radio" value="ContactMyBidder" title="Member to Member message initiated by sellers to their bidders during an active listing."><?= $lang['ContactMyBidder']; ?></label>
                          <label><input name="MessageType" type="radio" value="ContactTransactionPartner" title="Member message between order partners within 90 days after creation of the order."><?= $lang['ContactTransactionPartner']; ?></label>
                          <label><input name="MessageType" type="radio" value="ResponseToASQQuestion" title="Member to Member message initiated as a response to an Ask A Question message."><?= $lang['ResponseToASQQuestion']; ?></label>
                          <label><input name="MessageType" type="radio" value="ResponseToContacteBayMember" title="Member to Member message initiated as a response to a Contact eBay Member message."><?= $lang['ResponseToContacteBayMember']; ?></label>
                          <label><input name="MessageType" type="radio" value="ClassifiedsBestOffer" title="Indicates that a best offer has been made on the seller's corresponding classified ad listing. This message type is only applicable to Classified categories that allow the Best Offer feature, such as motor vehicles."><?= $lang['ClassifiedsBestOffer']; ?></label>
                          <label><input name="MessageType" type="radio" value="ClassifiedsContactSeller" title="Indicates that an inquiry has been sent to the seller regarding the corresponding classified ad listing."><?= $lang['ClassifiedsContactSeller']; ?></label>
                          <label><input name="MessageType" type="radio" value="All" title="All message types."><?= $lang['All']; ?></label>
                          <label><input name="MessageType" type="radio" value="CustomCode" title="备用"><?= $lang['CustomCode']; ?></label>
                      </div>
                    </th>
                </tr>
                <tr>
                    <th>
                        <input type="checkbox" name="" id="checkall" />
                    </th>
                    <th><?= $lang['status']; ?></th>
                    <th><?= $lang['sender']; ?></th>
                    <th><?= $lang['receiver']; ?></th>
                    <th><?= $lang['subject']; ?></th>
                    <th></th>
                    <th></th>
                    <th><?= $lang['time']; ?></th>
                </tr>
            </thead>
            <!-- 表格内容 输出邮件列表 -->
            <tbody id="msg_list">
            </tbody>
            <!-- 表格尾部 -->
            <tfoot>
                <tr>
                    <td id="paginationNavBar" colspan="8" style="text-align:right;"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <script src="public/template/js/jquery-1.8.3.min.js?v<?= $git_hash; ?>"></script>
    <script src="public/lang/<?= $lang_dir; ?>/js/lang.js?v<?= $git_hash; ?>"></script>
    <script src="public/template/js/common.js?v<?= $git_hash; ?>"></script>
    <!-- {literal} -->
        <script>
            // 表头展开/收起更多选项
            $(function(){
                $('#openOptions').on('change',function(){
                    $(this).prop('checked')?$('.optionTr').show(200):$('.optionTr').hide(200);
                })
            })
        </script>
    <!-- {/literal} -->
    <script src="public/template/js/msglist_biz.js?v<?= $git_hash; ?>"></script>
</body>

</html>
