<?php

/**
 * @desc IMS综合枚举文件
 * @author YangLong
 * @desc 2015-03-04
 */
class EnumOther
{

    /**
     * @desc users表超级用户ID
     * @var int
     */
    const ROOT_USER_ID = 1;
    
    /**
     * @var 执行信息回复任务数量
     */
    const MSG_REPLY_SIZE = 10;
    
    /**
     * @var 信息解释任务数量
     */
    const MSG_PARSE_SIZE = 50;
    
    /**
     * @var Return解析任务数量
     */
    const RETURN_PARSE_SIZE = 1;
    
    /**
     * @var Listing解析任务数量
     */
    const LISTING_PARSE_SIZE = 10;
    
    /**
     * @var CASE解析任务数量
     */
    const DISPUTES_PARSE_SIZE = 10;
    
    /**
     * @var FEEDBACK解析任务数量
     */
    const FEEDBACK_PARSE_SIZE = 5;
    
    /**
     * @var 待处理信息下载表状态值
     */
    const MSG_DOWN_NOTSTATUS = 0;
    
    /**
     * @var 已处理信息下载表状态值
     */
    const MSG_DOWN_DEALSTATUS = 1;
    
    /**
     * @var 定义系统的默认页
     */
    const PAGE = 1;
    
    /**
     * @var 定义系统默认的每页显示数量
     */
    const PAGESIZE = 20;
    
    /**
     * @var 调用接口成功状态
     */
    const ACK_SUCCESS = 'Success';
    
    /**
     * @var 调用接口失败状态
     */
    const ACK_FAILURE = 'Failure';
    
    /**
     * @var 调用接口警告状态
     */
    const ACK_WARNING = 'Warning';
    
    const MODIFY_DEL            = 'del';            //待处理等页面的删除
    const MODIFY_HIDDEN         = 'hidden';         //已删除页面的删除
    const MODIFY_REVERT         = 'revert';         //信息还原
    const MODIFY_READ_NOT       = 'read_not';       //标记未读
    const MODIFY_READ_SUCCE     = 'read_succe';     //标记已读
    const MODIFY_STAR_NOT       = 'star_not';       //取消星标
    const MODIFY_STAR_SUCCE     = 'star_succe';     //标记星标
    const MODIFY_DISPOSE_SUCCE  = 'handle_not';     //标记待处理
    const MODIFY_DISPOSE_NOT    = 'handle_yes';     //取消待处理
    const MODIFY_LABEL_NOT      = 'label_not';      //取消自定义标签
    const MODIFY_LABEL_SUCCE    = 'label_succe';    //添加自定义标签
    const MODIFY_REPLIED        = 'replied';        //标记为已回复
    
    const IMS_PENDING           = 'pending';        //待处理信息
    const IMS_STAR              = 'star';           //星标信息
    const IMS_SYS               = 'sys';            //系统信息
    const IMS_SENT              = 'sent';           //发件箱信息
    const IMS_SALEBEFORE        = 'salebefore';     //售前信息
    const IMS_SALEAFTER         = 'saleafter';      //售后信息
    const IMS_DELETE            = 'delete';         //删除信息
    const IMS_MEMBER            = 'member';         //会员信息
    const IMS_LABEL             = 'label';          //自定义标签
    const IMS_AUTOTAG           = 'autotag';        //自动标签
    
    const CASE_CANCEL           = 'cancel';         //取消订单
    const CASE_INR              = 'ebp_inr';        //买家发起case没有收到商品
    const CASE_SNAD             = 'ebp_snad';       //买家发起case描述不符合
    const CASE_UPI              = 'upi';            //卖家发起case没收到付款

    /**
     * @desc 下载心跳时间
     * @var int
     */
    const HEARTBEAT_TIME = 900;
    
    /**
     * @desc 生成feedback队列的频率
     * @var int
     */
    const FEEDBACK_TIME = 21600;
    
    /**
     * @desc 生成feedback队列的频率
     * @var int
     */
    const RETURN_UPDATE_TIME = 1800;
    
    /**
     * @desc Case上传重试间隔
     * @var int
     */
    const CASE_REUPLOAD_TIME = 120;
    
    /**
     * @desc return上传重试间隔
     * @var int
     */
    const RETURN_REUPLOAD_TIME = 120;

    /**
     * @desc 重叠时间2分钟
     * @var int
     */
    const OVARLAP_TIME = 60;
    
    /**
     * @desc 消息校验间隔
     * @var int
     */
    const MSG_CHECK_TIME = 43200;
    
    /**
     * @desc 校验时间宽度
     * @var int
     */
    const MSG_CHECK_SIZE = 604800;
    
    /**
     * @desc 新用户默认初次下载时间
     * @var int
     */
    const NEW_USER_GET_TIME = 2592000;
    
    /**
     * @desc 最大运行次数
     * @var int
     */
    const MAX_RUN_COUNT = 4;
    
    // 账号状态常量定义
    const EBAY_ACCOUNT_CLOSED  = 2; // 禁用
    const EBAY_ACCOUNT_NORMAL  = 1; // 正常(启用)
    const EBAY_ACCOUNT_UNAUTH  = 3; // 未授权
    const EBAY_ACCOUNT_OVERDUE = 4; // 授权过期
    
    /**
     * eBay case 最大下载时间
     * @var int
     */
    const CASE_MAX_DOWNLOAD_DATE = 47347200;
    
    /**
     * ebay return 最大下载时间 18个月
     * @var int
     */
    const RETURN_MAX_DOWNLOAD_DATE = 47347200;
    
    /**
     * 下载任务一次性取出个数
     * @var int
     */
    const DOWN_EXECUTESIZE = 1;
    
     /**
     * return下载任务一次性取出个数
     * @var int
     */
    const RETURN_EXECUTESIZE = 3;
    /**
     * CASE DATA再次摘取时间
     * @var int
     */
    const CASE_DATA_REPICK_TIME = 600;

    /**
     * CASE DATA最大摘取次数
     * @var int
     */
    const CASE_DATA_MAX_PICK_COUNT = 3;
    
    /**
     * FEEDBACK DATA再次摘取时间
     * @var int
     */
    const FEEDBACK_DATA_REPICK_TIME = 600;
    
     /**
     * FEEDBACK DATA最大摘取次数
     * @var int
     */
    const FEEDBACK_DATA_MAX_PICK_COUNT = 3;
    
    
     /**
     * RETURN DATA再次摘取时间
     * @var int
     */
    const RETURN_DATA_REPICK_TIME = 600;
    
     /**
     * RETURN DATA最大摘取次数
     * @var int
     */
    const RETURN_DATA_MAX_PICK_COUNT = 3;
    
    
    /**
     * DISPUTE DATA再次摘取时间
     * @var int
     */
    const DISPUTE_DATA_REPICK_TIME = 600;
    
    /**
     * DISPUTE DATA最大摘取次数
     * @var int
     */
    const DISPUTE_DATA_MAX_PICK_COUNT = 3;
    
    /**
     * Case Upload每次运行取出的个数
     * @var int
     */
    const CASE_UPLOAD_PICK_SIZE = 10;
    
    /**
     * RETURN Upload 每次运行取出的个数
     * @var int
     */
    const RETURN_UPLOAD_PICK_SIZE = 10;
    
    /**
     * FEEDBACK Upload 每次运行取出的个数
     * @var int
     */
    const FEEDBACK_UPLOAD_PICK_SIZE = 10;
    
    /**
     * @desc Case状态更新间隔
     * @var int
     */
    const CASE_UPLOAD_STATUS_TIME = 1800;
    
    /**
     * @desc Case状态更新范围（天数）
     * @var int
     */
    const CASE_UPLOAD_SIZE = 75;

    /**
     * @desc 消息Text字段名日志位置
     * @var string
     */
    const LOG_DIR_MSG_TEXT = 'msg/text';

    /**
     * @desc 消息Content字段名日志位置
     * @var string
     */
    const LOG_DIR_MSG_CONTENT = 'msg/content';
    
    /**
     * @desc return 图片字段位置
     * @var string
     */
    const LOG_DIR_RETURN_FILE_DATA='orginal';
    
    /**
     * @desc return 图片压缩字段位置
     * @var string
     */
    const LOG_DIR_RETURN_RESIZE_FILE_DATA='resize';
    
    /**
     * @desc return 图片临时存放位置
     * @var string
     */
    const LOG_DIR_RETURN_TEMP_FILE_DATA='tmpreturnfiledata';
    
    /**
     * @desc return 图片存放地后缀
     * @var String
     */
    const LOG_DIR_RETURN_TEMP_DOWN_TAG ='DOWN';
    const LOG_DIR_RETURN_TEMP_UPDATE_TAG ='UPDATE';
    
    /**
     * @desc configset 任务分配
     * @var string
     */
    const TASKASSIGN ='taskAssign';
    
}
